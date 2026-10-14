<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    initialCode: {
        type: String,
        default: '',
    },
    suggestedRacks: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue', 'mutated', 'closed']);

// ==========================================
// Scan State
// ==========================================
const scanInput = ref('');
const scanInputRef = ref(null);
const isScanning = ref(false);
const scanError = ref(null);

const activeBarcode = ref(null);
const rackItems = ref([]);

// ==========================================
// Live Racks from DB State
// ==========================================
const liveRacks = ref([]);
const isLoadingRacks = ref(false);

async function fetchLiveRacks() {
    isLoadingRacks.value = true;
    try {
        const response = await axios.get(route('barcodes.racksList'));
        if (response.data && Array.isArray(response.data)) {
            liveRacks.value = response.data;
        }
    } catch (err) {
        console.warn('Gagal mengambil daftar rak terbaru dari database:', err);
    } finally {
        isLoadingRacks.value = false;
    }
}

const displayRacks = computed(() => {
    if (liveRacks.value.length > 0) {
        return liveRacks.value;
    }
    if (props.suggestedRacks && props.suggestedRacks.length > 0) {
        return props.suggestedRacks.map((r) =>
            typeof r === 'string' ? { code: r, items_count: 0 } : r
        );
    }
    return [];
});

// ==========================================
// Mutation State
// ==========================================
const activeMutatingItem = ref(null);
const mutationType = ref('in'); // 'in' or 'out'
const mutationAmount = ref(1);
const mutationNotes = ref('');
const isSubmittingMutation = ref(false);
const mutationAlert = ref(null);

// ==========================================
// Add Item directly to Scanned Rack State
// ==========================================
const isAddingItem = ref(false);
const isSubmittingNewItem = ref(false);
const addItemError = ref(null);

const DEFAULT_CATEGORIES = [
    'Heavy Equipment',
    'Material Handlers',
    'Safety Gear',
    'Electronics',
    'Spare Parts',
    'Perlengkapan',
    'Konsumsi',
    'Aset',
];

const STORAGE_KEY_CATEGORIES = 'warehouse_custom_categories';
const savedCategories = ref([]);
const isCustomCategory = ref(false);
const customCategoryInput = ref('');

function loadSavedCategories() {
    try {
        const stored = localStorage.getItem(STORAGE_KEY_CATEGORIES);
        if (stored) {
            const parsed = JSON.parse(stored);
            if (Array.isArray(parsed)) {
                savedCategories.value = parsed;
            }
        }
    } catch (e) {
        console.warn('Gagal membaca saved categories dari localStorage:', e);
    }
}

function saveCustomCategory(categoryName) {
    const trimmed = (categoryName || '').trim();
    if (!trimmed) return;
    if (!savedCategories.value.includes(trimmed) && !DEFAULT_CATEGORIES.includes(trimmed)) {
        savedCategories.value.push(trimmed);
        try {
            localStorage.setItem(STORAGE_KEY_CATEGORIES, JSON.stringify(savedCategories.value));
        } catch (e) {
            console.warn('Gagal menyimpan kategori ke localStorage:', e);
        }
    }
}

const allCategories = computed(() => {
    return Array.from(new Set([...DEFAULT_CATEGORIES, ...savedCategories.value])).filter(Boolean);
});

const newItemForm = ref({
    code: '',
    name: '',
    category: 'Heavy Equipment',
    stock: 1,
    unit: 'Unit',
    min_stock: 2,
    status: 'Ready',
    condition: 'Good',
});

function autoGenerateSku() {
    const rack = (activeBarcode.value?.rack_code || 'A01').replace(/[^a-zA-Z0-9]/g, '');
    const rand = Math.floor(100 + Math.random() * 900);
    newItemForm.value.code = `SKU-${rack}-${rand}`;
}

function openAddItemForm() {
    isAddingItem.value = true;
    activeMutatingItem.value = null;
    addItemError.value = null;
    isCustomCategory.value = false;
    customCategoryInput.value = '';
    loadSavedCategories();
    autoGenerateSku();
}

function closeAddItemForm() {
    isAddingItem.value = false;
    addItemError.value = null;
    isCustomCategory.value = false;
    customCategoryInput.value = '';
    newItemForm.value = {
        code: '',
        name: '',
        category: 'Heavy Equipment',
        stock: 1,
        unit: 'Unit',
        min_stock: 2,
        status: 'Ready',
        condition: 'Good',
    };
}

