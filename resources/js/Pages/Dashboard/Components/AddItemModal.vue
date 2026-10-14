<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    rackLocations: { type: Array, required: true },
    availableCategories: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue', 'save']);

const DEFAULT_CATEGORIES = [
    'Heavy Equipment',
    'Material Handlers',
    'Safety Gear',
    'Electronics',
    'Spare Parts',
];

const STORAGE_KEY = 'warehouse_custom_categories';

const savedCategories = ref([]);
const isCustomCategory = ref(false);
const customCategoryInput = ref('');

// Dynamic Live Racks & Location State
const liveRacks = ref([]);
const isCustomLocation = ref(false);
const selectedRackCode = ref('');
const shelfDetail = ref('Rak 1');
const customLocationInput = ref('');

// Load stored categories from localStorage
function loadSavedCategories() {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
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

// Fetch live rack list from DB
async function fetchLiveRacks() {
    try {
        const url = typeof route === 'function' ? route('barcodes.racksList') : '/barcodes/racks-list';
        const res = await axios.get(url);
        if (Array.isArray(res.data) && res.data.length > 0) {
            liveRacks.value = res.data;
        }
    } catch (e) {
        console.warn('Gagal memuat live racks di AddItemModal:', e);
    }
}

// Save category permanently to localStorage
function saveCustomCategory(categoryName) {
    const trimmed = (categoryName || '').trim();
    if (!trimmed) return;

    if (!savedCategories.value.includes(trimmed) && !DEFAULT_CATEGORIES.includes(trimmed)) {
        savedCategories.value.push(trimmed);
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(savedCategories.value));
        } catch (e) {
            console.warn('Gagal menyimpan kategori ke localStorage:', e);
        }
    }
}

// Merged unique categories list
const allCategories = computed(() => {
    const set = new Set([
        ...DEFAULT_CATEGORIES,
        ...(props.availableCategories || []),
        ...savedCategories.value,
    ]);
    return Array.from(set).filter(Boolean);
});

// Merged unique racks list
const availableRacks = computed(() => {
    const map = new Map();

    if (Array.isArray(props.rackLocations)) {
        props.rackLocations.forEach((r) => {
            if (r && r.code) {
                map.set(r.code, {
                    code: r.code,
                    zone: r.zone || 'Zone A',
                    type: r.type || 'Standard Rack',
                    items_count: r.items_count ?? r.itemsCount ?? 0,
                });
            }
        });
    }

    if (Array.isArray(liveRacks.value)) {
        liveRacks.value.forEach((r) => {
            if (r && r.code) {
                map.set(r.code, {
                    code: r.code,
                    zone: r.zone || 'Zone A',
                    type: r.type || 'Standard Rack',
                    items_count: r.items_count ?? 0,
                });
            }
        });
    }

    const list = Array.from(map.values());
    list.sort((a, b) => a.code.localeCompare(b.code));
    return list;
});

function cleanZone(zone) {
    if (!zone) return 'Zone A';
    return zone.toLowerCase().startsWith('zone') ? zone : `Zone ${zone}`;
}

function formatLocation(code, shelf) {
    if (!code) return '';
    const rack = availableRacks.value.find((r) => r.code === code);
    const zone = cleanZone(rack?.zone);
    const s = (shelf || '').trim();
    return s ? `${zone} (${code}) ${s}` : `${zone} (${code})`;
}

function updateLocationFromSelect() {
    if (selectedRackCode.value) {
        form.value.location = formatLocation(selectedRackCode.value, shelfDetail.value);
    }
}

const form = ref({
    code: '',
    name: '',
    category: 'Heavy Equipment',
    location: 'Zone A (A-01) Rak 1',
    stock: 1,
    unit: 'Pcs',
    base_unit: 'Pcs',
    has_packaging: false,
    packaging_unit: 'Box',
    conversion_factor: 20,
    initial_unit: 'packaging',
    status: 'Ready',
    condition: 'Good',
    minStock: 2,
});

onMounted(() => {
    loadSavedCategories();
    fetchLiveRacks();
});

