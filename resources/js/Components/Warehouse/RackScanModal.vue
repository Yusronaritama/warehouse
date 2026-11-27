<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import axios from 'axios';
import LiveCameraScanner from '@/Components/Warehouse/LiveCameraScanner.vue';

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
const showCameraScanner = ref(false);

function handleCameraScanned(code) {
    scanInput.value = code;
    executeScan(code);
}

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
    'Bahan Baku',
    'Painting',
    'Masking',
    'Buffing',
    'Packaging',
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
    category: 'Bahan Baku',
    stock: 1,
    unit: 'Pcs',
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
        case 'Bahan Baku':
            return 'bg-[#81A1C1]/15 text-[#81A1C1] border-[#81A1C1]/30';
        case 'Painting':
            return 'bg-[#B48EAD]/15 text-[#B48EAD] border-[#B48EAD]/30';
        case 'Masking':
            return 'bg-[#EBCB8B]/15 text-[#EBCB8B] border-[#EBCB8B]/30';
        case 'Buffing':
            return 'bg-[#A3BE8C]/15 text-[#A3BE8C] border-[#A3BE8C]/30';
        case 'Packaging':
            return 'bg-[#88C0D0]/15 text-[#88C0D0] border-[#88C0D0]/30';
        case 'Heavy Equipment':
            return 'bg-[#EBCB8B]/15 text-[#EBCB8B] border-[#EBCB8B]/30';
        case 'Material Handlers':
            return 'bg-[#81A1C1]/15 text-[#81A1C1] border-[#81A1C1]/30';
        case 'Safety Gear':
            return 'bg-[#A3BE8C]/15 text-[#A3BE8C] border-[#A3BE8C]/30';
        case 'Electronics':
            return 'bg-[#B48EAD]/15 text-[#B48EAD] border-[#B48EAD]/30';
        case 'Spare Parts':
            return 'bg-[#88C0D0]/15 text-[#88C0D0] border-[#88C0D0]/30';
        default:
            return 'bg-[#434C5E]/60 text-[#D8DEE9] border-[#4C566A]/60';
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
        class="fixed inset-0 z-[70] flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs"
    >
        <div class="bg-[#3B4252] border border-[#4C566A]/60 rounded-2xl max-w-2xl w-full p-5 sm:p-6 shadow-2xl relative text-[#ECEFF4] flex flex-col max-h-[92vh]">
            <!-- Modal Header (Nordic Dim) -->
            <div class="flex items-center justify-between pb-4 border-b border-[#4C566A]/60">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-xl bg-[#434C5E] border border-[#4C566A] text-[#88C0D0] relative">
                        <span class="absolute -top-1 -right-1 flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#88C0D0] opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-[#88C0D0]"></span>
                        </span>
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-[#ECEFF4] tracking-wide">
                            Scan Barcode Rak & Mutasi Stok
                        </h3>
                        <p class="text-xs text-[#A5B1C2] mt-0.5">
                            Pindai stiker rak fisik untuk melihat isi barang, menambah item, dan update stok (IN / OUT).
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    @click="closeModal"
                    class="p-2 rounded-lg text-[#A5B1C2] hover:text-[#ECEFF4] hover:bg-[#434C5E] transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Scanner Input Box -->
            <div class="mt-4 p-3.5 bg-[#2E3440] rounded-xl border border-[#4C566A]/60">
                <form @submit.prevent="executeScan()" class="flex gap-2">
                    <div class="relative flex-1">
                        <input
                            ref="scanInputRef"
                            v-model="scanInput"
                            type="text"
                            placeholder="Ketik atau scan barcode (e.g. BC-A01-8942 atau RAK A-01)..."
                            class="w-full bg-[#3B4252] border border-[#4C566A] rounded-lg pl-3 pr-8 py-2 text-xs sm:text-sm text-[#ECEFF4] placeholder-[#8C96A9] font-mono uppercase focus:border-[#88C0D0] focus:ring-1 focus:ring-[#88C0D0]"
                        />
                        <button
                            v-if="scanInput"
                            type="button"
                            @click="scanInput = ''; scanInputRef?.focus()"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#A5B1C2] hover:text-[#ECEFF4] text-xs"
                        >
                            &times;
                        </button>
                    </div>

                    <button
                        type="button"
                        @click="showCameraScanner = true"
                        class="px-3 py-2 bg-[#434C5E] hover:bg-[#4C566A] text-[#88C0D0] border border-[#4C566A] rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 shadow-xs active:scale-95"
                        title="Pindai menggunakan kamera HP"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="hidden sm:inline">Kamera HP</span>
                    </button>

                    <button
                        type="submit"
                        :disabled="isScanning"
                        class="px-4 py-2 bg-[#5E81AC] hover:bg-[#81A1C1] text-[#ECEFF4] rounded-lg text-xs font-bold transition-all disabled:opacity-50 flex items-center gap-1.5 shadow-xs active:scale-95"
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
                <div v-if="displayRacks && displayRacks.length > 0" class="mt-2.5 pt-2 border-t border-[#4C566A]/60">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[10px] text-[#A5B1C2] font-semibold flex items-center gap-1.5">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#A3BE8C] animate-pulse"></span>
                            Pilih cepat rak ({{ displayRacks.length }} rak terdaftar di Database):
                        </span>
                        <button
                            type="button"
                            @click="fetchLiveRacks"
                            title="Muat ulang rak terbaru dari DB"
                            class="text-[10px] text-[#A5B1C2] hover:text-[#88C0D0] transition-colors flex items-center gap-1 font-mono"
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
                            :class="activeBarcode?.rack_code === (r.code || r) ? 'bg-[#5E81AC] text-[#ECEFF4] border border-[#81A1C1] font-bold' : 'bg-[#3B4252] hover:bg-[#434C5E] text-[#D8DEE9] border border-[#4C566A]/60'"
                        >
                            <span>{{ r.code || r }}</span>
                            <span v-if="r.items_count !== undefined" class="text-[9px] text-[#A5B1C2] font-sans">
                                ({{ r.items_count }})
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Scan Error Feedback -->
            <div v-if="scanError" class="mt-3 p-3 bg-[#BF616A]/15 border border-[#BF616A]/30 rounded-xl text-xs text-[#BF616A] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#BF616A] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ scanError }}</span>
                </div>
                <button type="button" @click="scanError = null" class="text-[#A5B1C2] hover:text-[#ECEFF4]">&times;</button>
            </div>

            <!-- Mutation Alert Banner -->
            <div
                v-if="mutationAlert"
                class="mt-3 p-3 rounded-xl text-xs flex items-center justify-between border"
                :class="mutationAlert.type === 'success' ? 'bg-[#A3BE8C]/15 text-[#A3BE8C] border-[#A3BE8C]/30' : 'bg-[#BF616A]/15 text-[#BF616A] border-[#BF616A]/30'"
            >
                <div class="flex items-center gap-2">
                    <svg v-if="mutationAlert.type === 'success'" class="w-4 h-4 text-[#A3BE8C] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg v-else class="w-4 h-4 text-[#BF616A] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ mutationAlert.message }}</span>
                </div>
                <button type="button" @click="mutationAlert = null" class="text-[#A5B1C2] hover:text-[#ECEFF4] text-xs">&times;</button>
            </div>

            <!-- Active Rack Details Strip -->
            <div v-if="activeBarcode" class="mt-3.5 p-3.5 bg-[#2E3440] rounded-xl border border-[#4C566A]/60 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-base font-black text-[#88C0D0] font-mono tracking-tight">
                            RAK {{ activeBarcode.rack_code }}
                        </span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-[#3B4252] text-[#D8DEE9] border border-[#4C566A]/60">
                            {{ activeBarcode.barcode_code }}
                        </span>
                    </div>
                    <div class="text-[11px] text-[#A5B1C2] mt-0.5">
                        {{ activeBarcode.zone || 'Zone A' }} &bull; {{ activeBarcode.label }}
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <div class="text-right mr-1 hidden sm:block">
                        <span class="text-[10px] uppercase tracking-wider text-[#A5B1C2] font-semibold block">Total Item Rak</span>
                        <span class="font-mono text-sm font-bold text-[#ECEFF4]">{{ rackItems.length }} SKU</span>
                    </div>

                    <!-- Shortcut button: Buka Halaman Kelola Rak Penuh -->
                    <Link
                        :href="route('racks.show', activeBarcode.rack_code)"
                        class="px-3 py-1.5 text-xs font-bold rounded-lg bg-[#5E81AC] hover:bg-[#81A1C1] text-[#ECEFF4] transition-all flex items-center gap-1.5 active:scale-95 shadow-xs"
                        title="Buka halaman penuh untuk CRUD barang & ubah nama rak"
                    >
                        <span>Kelola Rak (CRUD) &rarr;</span>
                    </Link>

                    <!-- Shortcut button: + Tambah Barang ke Rak -->
                    <button
                        type="button"
                        @click="openAddItemForm"
                        class="px-2.5 py-1.5 text-xs font-semibold rounded-lg bg-[#434C5E] hover:bg-[#4C566A] text-[#ECEFF4] border border-[#4C566A] transition-all flex items-center gap-1.5 active:scale-95 shadow-xs"
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
            <div v-if="activeBarcode && isAddingItem" class="mt-3 p-4 bg-[#2E3440] rounded-xl border border-[#88C0D0]/40 shadow-xl space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-[#4C566A]/60">
                    <div class="flex items-center gap-2">
                        <span class="p-1 rounded bg-[#88C0D0]/20 text-[#88C0D0]">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </span>
                        <div>
                            <h4 class="text-xs sm:text-sm font-bold text-[#ECEFF4]">
                                Tambah Barang Baru ke Rak {{ activeBarcode.rack_code }}
                            </h4>
                            <p class="text-[10px] text-[#A5B1C2]">
                                Barang akan langsung didaftarkan dengan alokasi lokasi di rak ini.
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="closeAddItemForm"
                        class="p-1 rounded text-[#A5B1C2] hover:text-[#ECEFF4] text-xs"
                    >
                        &times;
                    </button>
                </div>

                <div v-if="addItemError" class="p-2.5 rounded bg-[#BF616A]/15 border border-[#BF616A]/30 text-[#BF616A] text-xs">
                    {{ addItemError }}
                </div>

                <form @submit.prevent="submitNewItem" class="space-y-3 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Kode SKU -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="font-semibold text-[#A5B1C2]">
                                    Kode SKU / Aset <span class="text-[#BF616A]">*</span>
                                </label>
                                <button
                                    type="button"
                                    @click="autoGenerateSku"
                                    class="text-[10px] text-[#88C0D0] hover:underline font-mono"
                                >
                                    Auto Generate
                                </button>
                            </div>
                            <input
                                v-model="newItemForm.code"
                                type="text"
                                placeholder="cth: SKU-A01-102"
                                required
                                class="w-full bg-[#3B4252] border border-[#4C566A] rounded-lg px-2.5 py-1.5 text-xs text-[#ECEFF4] font-mono uppercase focus:border-[#88C0D0]"
                            />
                        </div>

                        <!-- Nama Barang -->
                        <div>
                            <label class="block font-semibold text-[#A5B1C2] mb-1">
                                Nama Barang / Aset <span class="text-[#BF616A]">*</span>
                            </label>
                            <input
                                v-model="newItemForm.name"
                                type="text"
                                placeholder="cth: Forklift Hydraulic Hose"
                                required
                                class="w-full bg-[#3B4252] border border-[#4C566A] rounded-lg px-2.5 py-1.5 text-xs text-[#ECEFF4] focus:border-[#88C0D0]"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- Kategori -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="font-semibold text-[#A5B1C2]">
                                    Kategori <span class="text-[#BF616A]">*</span>
                                </label>
                                <button
                                    type="button"
                                    @click="isCustomCategory = !isCustomCategory"
                                    class="text-[10px] text-[#88C0D0] hover:text-[#81A1C1] transition-colors font-medium"
                                >
                                    {{ isCustomCategory ? 'Pilih List' : '+ Kategori Baru' }}
                                </button>
                            </div>

                            <!-- Dropdown List -->
                            <div v-if="!isCustomCategory">
                                <select
                                    :value="newItemForm.category"
                                    @change="if ($event.target.value === '__NEW__') { isCustomCategory = true; customCategoryInput = ''; } else { newItemForm.category = $event.target.value; }"
                                    class="w-full bg-[#3B4252] border border-[#4C566A] rounded-lg px-2.5 py-1.5 text-xs text-[#ECEFF4] focus:border-[#88C0D0]"
                                    required
                                >
                                    <option v-for="cat in allCategories" :key="cat" :value="cat">
                                        {{ cat }}
                                    </option>
                                    <option value="__NEW__" class="text-[#EBCB8B] font-semibold bg-[#2E3440]">
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
                                        class="w-full bg-[#3B4252] border border-[#88C0D0]/60 rounded-lg pl-2.5 pr-14 py-1.5 text-xs text-[#ECEFF4] focus:border-[#88C0D0] font-medium"
                                    />
                                    <button
                                        type="button"
                                        @click="isCustomCategory = false; newItemForm.category = allCategories[0] || 'Bahan Baku'"
                                        class="absolute right-1.5 top-1/2 -translate-y-1/2 px-1.5 py-0.5 rounded text-[9px] bg-[#434C5E] hover:bg-[#4C566A] text-[#D8DEE9] border border-[#4C566A]"
                                    >
                                        Batal
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Stok Awal -->
                        <div>
                            <label class="block font-semibold text-[#A5B1C2] mb-1">
                                Stok Awal <span class="text-[#BF616A]">*</span>
                            </label>
                            <input
                                v-model.number="newItemForm.stock"
                                type="number"
                                min="1"
                                required
                                class="w-full bg-[#3B4252] border border-[#4C566A] rounded-lg px-2.5 py-1.5 text-xs text-[#ECEFF4] font-mono focus:border-[#88C0D0]"
                            />
                        </div>

                        <!-- Satuan -->
                        <div>
                            <label class="block font-semibold text-[#A5B1C2] mb-1">
                                Satuan <span class="text-[#BF616A]">*</span>
                            </label>
                            <select
                                v-model="newItemForm.unit"
                                class="w-full bg-[#3B4252] border border-[#4C566A] rounded-lg px-2.5 py-1.5 text-xs text-[#ECEFF4] focus:border-[#88C0D0]"
                                required
                            >
                                <option value="Pcs">Pcs</option>
                                <option value="Liter">Liter</option>
                                <option value="Roll">Roll</option>
                                <option value="Botol">Botol</option>
                                <option value="Box">Box</option>
                                <option value="Unit">Unit</option>
                                <option value="Kg">Kg</option>
                                <option value="Meter">Meter</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Kondisi -->
                        <div>
                            <label class="block font-semibold text-[#A5B1C2] mb-1">Kondisi</label>
                            <select
                                v-model="newItemForm.condition"
                                class="w-full bg-[#3B4252] border border-[#4C566A] rounded-lg px-2.5 py-1.5 text-xs text-[#ECEFF4] focus:border-[#88C0D0]"
                            >
                                <option value="Good">Good (Baik)</option>
                                <option value="Fair">Fair (Cukup)</option>
                                <option value="Needs Maintenance">Needs Maintenance (Perlu Servis)</option>
                                <option value="New">New (Baru)</option>
                            </select>
                        </div>

                        <!-- Lokasi Penempatan (Read Only Chip) -->
                        <div>
                            <label class="block font-semibold text-[#A5B1C2] mb-1">Lokasi Rak Dialokasikan</label>
                            <div class="px-2.5 py-1.5 bg-[#3B4252] border border-[#4C566A]/60 rounded-lg text-[#88C0D0] font-mono text-[11px] truncate">
                                Rak {{ activeBarcode.rack_code }} &bull; {{ activeBarcode.zone || 'Zone A' }}
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#4C566A]/60">
                        <button
                            type="button"
                            @click="closeAddItemForm"
                            class="px-3 py-1.5 text-xs font-semibold text-[#A5B1C2] hover:text-[#ECEFF4] rounded-lg transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="isSubmittingNewItem"
                            class="px-4 py-1.5 text-xs font-bold text-[#ECEFF4] bg-[#5E81AC] hover:bg-[#81A1C1] rounded-lg shadow-md transition-all flex items-center gap-1.5 disabled:opacity-50 active:scale-95"
                        >
                            <svg v-if="isSubmittingNewItem" class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24">
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
                    class="bg-[#2E3440] border border-[#4C566A]/60 rounded-xl p-3.5 transition-all"
                    :class="activeMutatingItem?.id === item.id ? 'border-[#88C0D0]/60 ring-1 ring-[#88C0D0]/20' : ''"
                >
                    <!-- Top line of Item -->
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h4 class="font-bold text-sm text-[#ECEFF4]">{{ item.name }}</h4>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="font-mono text-xs text-[#88C0D0] font-semibold">{{ item.code }}</span>
                                <span class="text-[#4C566A]">&bull;</span>
                                <span class="px-2 py-0.2 rounded text-[10px] font-medium border" :class="getCategoryBadge(item.category)">
                                    {{ item.category }}
                                </span>
                            </div>
                        </div>

                        <!-- Current Stock Big Badge -->
                        <div class="text-right">
                            <div class="text-[10px] uppercase tracking-wider text-[#A5B1C2] font-semibold">Stok Saat Ini</div>
                            <div class="font-mono mt-0.5">
                                <span class="text-xl font-black text-[#ECEFF4]">{{ item.stock }}</span>
                                <span class="text-xs text-[#A5B1C2] ml-1 font-semibold">{{ item.unit }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons Row (IN & OUT) -->
                    <div class="flex items-center justify-between pt-3 mt-3 border-t border-[#4C566A]/60">
                        <span class="text-[11px] text-[#A5B1C2]">
                            Status:
                            <span :class="item.stock <= (item.min_stock || 5) ? 'text-[#EBCB8B] font-semibold' : 'text-[#A3BE8C] font-semibold'">
                                {{ item.status || 'Ready' }}
                            </span>
                        </span>

                        <div class="flex items-center gap-2">
                            <!-- Tombol + IN (Masuk) -->
                            <button
                                type="button"
                                @click="startMutation(item, 'in')"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 shadow-xs"
                                :class="activeMutatingItem?.id === item.id && mutationType === 'in' ? 'bg-[#A3BE8C] text-[#2E3440] shadow-md ring-2 ring-[#A3BE8C]' : 'bg-[#A3BE8C]/15 hover:bg-[#A3BE8C]/25 text-[#A3BE8C] border border-[#A3BE8C]/30'"
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
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 shadow-xs disabled:opacity-40"
                                :class="activeMutatingItem?.id === item.id && mutationType === 'out' ? 'bg-[#BF616A] text-[#ECEFF4] shadow-md ring-2 ring-[#BF616A]' : 'bg-[#BF616A]/15 hover:bg-[#BF616A]/25 text-[#BF616A] border border-[#BF616A]/30'"
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
                        class="mt-3 p-3.5 bg-[#3B4252] rounded-xl border border-[#4C566A]/60 animate-fadeIn"
                    >
                        <div class="flex items-center justify-between pb-2 border-b border-[#4C566A]/60">
                            <span class="text-xs font-bold flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full" :class="mutationType === 'in' ? 'bg-[#A3BE8C]' : 'bg-[#BF616A]'"></span>
                                <span :class="mutationType === 'in' ? 'text-[#A3BE8C]' : 'text-[#BF616A]'">
                                    Mutasi {{ mutationType === 'in' ? 'TAMBAH (IN)' : 'PENGELUARAN (OUT)' }}
                                </span>
                            </span>
                            <span class="text-[11px] text-[#A5B1C2] font-mono">
                                {{ item.code }}
                            </span>
                        </div>

                        <form @submit.prevent="submitMutation" class="mt-3 space-y-3">
                            <!-- Input Jumlah / Step controls -->
                            <div>
                                <label class="block text-[11px] font-semibold text-[#A5B1C2] mb-1">
                                    Jumlah Kuantitas ({{ item.unit }})
                                </label>
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        @click="adjustAmount(-10)"
                                        class="px-2.5 py-1.5 bg-[#2E3440] hover:bg-[#434C5E] text-[#D8DEE9] border border-[#4C566A]/60 rounded-lg text-xs font-mono font-bold"
                                    >
                                        -10
                                    </button>
                                    <button
                                        type="button"
                                        @click="adjustAmount(-1)"
                                        class="px-2.5 py-1.5 bg-[#2E3440] hover:bg-[#434C5E] text-[#D8DEE9] border border-[#4C566A]/60 rounded-lg text-xs font-mono font-bold"
                                    >
                                        -1
                                    </button>
                                    <input
                                        v-model.number="mutationAmount"
                                        type="number"
                                        min="1"
                                        :max="mutationType === 'out' ? item.stock : undefined"
                                        required
                                        class="w-24 text-center bg-[#2E3440] border border-[#4C566A] rounded-lg py-1.5 text-sm font-bold font-mono text-[#ECEFF4] focus:border-[#88C0D0] focus:ring-1 focus:ring-[#88C0D0]"
                                    />
                                    <button
                                        type="button"
                                        @click="adjustAmount(1)"
                                        class="px-2.5 py-1.5 bg-[#2E3440] hover:bg-[#434C5E] text-[#D8DEE9] border border-[#4C566A]/60 rounded-lg text-xs font-mono font-bold"
                                    >
                                        +1
                                    </button>
                                    <button
                                        type="button"
                                        @click="adjustAmount(10)"
                                        class="px-2.5 py-1.5 bg-[#2E3440] hover:bg-[#434C5E] text-[#D8DEE9] border border-[#4C566A]/60 rounded-lg text-xs font-mono font-bold"
                                    >
                                        +10
                                    </button>

                                    <!-- Quick MAX button for OUT -->
                                    <button
                                        v-if="mutationType === 'out'"
                                        type="button"
                                        @click="mutationAmount = item.stock"
                                        class="px-2 py-0.5 rounded text-[10px] font-mono bg-[#BF616A]/15 hover:bg-[#BF616A]/25 text-[#BF616A] border border-[#BF616A]/30"
                                    >
                                        Keluarkan Semua ({{ item.stock }})
                                    </button>
                                </div>

                                <div v-if="mutationType === 'out'" class="text-[10px] text-[#A5B1C2] mt-1">
                                    Maksimal pengeluaran: <strong class="text-[#ECEFF4]">{{ item.stock }} {{ item.unit }}</strong>
                                </div>
                            </div>

                            <!-- Keterangan / Sumber / Tujuan -->
                            <div>
                                <label class="block text-[11px] font-semibold text-[#A5B1C2] mb-1">
                                    Catatan / Referensi Mutasi
                                </label>
                                <input
                                    v-model="mutationNotes"
                                    type="text"
                                    :placeholder="mutationType === 'in' ? 'Contoh: PO Penerimaan #102, Restock Rutin' : 'Contoh: Surat Jalan #89, Pengambilan Tim Lapangan'"
                                    class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3 py-1.5 text-xs text-[#ECEFF4] placeholder-[#8C96A9] focus:border-[#88C0D0] focus:ring-1 focus:ring-[#88C0D0]"
                                />

                                <!-- Quick Tags / Chips -->
                                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                    <span class="text-[10px] text-[#A5B1C2]">Pilihan cepat:</span>
                                    <template v-if="mutationType === 'in'">
                                        <button
                                            type="button"
                                            @click="setQuickNote('Restock Vendor PO')"
                                            class="px-2 py-0.5 rounded text-[10px] bg-[#2E3440] hover:bg-[#434C5E] text-[#D8DEE9] border border-[#4C566A]/60"
                                        >
                                            Restock Vendor
                                        </button>
                                        <button
                                            type="button"
                                            @click="setQuickNote('Penerimaan Material')"
                                            class="px-2 py-0.5 rounded text-[10px] bg-[#2E3440] hover:bg-[#434C5E] text-[#D8DEE9] border border-[#4C566A]/60"
                                        >
                                            Penerimaan Material
                                        </button>
                                        <button
                                            type="button"
                                            @click="setQuickNote('Koreksi Stock Opname')"
                                            class="px-2 py-0.5 rounded text-[10px] bg-[#2E3440] hover:bg-[#434C5E] text-[#D8DEE9] border border-[#4C566A]/60"
                                        >
                                            Koreksi Opname
                                        </button>
                                    </template>
                                    <template v-else>
                                        <button
                                            type="button"
                                            @click="setQuickNote('Pengambilan Operasional Lapangan')"
                                            class="px-2 py-0.5 rounded text-[10px] bg-[#2E3440] hover:bg-[#434C5E] text-[#D8DEE9] border border-[#4C566A]/60"
                                        >
                                            Operasional Lapangan
                                        </button>
                                        <button
                                            type="button"
                                            @click="setQuickNote('Pemakaian Tim Maintenance')"
                                            class="px-2 py-0.5 rounded text-[10px] bg-[#2E3440] hover:bg-[#434C5E] text-[#D8DEE9] border border-[#4C566A]/60"
                                        >
                                            Tim Maintenance
                                        </button>
                                        <button
                                            type="button"
                                            @click="setQuickNote('Pengeluaran Surat Jalan')"
                                            class="px-2 py-0.5 rounded text-[10px] bg-[#2E3440] hover:bg-[#434C5E] text-[#D8DEE9] border border-[#4C566A]/60"
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
                                    class="px-3 py-1.5 text-xs text-[#A5B1C2] hover:text-[#ECEFF4] rounded-lg transition-colors"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="isSubmittingMutation"
                                    class="px-4 py-2 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 shadow-md disabled:opacity-50"
                                    :class="mutationType === 'in' ? 'bg-[#A3BE8C] hover:bg-[#A3BE8C]/90 text-[#2E3440]' : 'bg-[#BF616A] hover:bg-[#BF616A]/90 text-[#ECEFF4]'"
                                >
                                    <svg v-if="isSubmittingMutation" class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24">
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
                <div v-if="rackItems.length === 0 && !isAddingItem" class="p-6 bg-[#2E3440] rounded-xl border border-[#4C566A]/60 text-center">
                    <svg class="w-10 h-10 text-[#4C566A] mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <h4 class="font-bold text-[#ECEFF4] text-sm">Rak ini belum memiliki item/barang tersimpan</h4>
                    <p class="text-xs text-[#A5B1C2] mt-1 max-w-sm mx-auto mb-4">
                        Rak [{{ activeBarcode.rack_code }}] saat ini kosong. Anda dapat langsung menambahkan barang pertama ke dalam rak ini sekarang:
                    </p>
                    <button
                        type="button"
                        @click="openAddItemForm"
                        class="px-4 py-2 text-xs font-bold text-[#ECEFF4] bg-[#5E81AC] hover:bg-[#81A1C1] rounded-lg shadow-md transition-all inline-flex items-center gap-2 active:scale-95"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Barang Pertama ke Rak Ini
                    </button>
                </div>
            </div>

            <!-- Initial Idle State (before scan) -->
            <div v-else class="flex-1 flex flex-col items-center justify-center py-8 text-center text-[#A5B1C2]">
                <svg class="w-12 h-12 text-[#4C566A] mb-3 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <p class="text-sm font-semibold text-[#D8DEE9]">Siap Menerima Scan Barcode</p>
                <p class="text-xs text-[#8C96A9] mt-1 max-w-xs">
                    Ketik kode stiker barcode rak atau klik salah satu pilihan rak di atas untuk memuat inventaris barang.
                </p>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between pt-3 mt-auto border-t border-[#4C566A]/60">
                <div class="text-[11px] text-[#A5B1C2] font-mono">
                    <span v-if="activeBarcode">Rak Terhubung: {{ activeBarcode.rack_code }} ({{ activeBarcode.barcode_code }})</span>
                    <span v-else>Tekan Esc untuk menutup</span>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-4 py-2 text-xs font-semibold text-[#D8DEE9] hover:bg-[#434C5E] rounded-lg transition-colors"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- Live Camera Scanner for Mobile / Laptop -->
        <LiveCameraScanner
            v-model="showCameraScanner"
            @scanned="handleCameraScanned"
        />
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