async function submitNewItem() {
    if (!activeBarcode.value) return;

    let finalCategory = newItemForm.value.category;
    if (isCustomCategory.value) {
        finalCategory = customCategoryInput.value.trim();
        if (finalCategory) {
            saveCustomCategory(finalCategory);
            newItemForm.value.category = finalCategory;
        }
    }

    if (!newItemForm.value.code || !newItemForm.value.name || !finalCategory) {
        addItemError.value = 'Mohon lengkapi kode SKU, nama barang, dan kategori!';
        return;
    }

    isSubmittingNewItem.value = true;
    addItemError.value = null;

    const locationName = `Zone ${activeBarcode.value.zone || 'Zone A'} (${activeBarcode.value.rack_code}) Rak 1`;

    try {
        const payload = {
            code: newItemForm.value.code.trim().toUpperCase(),
            name: newItemForm.value.name.trim(),
            category: finalCategory,
            location: locationName,
            stock: parseInt(newItemForm.value.stock) || 1,
            unit: newItemForm.value.unit || 'Unit',
            min_stock: parseInt(newItemForm.value.min_stock) || 2,
            status: newItemForm.value.status || 'Ready',
            condition: newItemForm.value.condition || 'Good',
        };

        const res = await axios.post(route('warehouse.items.store'), payload, {
            headers: { Accept: 'application/json' },
        });

        const createdItem = res.data?.item || payload;
        if (!createdItem.id) {
            createdItem.id = Date.now();
        }

        // Push directly into rack items array
        rackItems.value.unshift(createdItem);

        mutationAlert.value = {
            type: 'success',
            message: `Barang '${createdItem.name}' (+${createdItem.stock} ${createdItem.unit}) berhasil didaftarkan ke Rak ${activeBarcode.value.rack_code}!`,
        };

        closeAddItemForm();

        // Notify parent & refresh live racks count
        emit('mutated', {
            item: createdItem,
            type: 'in',
            amount: createdItem.stock,
            barcode: activeBarcode.value,
        });

        fetchLiveRacks();
        router.reload({ preserveScroll: true });
    } catch (err) {
        console.error('Error adding item to rack:', err);
        const serverMsg = err.response?.data?.message;
        const validationErrors = err.response?.data?.errors;
        if (validationErrors) {
            const firstErr = Object.values(validationErrors).flat()[0];
            addItemError.value = firstErr || serverMsg || 'Gagal menyimpan barang ke rak.';
        } else {
            addItemError.value = serverMsg || 'Gagal menyimpan barang ke rak. Periksa koneksi atau input.';
        }
    } finally {
        isSubmittingNewItem.value = false;
    }
}

// Watch modal open/close
watch(
    () => props.modelValue,
    async (isOpen) => {
        if (isOpen) {
            mutationAlert.value = null;
            scanError.value = null;
            closeAddItemForm();

            // Always fetch freshest live racks from DB on open
            fetchLiveRacks();

            if (props.initialCode && props.initialCode.trim() !== '') {
                scanInput.value = props.initialCode.trim();
                executeScan(scanInput.value);
            } else {
                await nextTick();
                scanInputRef.value?.focus();
            }
        } else {
            resetModalState();
        }
    }
);

function resetModalState() {
    activeBarcode.value = null;
    rackItems.value = [];
    activeMutatingItem.value = null;
    mutationAlert.value = null;
    scanError.value = null;
    scanInput.value = '';
    closeAddItemForm();
}

function closeModal() {
    emit('update:modelValue', false);
    emit('closed');
}

// ==========================================
// Execute Scan / Lookup
// ==========================================
async function executeScan(codeToScan = null) {
    const code = (codeToScan || scanInput.value || '').trim();
    if (!code) return;

    isScanning.value = true;
    scanError.value = null;
    mutationAlert.value = null;
    activeMutatingItem.value = null;
    closeAddItemForm();

    try {
        const response = await axios.post(route('barcodes.scan'), { code });
        const data = response.data;

        if (data && data.success) {
            activeBarcode.value = data.barcode;
            rackItems.value = data.items ? [...data.items] : [];
            scanInput.value = data.barcode.barcode_code;
        } else {
            scanError.value = data?.message || `Barcode atau kode rak '${code}' tidak ditemukan di sistem.`;
            activeBarcode.value = null;
            rackItems.value = [];
        }
    } catch (err) {
        scanError.value = err.response?.data?.message || 'Terjadi kendala saat memindai barcode. Periksa kode rak.';
        activeBarcode.value = null;
        rackItems.value = [];
    } finally {
        isScanning.value = false;
    }
}