watch(
    availableRacks,
    (racks) => {
        if (racks.length > 0 && !selectedRackCode.value) {
            selectedRackCode.value = racks[0].code;
            if (!isCustomLocation.value) {
                updateLocationFromSelect();
            }
        }
    },
    { immediate: true }
);

watch(shelfDetail, () => {
    if (!isCustomLocation.value) {
        updateLocationFromSelect();
    }
});

watch(customLocationInput, (val) => {
    if (isCustomLocation.value) {
        form.value.location = val;
    }
});

watch(
    () => props.modelValue,
    (isOpen) => {
        if (isOpen) {
            loadSavedCategories();
            fetchLiveRacks();
            isCustomCategory.value = false;
            customCategoryInput.value = '';
            isCustomLocation.value = false;
            customLocationInput.value = '';

            if (!selectedRackCode.value && availableRacks.value.length > 0) {
                selectedRackCode.value = availableRacks.value[0].code;
            }
            if (selectedRackCode.value) {
                updateLocationFromSelect();
            }
        }
    }
);

function onCategorySelect(event) {
    const val = event.target.value;
    if (val === '__NEW_CATEGORY__') {
        isCustomCategory.value = true;
        customCategoryInput.value = '';
        form.value.category = '';
    } else {
        form.value.category = val;
    }
}

function selectCategory(cat) {
    form.value.category = cat;
    isCustomCategory.value = false;
}

function toggleCustomCategory() {
    isCustomCategory.value = !isCustomCategory.value;
    if (isCustomCategory.value) {
        customCategoryInput.value = '';
        form.value.category = '';
    } else {
        form.value.category = allCategories.value[0] || 'Heavy Equipment';
    }
}

function onRackSelect(event) {
    const val = event.target.value;
    if (val === '__CUSTOM_LOCATION__') {
        isCustomLocation.value = true;
        customLocationInput.value = form.value.location || '';
    } else {
        selectedRackCode.value = val;
        isCustomLocation.value = false;
        updateLocationFromSelect();
    }
}

function selectQuickRack(rackCode) {
    selectedRackCode.value = rackCode;
    isCustomLocation.value = false;
    updateLocationFromSelect();
}

function toggleCustomLocation() {
    isCustomLocation.value = !isCustomLocation.value;
    if (isCustomLocation.value) {
        customLocationInput.value = form.value.location || '';
    } else {
        if (!selectedRackCode.value && availableRacks.value.length > 0) {
            selectedRackCode.value = availableRacks.value[0].code;
        }
        updateLocationFromSelect();
    }
}

function close() {
    emit('update:modelValue', false);
}

function submit() {
    let finalCategory = form.value.category;
    if (isCustomCategory.value) {
        finalCategory = customCategoryInput.value.trim();
        if (finalCategory) {
            saveCustomCategory(finalCategory);
            form.value.category = finalCategory;
        }
    }

    let finalLocation = form.value.location;
    if (isCustomLocation.value) {
        finalLocation = customLocationInput.value.trim();
        if (finalLocation) {
            form.value.location = finalLocation;
        }
    } else if (selectedRackCode.value) {
        finalLocation = formatLocation(selectedRackCode.value, shelfDetail.value);
        form.value.location = finalLocation;
    }

    if (!form.value.name || !form.value.code || !finalLocation || !finalCategory) {
        alert('Mohon lengkapi kode SKU, nama barang, kategori, dan lokasi rak!');
        return;
    }

    emit('save', {
        ...form.value,
        category: finalCategory,
        location: finalLocation,
        unit: form.value.base_unit || form.value.unit || 'Unit',
        base_unit: form.value.base_unit || form.value.unit || 'Unit',
        packaging_unit: form.value.has_packaging ? form.value.packaging_unit : null,
        conversion_factor: form.value.has_packaging ? (parseInt(form.value.conversion_factor) || 1) : 1,
        initial_unit: form.value.has_packaging ? (form.value.initial_unit === 'packaging' ? form.value.packaging_unit : form.value.base_unit) : form.value.base_unit,
    });
    close();

    form.value = {
        code: '',
        name: '',
        category: finalCategory || 'Heavy Equipment',
        location: finalLocation || 'Zone A (A-01) Rak 1',
        stock: 1,
        unit: 'Pcs',
        base_unit: 'Pcs',
        has_packaging: false,
        packaging_unit: 'Box',
        conversion_factor: 20,
        initial_unit: 'packaging',
        status: 'Ready',
        condition: 'Good',
        minStock: 2,
    };
    isCustomCategory.value = false;
    customCategoryInput.value = '';
    isCustomLocation.value = false;
    customLocationInput.value = '';
}
</script>