function quickSelectRack(code) {
    scanInput.value = code;
    executeScan(code);
}

// ==========================================
// Stock Mutation Controls
// ==========================================
function startMutation(item, type) {
    if (activeMutatingItem.value?.id === item.id && mutationType.value === type) {
        activeMutatingItem.value = null;
        return;
    }
    isAddingItem.value = false;
    activeMutatingItem.value = item;
    mutationType.value = type;
    mutationAmount.value = 1;
    mutationNotes.value = '';
    mutationAlert.value = null;
}

function cancelMutation() {
    activeMutatingItem.value = null;
}

function adjustAmount(delta) {
    const nextVal = (mutationAmount.value || 0) + delta;
    if (nextVal < 1) return;
    if (mutationType.value === 'out' && activeMutatingItem.value && nextVal > activeMutatingItem.value.stock) {
        return;
    }
    mutationAmount.value = nextVal;
}

function setQuickNote(note) {
    mutationNotes.value = note;
}

async function submitMutation() {
    if (!activeMutatingItem.value || mutationAmount.value < 1) return;

    if (mutationType.value === 'out' && mutationAmount.value > activeMutatingItem.value.stock) {
        mutationAlert.value = {
            type: 'error',
            message: `Stok tidak mencukupi. Maksimal pengeluaran adalah ${activeMutatingItem.value.stock} ${activeMutatingItem.value.unit}.`,
        };
        return;
    }

    isSubmittingMutation.value = true;
    mutationAlert.value = null;

    try {
        const response = await axios.post(route('warehouse.items.mutate', activeMutatingItem.value.id), {
            type: mutationType.value,
            amount: mutationAmount.value,
            input_unit: activeMutatingItem.value.base_unit || activeMutatingItem.value.unit,
            notes: mutationNotes.value.trim() || (mutationType.value === 'in' ? `Inbound ke Rak ${activeBarcode.value?.rack_code}` : `Outbound dari Rak ${activeBarcode.value?.rack_code}`),
        });

        const data = response.data;

        if (data && data.success) {
            const itemRef = activeMutatingItem.value;
            const updatedStock = data.item?.stock ?? (mutationType.value === 'in'
                ? itemRef.stock + mutationAmount.value
                : itemRef.stock - mutationAmount.value);

            // Update item in local list
            itemRef.stock = updatedStock;
            if (data.item?.formatted_stock) {
                itemRef.formatted_stock = data.item.formatted_stock;
            }
            if (updatedStock <= (itemRef.min_stock || 5)) {
                itemRef.status = 'Low Stock';
            } else if (itemRef.status === 'Low Stock' && updatedStock > (itemRef.min_stock || 5)) {
                itemRef.status = 'Ready';
            }

            const actionText = mutationType.value === 'in' ? 'penambahan (IN)' : 'pengeluaran (OUT)';
            const displayStock = itemRef.formatted_stock || `${updatedStock} ${itemRef.unit}`;
            mutationAlert.value = {
                type: 'success',
                message: `Sukses mencatat ${actionText} ${mutationAmount.value} ${itemRef.unit} untuk [${itemRef.name}]. Stok sekarang: ${displayStock}.`,
            };

            // Emit mutated event to parent page
            emit('mutated', {
                item: itemRef,
                type: mutationType.value,
                amount: mutationAmount.value,
                barcode: activeBarcode.value,
            });

            // Reset active form
            activeMutatingItem.value = null;
            mutationAmount.value = 1;
            mutationNotes.value = '';

            // Soft reload Inertia page props in background to refresh metrics & logs
            router.reload({ preserveScroll: true });
        } else {
            mutationAlert.value = {
                type: 'error',
                message: data.message || 'Gagal menyimpan mutasi stok.',
            };
        }
    } catch (err) {
        mutationAlert.value = {
            type: 'error',
            message: 'Terjadi kendala sistem saat memproses mutasi stok.',
        };
    } finally {
        isSubmittingMutation.value = false;
    }
}