<template>
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 rounded-xl max-w-lg w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <span class="p-1 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </span>
                    Tambah Barang & Cantumkan Lokasi Rak
                </h3>
                <button @click="close" class="text-slate-400 hover:text-white text-xl">&times;</button>
            </div>

            <form @submit.prevent="submit" class="mt-4 space-y-3.5">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kode SKU / Aset</label>
                        <input
                            v-model="form.code"
                            type="text"
                            placeholder="cth: AST-LIFT-002"
                            required
                            class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-amber-500 font-mono"
                        />
                    </div>

                    <!-- Kategori dengan Fleksibilitas Tambah Kategori Baru -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-300">
                                Kategori <span class="text-rose-400">*</span>
                            </label>
                            <button
                                type="button"
                                @click="toggleCustomCategory"
                                class="text-[10px] text-amber-400 hover:text-amber-300 transition-colors font-medium"
                            >
                                {{ isCustomCategory ? 'Pilih dari List' : '+ Kategori Baru' }}
                            </button>
                        </div>

                        <!-- Mode 1: Dropdown Pilihan Kategori Tersimpan -->
                        <div v-if="!isCustomCategory">
                            <select
                                :value="form.category"
                                @change="onCategorySelect"
                                class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-amber-500"
                                required
                            >
                                <option v-for="cat in allCategories" :key="cat" :value="cat">
                                    {{ cat }}
                                </option>
                                <option value="__NEW_CATEGORY__" class="text-amber-400 font-semibold bg-slate-950">
                                    + Ketik Kategori Kustom Baru...
                                </option>
                            </select>
                        </div>

                        <!-- Mode 2: Input Teks Kategori Kustom Baru -->
                        <div v-else class="space-y-1.5">
                            <div class="relative">
                                <input
                                    v-model="customCategoryInput"
                                    type="text"
                                    placeholder="Ketik kategori baru..."
                                    required
                                    class="w-full bg-slate-950 border border-amber-500/60 rounded-lg pl-3 pr-14 py-2 text-sm text-white focus:border-amber-400 font-medium"
                                />
                                <button
                                    type="button"
                                    @click="isCustomCategory = false; form.category = allCategories[0] || 'Heavy Equipment'"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 px-1.5 py-0.5 rounded text-[10px] bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700"
                                >
                                    Batal
                                </button>
                            </div>

                            <!-- Chips Kategori yang Tersimpan -->
                            <div v-if="allCategories.length > 0" class="flex flex-wrap items-center gap-1">
                                <span class="text-[10px] text-slate-400">Pilihan cepat:</span>
                                <button
                                    v-for="cat in allCategories.slice(0, 4)"
                                    :key="cat"
                                    type="button"
                                    @click="selectCategory(cat)"
                                    class="px-1.5 py-0.5 rounded text-[10px] bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition-colors"
                                >
                                    {{ cat }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Barang / Deskripsi Aset</label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="cth: Forklift Diesel 3 Ton Isuzu Engine"
                        required
                        class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-amber-500"
                    />
                </div>

                <!-- Fleksibel Lokasi Rak (Pilih Master Rak / Dropdown / Manual) -->
                <div class="p-3 bg-slate-950/80 border border-amber-500/30 rounded-lg space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-semibold text-amber-400 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                            Teks Lokasi Rak <span class="text-rose-400">*</span>
                        </label>
                        <button
                            type="button"
                            @click="toggleCustomLocation"
                            class="text-[10px] text-amber-400 hover:text-amber-300 transition-colors font-medium flex items-center gap-1"
                        >
                            <span v-if="isCustomLocation">&larr; Pilih dari Dropdown Rak</span>
                            <span v-else>+ Ketik Manual</span>
                        </button>
                    </div>

                    <!-- Mode 1: Dropdown Pilihan Rak Master yang Sudah Ada -->
                    <div v-if="!isCustomLocation" class="space-y-2">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <div class="sm:col-span-2">
                                <label class="block text-[11px] text-slate-400 mb-1">Pilih Master Rak:</label>
                                <select
                                    :value="selectedRackCode"
                                    @change="onRackSelect"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-white focus:border-amber-500 font-mono"
                                    required
                                >
                                    <option v-for="r in availableRacks" :key="r.code" :value="r.code">
                                        Rak {{ r.code }} ({{ r.zone || 'Zone A' }}) &bull; {{ r.items_count }} item
                                    </option>
                                    <option value="__CUSTOM_LOCATION__" class="text-amber-400 font-semibold bg-slate-950">
                                        + Ketik Manual / Rak Kustom...
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] text-slate-400 mb-1">Tingkat / Slot:</label>
                                <input
                                    v-model="shelfDetail"
                                    type="text"
                                    placeholder="cth: Rak 1"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-white focus:border-amber-500 font-mono"
                                />
                            </div>
                        </div>

                        <!-- Live preview format lokasi -->
                        <div class="flex items-center justify-between text-[11px] bg-slate-900/90 border border-slate-800 rounded px-2.5 py-1 text-slate-300 font-mono">
                            <span class="text-slate-400 text-[10px]">Tersimpan sebagai:</span>
                            <span class="text-amber-300 font-semibold">{{ form.location }}</span>
                        </div>

                        <!-- Chips Pilihan Cepat Rak -->
                        <div v-if="availableRacks.length > 0" class="flex flex-wrap items-center gap-1 pt-1 border-t border-slate-900">
                            <span class="text-[10px] text-slate-400">Pilihan cepat:</span>
                            <button
                                v-for="r in availableRacks.slice(0, 6)"
                                :key="r.code"
                                type="button"
                                @click="selectQuickRack(r.code)"
                                :class="[
                                    'px-1.5 py-0.5 rounded text-[10px] font-mono transition-colors border',
                                    selectedRackCode === r.code
                                        ? 'bg-amber-500/20 text-amber-300 border-amber-500/40 font-bold'
                                        : 'bg-slate-900 hover:bg-slate-800 text-slate-300 border-slate-800'
                                ]"
                            >
                                {{ r.code }}
                            </button>
                        </div>
                    </div>

                    <!-- Mode 2: Input Teks Lokasi Rak Manual Bebas -->
                    <div v-else class="space-y-1.5">
                        <div class="relative">
                            <input
                                v-model="customLocationInput"
                                type="text"
                                placeholder="cth: Zone A (A-01) Rak Tingkat 2"
                                required
                                class="w-full bg-slate-900 border border-amber-500/60 rounded-lg pl-3 pr-14 py-2 text-sm text-white focus:border-amber-400 font-mono"
                            />
                            <button
                                type="button"
                                @click="isCustomLocation = false; updateLocationFromSelect()"
                                class="absolute right-2 top-1/2 -translate-y-1/2 px-1.5 py-0.5 rounded text-[10px] bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700"
                            >
                                Batal
                            </button>
                        </div>
                        <span class="text-[11px] text-slate-400 block">
                            Format bebas. Tercantum di tabel dan dapat diperbarui sewaktu-waktu.
                        </span>
                    </div>
                </div>

                <!-- Bagian Satuan & Stok Bertingkat (Multi-UoM) -->
                <div class="p-3 bg-slate-950/70 border border-slate-800 rounded-lg space-y-3">
                    <div class="grid grid-cols-3 gap-2.5">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">
                                Jumlah Stok Awal
                            </label>
                            <input
                                v-model="form.stock"
                                type="number"
                                min="1"
                                required
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-amber-500 font-mono font-bold"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">
                                Satuan Dasar (Eceran)
                            </label>
                            <input
                                v-model="form.base_unit"
                                type="text"
                                placeholder="cth: Pcs, KG, Liter"
                                required
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-amber-500 font-medium"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Min. Alert</label>
                            <input
                                v-model="form.minStock"
                                type="number"
                                min="1"
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-amber-500 font-mono"
                            />
                        </div>
                    </div>

                    <!-- Checkbox Toggle Kemasan Bertingkat -->
                    <div class="pt-2 border-t border-slate-850">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                v-model="form.has_packaging"
                                class="rounded bg-slate-900 border-slate-700 text-amber-500 focus:ring-amber-500"
                            />
                            <span class="text-xs text-amber-300 font-semibold">
                                Punya Satuan Kemasan Bertingkat? (cth: 1 Box = 20 Pcs, 1 Sak = 25 KG)
                            </span>
                        </label>

                        <!-- Input Detail Kemasan Bertingkat -->
                        <div v-if="form.has_packaging" class="mt-2.5 pt-2 border-t border-slate-900 space-y-2">
                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[11px] text-slate-400 mb-1">
                                        Nama Satuan Kemasan:
                                    </label>
                                    <input
                                        v-model="form.packaging_unit"
                                        type="text"
                                        placeholder="cth: Box, Dus, Sak, Roll"
                                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-white focus:border-amber-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] text-slate-400 mb-1">
                                        Isi per 1 {{ form.packaging_unit || 'Kemasan' }}:
                                    </label>
                                    <div class="relative">
                                        <input
                                            v-model="form.conversion_factor"
                                            type="number"
                                            min="2"
                                            placeholder="cth: 20"
                                            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-white focus:border-amber-500 font-mono font-bold"
                                        />
                                        <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[11px] text-slate-400 pointer-events-none">
                                            {{ form.base_unit || 'Pcs' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Pilihan Kuantitas yang Dimasukkan -->
                            <div class="flex items-center justify-between text-[11px] bg-slate-900 px-2.5 py-1.5 rounded border border-slate-800">
                                <span class="text-slate-400">Angka stok awal adalah:</span>
                                <div class="flex items-center gap-3">
                                    <label class="flex items-center gap-1 cursor-pointer">
                                        <input type="radio" value="packaging" v-model="form.initial_unit" class="text-amber-500 focus:ring-0" />
                                        <span class="text-slate-200 font-semibold">{{ form.packaging_unit || 'Kemasan' }}</span>
                                    </label>
                                    <label class="flex items-center gap-1 cursor-pointer">
                                        <input type="radio" value="base" v-model="form.initial_unit" class="text-amber-500 focus:ring-0" />
                                        <span class="text-slate-200 font-semibold">{{ form.base_unit || 'Eceran' }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Live Calculation Preview -->
                            <div class="text-[11px] font-mono text-amber-300 bg-amber-500/10 border border-amber-500/20 px-2.5 py-1 rounded flex items-center justify-between">
                                <span>Rasio: 1 {{ form.packaging_unit || 'Box' }} = {{ form.conversion_factor || 1 }} {{ form.base_unit || 'Pcs' }}</span>
                                <span>
                                    Total Masuk:
                                    <strong class="text-white">
                                        {{ form.initial_unit === 'packaging' ? ((parseInt(form.stock) || 0) * (parseInt(form.conversion_factor) || 1)) : (form.stock || 0) }} {{ form.base_unit }}
                                    </strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Status Operasional</label>
                        <select
                            v-model="form.status"
                            class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-amber-500"
                        >
                            <option value="Ready">Ready</option>
                            <option value="Low Stock">Low Stock</option>
                            <option value="In Maintenance">In Maintenance</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kondisi Fisik</label>
                        <select
                            v-model="form.condition"
                            class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-amber-500"
                        >
                            <option value="Good">Good (Siap Pakai)</option>
                            <option value="New">New (Baru Datang)</option>
                            <option value="Under Repair">Under Repair</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-2.5">
                    <button
                        type="button"
                        @click="close"
                        class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 text-sm font-semibold hover:bg-slate-700"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-sm font-bold shadow-lg shadow-amber-500/20"
                    >
                        Simpan ke Gudang
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