// Category badge color helper
function getCategoryBadge(category) {
    switch (category) {
        case 'Heavy Equipment':
            return 'bg-amber-500/10 text-amber-400 border-amber-500/30';
        case 'Material Handlers':
            return 'bg-blue-500/10 text-blue-400 border-blue-500/30';
        case 'Safety Gear':
            return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30';
        case 'Electronics':
            return 'bg-purple-500/10 text-purple-400 border-purple-500/30';
        case 'Spare Parts':
            return 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30';
        default:
            return 'bg-slate-800 text-slate-300 border-slate-700';
    }
}

// Keyboard shortcut (Escape to close)
function handleGlobalKeydown(e) {
    if (e.key === 'Escape' && props.modelValue) {
        closeModal();
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleGlobalKeydown);
    fetchLiveRacks();
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleGlobalKeydown);
});
</script>

<template>
    <div
        v-if="modelValue"
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/85 backdrop-blur-md"
    >
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-2xl w-full p-5 sm:p-6 shadow-2xl relative text-slate-100 flex flex-col max-h-[92vh]">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-400 relative">
                        <span class="absolute -top-1 -right-1 flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                        </span>
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-white tracking-wide">
                            Scan Barcode Rak & Mutasi Stok
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Pindai stiker rak fisik untuk melihat isi barang, menambah item, dan update stok (IN / OUT).
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    @click="closeModal"
                    class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Scanner Input Box -->
            <div class="mt-4 p-3.5 bg-slate-950 rounded-xl border border-slate-800">
                <form @submit.prevent="executeScan()" class="flex gap-2">
                    <div class="relative flex-1">
                        <input
                            ref="scanInputRef"
                            v-model="scanInput"
                            type="text"
                            placeholder="Ketik atau scan barcode (e.g. BC-A01-8942 atau RAK A-01)..."
                            class="w-full bg-slate-900 border border-slate-700 rounded-lg pl-3 pr-8 py-2 text-xs sm:text-sm text-white font-mono uppercase focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500"
                        />
                        <button
                            v-if="scanInput"
                            type="button"
                            @click="scanInput = ''; scanInputRef?.focus()"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white text-xs"
                        >
                            &times;
                        </button>
                    </div>

                    <button
                        type="submit"
                        :disabled="isScanning"
                        class="px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white rounded-lg text-xs font-bold transition-all disabled:opacity-50 flex items-center gap-1.5 shadow-md shadow-cyan-600/20 active:scale-95"
                    >
                        <svg v-if="isScanning" class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>{{ isScanning ? 'Mencari...' : 'Scan' }}</span>
                    </button>
                </form>

                <!-- Live Quick Racks from Database -->
                <div v-if="displayRacks && displayRacks.length > 0" class="mt-2.5 pt-2 border-t border-slate-900">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[10px] text-slate-400 font-semibold flex items-center gap-1.5">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Pilih cepat rak ({{ displayRacks.length }} rak terdaftar di Database):
                        </span>
                        <button
                            type="button"
                            @click="fetchLiveRacks"
                            title="Muat ulang rak terbaru dari DB"
                            class="text-[10px] text-slate-500 hover:text-cyan-400 transition-colors flex items-center gap-1 font-mono"
                        >
                            <svg class="w-3 h-3" :class="isLoadingRacks ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Refresh
                        </button>
                    </div>

                    <!-- Scrollable chip list of ALL racks -->
                    <div class="flex flex-wrap items-center gap-1.5 max-h-24 overflow-y-auto pr-1">
                        <button
                            v-for="r in displayRacks"
                            :key="r.code || r"
                            type="button"
                            @click="quickSelectRack(r.code || r)"
                            class="px-2 py-0.5 rounded text-[10px] font-mono transition-all flex items-center gap-1"
                            :class="activeBarcode?.rack_code === (r.code || r) ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40 font-bold' : 'bg-slate-800 hover:bg-slate-700 text-cyan-300 border border-slate-700'"
                        >
                            <span>{{ r.code || r }}</span>
                            <span v-if="r.items_count !== undefined" class="text-[9px] text-slate-400 font-sans">
                                ({{ r.items_count }})
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Scan Error Feedback -->
            <div v-if="scanError" class="mt-3 p-3 bg-rose-500/10 border border-rose-500/30 rounded-xl text-xs text-rose-300 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ scanError }}</span>
                </div>
                <button type="button" @click="scanError = null" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <!-- Mutation Alert Banner -->
            <div
                v-if="mutationAlert"
                class="mt-3 p-3 rounded-xl text-xs flex items-center justify-between border"
                :class="mutationAlert.type === 'success' ? 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30' : 'bg-rose-500/10 text-rose-300 border-rose-500/30'"
            >
                <div class="flex items-center gap-2">
                    <svg v-if="mutationAlert.type === 'success'" class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg v-else class="w-4 h-4 text-rose-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ mutationAlert.message }}</span>
                </div>
                <button type="button" @click="mutationAlert = null" class="text-slate-400 hover:text-white text-xs">&times;</button>
            </div>

            <!-- Active Rack Details Strip -->
            <div v-if="activeBarcode" class="mt-3.5 p-3.5 bg-gradient-to-r from-slate-950 to-slate-900 rounded-xl border border-amber-500/30 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-base font-black text-amber-400 font-mono tracking-tight">
                            RAK {{ activeBarcode.rack_code }}
                        </span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-500/15 text-amber-300 border border-amber-500/30">
                            {{ activeBarcode.barcode_code }}
                        </span>
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">
                        {{ activeBarcode.zone || 'Zone A' }} &bull; {{ activeBarcode.label }}
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <span class="text-[10px] uppercase tracking-wider text-slate-500 font-semibold block">Total Item Rak</span>
                        <span class="font-mono text-sm font-bold text-emerald-400">{{ rackItems.length }} SKU</span>
                    </div>

                    <!-- Shortcut button: + Tambah Barang ke Rak -->
                    <button
                        type="button"
                        @click="openAddItemForm"
                        class="px-2.5 py-1.5 text-xs font-semibold rounded-lg bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 border border-amber-500/30 transition-all flex items-center gap-1.5 active:scale-95 shadow-sm"
                        title="Tambah barang baru langsung ke rak ini"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>+ Tambah Barang</span>
                    </button>
                </div>
            </div>

            <!-- Inline Form: Tambah Barang Baru Langsung ke Rak Ini -->
            <div v-if="activeBarcode && isAddingItem" class="mt-3 p-4 bg-slate-950 rounded-xl border border-amber-500/40 shadow-xl space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="p-1 rounded bg-amber-500/20 text-amber-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </span>
                        <div>
                            <h4 class="text-xs sm:text-sm font-bold text-white">
                                Tambah Barang Baru ke Rak {{ activeBarcode.rack_code }}
                            </h4>
                            <p class="text-[10px] text-slate-400">
                                Barang akan langsung didaftarkan dengan alokasi lokasi di rak ini.
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="closeAddItemForm"
                        class="p-1 rounded text-slate-400 hover:text-white text-xs"
                    >
                        &times;
                    </button>
                </div>

                <div v-if="addItemError" class="p-2.5 rounded bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs">
                    {{ addItemError }}
                </div>

                <form @submit.prevent="submitNewItem" class="space-y-3 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Kode SKU -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="font-semibold text-slate-300">
                                    Kode SKU / Aset <span class="text-rose-400">*</span>
                                </label>
                                <button
                                    type="button"
                                    @click="autoGenerateSku"
                                    class="text-[10px] text-amber-400 hover:underline font-mono"
                                >
                                    Auto Generate
                                </button>
                            </div>
                            <input
                                v-model="newItemForm.code"
                                type="text"
                                placeholder="cth: SKU-A01-102"
                                required
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-white font-mono uppercase focus:border-amber-500"
                            />
                        </div>

                        <!-- Nama Barang -->
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">
                                Nama Barang / Aset <span class="text-rose-400">*</span>
                            </label>
                            <input
                                v-model="newItemForm.name"
                                type="text"
                                placeholder="cth: Forklift Hydraulic Hose"
                                required
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-white focus:border-amber-500"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- Kategori -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="font-semibold text-slate-300">
                                    Kategori <span class="text-rose-400">*</span>
                                </label>
                                <button
                                    type="button"
                                    @click="isCustomCategory = !isCustomCategory"
                                    class="text-[10px] text-amber-400 hover:text-amber-300 transition-colors font-medium"
                                >
                                    {{ isCustomCategory ? 'Pilih List' : '+ Kategori Baru' }}
                                </button>
                            </div>

                            <!-- Dropdown List -->
                            <div v-if="!isCustomCategory">
                                <select
                                    :value="newItemForm.category"
                                    @change="if ($event.target.value === '__NEW__') { isCustomCategory = true; customCategoryInput = ''; } else { newItemForm.category = $event.target.value; }"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-white focus:border-amber-500"
                                    required
                                >
                                    <option v-for="cat in allCategories" :key="cat" :value="cat">
                                        {{ cat }}
                                    </option>
                                    <option value="__NEW__" class="text-amber-400 font-semibold bg-slate-950">
                                        + Ketik Kategori Kustom Baru...
                                    </option>
                                </select>
                            </div>

                            <!-- Custom Input Box -->
                            <div v-else class="space-y-1">
                                <div class="relative">
                                    <input
                                        v-model="customCategoryInput"
                                        type="text"
                                        placeholder="Ketik kategori baru..."
                                        required
                                        class="w-full bg-slate-900 border border-amber-500/60 rounded-lg pl-2.5 pr-14 py-1.5 text-xs text-white focus:border-amber-400 font-medium"
                                    />
                                    <button
                                        type="button"
                                        @click="isCustomCategory = false; newItemForm.category = allCategories[0] || 'Heavy Equipment'"
                                        class="absolute right-1.5 top-1/2 -translate-y-1/2 px-1.5 py-0.5 rounded text-[9px] bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700"
                                    >
                                        Batal
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Stok Awal -->
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">
                                Stok Awal <span class="text-rose-400">*</span>
                            </label>
                            <input
                                v-model.number="newItemForm.stock"
                                type="number"
                                min="1"
                                required
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-white font-mono focus:border-amber-500"
                            />
                        </div>

                        <!-- Satuan -->
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Satuan</label>
                            <input
                                v-model="newItemForm.unit"
                                type="text"
                                placeholder="Unit, Pcs, Box..."
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-white focus:border-amber-500"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Kondisi -->
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Kondisi</label>
                            <select
                                v-model="newItemForm.condition"
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-white focus:border-amber-500"
                            >
                                <option value="Good">Good (Baik)</option>
                                <option value="Fair">Fair (Cukup)</option>
                                <option value="Needs Maintenance">Needs Maintenance (Perlu Servis)</option>
                                <option value="New">New (Baru)</option>
                            </select>
                        </div>

                        <!-- Lokasi Penempatan (Read Only Chip) -->
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Lokasi Rak Dialokasikan</label>
                            <div class="px-2.5 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-amber-300 font-mono text-[11px] truncate">
                                Rak {{ activeBarcode.rack_code }} &bull; {{ activeBarcode.zone || 'Zone A' }}
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-800">
                        <button
                            type="button"
                            @click="closeAddItemForm"
                            class="px-3 py-1.5 text-xs font-semibold text-slate-400 hover:text-white rounded-lg transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="isSubmittingNewItem"
                            class="px-4 py-1.5 text-xs font-bold text-slate-950 bg-amber-400 hover:bg-amber-300 rounded-lg shadow-md transition-all flex items-center gap-1.5 disabled:opacity-50 active:scale-95"
                        >
                            <svg v-if="isSubmittingNewItem" class="animate-spin h-3.5 w-3.5 text-slate-950" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Simpan Barang ke Rak Ini</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Items List Container (Scrollable) -->
            <div v-if="activeBarcode" class="flex-1 overflow-y-auto space-y-3 pr-1 my-3">
                <div
                    v-for="item in rackItems"
                    :key="item.id"
                    class="bg-slate-950 border border-slate-800 rounded-xl p-3.5 transition-all"
                    :class="activeMutatingItem?.id === item.id ? 'border-amber-500/60 ring-1 ring-amber-500/20' : ''"
                >
                    <!-- Top line of Item -->
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h4 class="font-bold text-sm text-white">{{ item.name }}</h4>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="font-mono text-xs text-amber-300 font-semibold">{{ item.code }}</span>
                                <span class="text-slate-600">&bull;</span>
                                <span class="px-2 py-0.2 rounded text-[10px] font-medium border" :class="getCategoryBadge(item.category)">
                                    {{ item.category }}
                                </span>
                            </div>
                        </div>

                        <!-- Current Stock Big Badge -->
                        <div class="text-right">
                            <div class="text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Stok Saat Ini</div>
                            <div class="font-mono mt-0.5">
                                <span class="text-xl font-black text-white">{{ item.stock }}</span>
                                <span class="text-xs text-slate-400 ml-1 font-semibold">{{ item.unit }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons Row (IN & OUT) -->
                    <div class="flex items-center justify-between pt-3 mt-3 border-t border-slate-800/80">
                        <span class="text-[11px] text-slate-400">
                            Status:
                            <span :class="item.stock <= (item.min_stock || 5) ? 'text-amber-400 font-semibold' : 'text-emerald-400 font-semibold'">
                                {{ item.status || 'Ready' }}
                            </span>
                        </span>

                        <div class="flex items-center gap-2">
                            <!-- Tombol + IN (Masuk) -->
                            <button
                                type="button"
                                @click="startMutation(item, 'in')"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm"
                                :class="activeMutatingItem?.id === item.id && mutationType === 'in' ? 'bg-emerald-500 text-slate-950 shadow-emerald-500/30 ring-2 ring-emerald-400' : 'bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>+ IN (Masuk)</span>
                            </button>

                            <!-- Tombol - OUT (Keluar) -->
                            <button
                                type="button"
                                :disabled="item.stock <= 0"
                                @click="startMutation(item, 'out')"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm disabled:opacity-40"
                                :class="activeMutatingItem?.id === item.id && mutationType === 'out' ? 'bg-rose-500 text-white shadow-rose-500/30 ring-2 ring-rose-400' : 'bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30'"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                </svg>
                                <span>- OUT (Keluar)</span>
                            </button>
                        </div>
                    </div>

                    <!-- Expandable Quick Mutation Form for this item -->
                    <div
                        v-if="activeMutatingItem?.id === item.id"
                        class="mt-3 p-3.5 bg-slate-900 rounded-xl border border-slate-700 animate-fadeIn"
                    >
                        <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                            <span class="text-xs font-bold flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full" :class="mutationType === 'in' ? 'bg-emerald-400' : 'bg-rose-400'"></span>
                                <span :class="mutationType === 'in' ? 'text-emerald-400' : 'text-rose-400'">
                                    Mutasi {{ mutationType === 'in' ? 'TAMBAH (IN)' : 'PENGELUARAN (OUT)' }}
                                </span>
                            </span>
                            <span class="text-[11px] text-slate-400 font-mono">
                                {{ item.code }}
                            </span>
                        </div>

                        <form @submit.prevent="submitMutation" class="mt-3 space-y-3">
                            <!-- Input Jumlah / Step controls -->
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-300 mb-1">
                                    Jumlah Kuantitas ({{ item.unit }})
                                </label>
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        @click="adjustAmount(-10)"
                                        class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-xs font-mono font-bold"
                                    >
                                        -10
                                    </button>
                                    <button
                                        type="button"
                                        @click="adjustAmount(-1)"
                                        class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-xs font-mono font-bold"
                                    >
                                        -1
                                    </button>
                                    <input
                                        v-model.number="mutationAmount"
                                        type="number"
                                        min="1"
                                        :max="mutationType === 'out' ? item.stock : undefined"
                                        required
                                        class="w-24 text-center bg-slate-950 border border-slate-700 rounded-lg py-1.5 text-sm font-bold font-mono text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                                    />
                                    <button
                                        type="button"
                                        @click="adjustAmount(1)"
                                        class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-xs font-mono font-bold"
                                    >
                                        +1
                                    </button>
                                    <button
                                        type="button"
                                        @click="adjustAmount(10)"
                                        class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-xs font-mono font-bold"
                                    >
                                        +10
                                    </button>

                                    <!-- Quick MAX button for OUT -->
                                    <button
                                        v-if="mutationType === 'out'"
                                        type="button"
                                        @click="mutationAmount = item.stock"
                                        class="px-2 py-0.5 rounded text-[10px] font-mono bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40"
                                    >
                                        Keluarkan Semua ({{ item.stock }})
                                    </button>
                                </div>

                                <div v-if="mutationType === 'out'" class="text-[10px] text-slate-400 mt-1">
                                    Maksimal pengeluaran: <strong class="text-white">{{ item.stock }} {{ item.unit }}</strong>
                                </div>
                            </div>

                            <!-- Keterangan / Sumber / Tujuan -->
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-300 mb-1">
                                    Catatan / Referensi Mutasi
                                </label>
                                <input
                                    v-model="mutationNotes"
                                    type="text"
                                    :placeholder="mutationType === 'in' ? 'Contoh: PO Penerimaan #102, Restock Rutin' : 'Contoh: Surat Jalan #89, Pengambilan Tim Lapangan'"
                                    class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                                />

                                <!-- Quick Tags / Chips -->
                                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                    <span class="text-[10px] text-slate-400">Pilihan cepat:</span>
                                    <template v-if="mutationType === 'in'">
                                        <button
                                            type="button"
                                            @click="setQuickNote('Restock Vendor PO')"
                                            class="px-2 py-0.5 rounded text-[10px] bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700"
                                        >
                                            Restock Vendor
                                        </button>
                                        <button
                                            type="button"
                                            @click="setQuickNote('Penerimaan Material')"
                                            class="px-2 py-0.5 rounded text-[10px] bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700"
                                        >
                                            Penerimaan Material
                                        </button>
                                        <button
                                            type="button"
                                            @click="setQuickNote('Koreksi Stock Opname')"
                                            class="px-2 py-0.5 rounded text-[10px] bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700"
                                        >
                                            Koreksi Opname
                                        </button>
                                    </template>
                                    <template v-else>
                                        <button
                                            type="button"
                                            @click="setQuickNote('Pengambilan Operasional Lapangan')"
                                            class="px-2 py-0.5 rounded text-[10px] bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700"
                                        >
                                            Operasional Lapangan
                                        </button>
                                        <button
                                            type="button"
                                            @click="setQuickNote('Pemakaian Tim Maintenance')"
                                            class="px-2 py-0.5 rounded text-[10px] bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700"
                                        >
                                            Tim Maintenance
                                        </button>
                                        <button
                                            type="button"
                                            @click="setQuickNote('Pengeluaran Surat Jalan')"
                                            class="px-2 py-0.5 rounded text-[10px] bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700"
                                        >
                                            Surat Jalan
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center justify-end gap-2 pt-2">
                                <button
                                    type="button"
                                    @click="cancelMutation"
                                    class="px-3 py-1.5 text-xs text-slate-400 hover:text-white rounded-lg"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="isSubmittingMutation"
                                    class="px-4 py-2 rounded-lg text-xs font-bold text-slate-950 transition-all flex items-center gap-1.5 shadow-md disabled:opacity-50"
                                    :class="mutationType === 'in' ? 'bg-emerald-400 hover:bg-emerald-300 shadow-emerald-500/20' : 'bg-rose-400 hover:bg-rose-300 shadow-rose-500/20'"
                                >
                                    <svg v-if="isSubmittingMutation" class="animate-spin h-3.5 w-3.5 text-slate-950" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span>Konfirmasi Simpan Mutasi</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Empty State for Rack with no items -->
                <div v-if="rackItems.length === 0 && !isAddingItem" class="p-6 bg-slate-950 rounded-xl border border-slate-800 text-center">
                    <svg class="w-10 h-10 text-slate-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <h4 class="font-bold text-slate-300 text-sm">Rak ini belum memiliki item/barang tersimpan</h4>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto mb-4">
                        Rak [{{ activeBarcode.rack_code }}] saat ini kosong. Anda dapat langsung menambahkan barang pertama ke dalam rak ini sekarang:
                    </p>
                    <button
                        type="button"
                        @click="openAddItemForm"
                        class="px-4 py-2 text-xs font-bold text-slate-950 bg-amber-400 hover:bg-amber-300 rounded-lg shadow-md transition-all inline-flex items-center gap-2 active:scale-95"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Barang Pertama ke Rak Ini
                    </button>
                </div>
            </div>

            <!-- Initial Idle State (before scan) -->
            <div v-else class="flex-1 flex flex-col items-center justify-center py-8 text-center text-slate-500">
                <svg class="w-12 h-12 text-slate-700 mb-3 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <p class="text-sm font-semibold text-slate-400">Siap Menerima Scan Barcode</p>
                <p class="text-xs text-slate-600 mt-1 max-w-xs">
                    Ketik kode stiker barcode rak atau klik salah satu pilihan rak di atas untuk memuat inventaris barang.
                </p>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between pt-3 mt-auto border-t border-slate-800">
                <div class="text-[11px] text-slate-500 font-mono">
                    <span v-if="activeBarcode">Rak Terhubung: {{ activeBarcode.rack_code }} ({{ activeBarcode.barcode_code }})</span>
                    <span v-else>Tekan Esc untuk menutup</span>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-800 rounded-lg transition-colors"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fadeIn {
    animation: fadeIn 0.15s ease-out forwards;
}
</style>
