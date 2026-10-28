<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrintStickerModal from '@/Pages/Barcodes/Components/PrintStickerModal.vue';
import StockActionModal from '@/Pages/Dashboard/Components/StockActionModal.vue';
import axios from 'axios';

const props = defineProps({
    rack: {
        type: Object,
        required: true,
    },
    items: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    availableLocations: {
        type: Array,
        default: () => [],
    },
});

// =========================================================================
// 1. Fitur Mengubah Nama & Data Rak
// =========================================================================
const rackForm = ref({
    label: props.rack.label || `Rak ${props.rack.rack_code}`,
    zone: props.rack.zone || 'Zone A',
    description: props.rack.description || '',
    status: props.rack.status || 'ACTIVE',
});

const isEditingRackInfo = ref(false);
const isSavingRack = ref(false);
const rackAlert = ref(null);

async function submitRackUpdate() {
    if (!rackForm.value.label.trim()) {
        rackAlert.value = { type: 'error', message: 'Nama/Label rak tidak boleh kosong!' };
        return;
    }

    isSavingRack.value = true;
    rackAlert.value = null;

    try {
        const response = await axios.put(route('racks.update', props.rack.rack_code), {
            label: rackForm.value.label.trim(),
            zone: rackForm.value.zone.trim(),
            description: rackForm.value.description.trim(),
            status: rackForm.value.status,
        });

        if (response.data && response.data.success) {
            rackAlert.value = { type: 'success', message: response.data.message || 'Nama rak berhasil diperbarui!' };
            isEditingRackInfo.value = false;
            router.reload({ only: ['rack'] });
        } else {
            rackAlert.value = { type: 'error', message: response.data?.message || 'Gagal menyimpan perubahan rak.' };
        }
    } catch (err) {
        rackAlert.value = {
            type: 'error',
            message: err.response?.data?.message || 'Terjadi kesalahan saat menyimpan nama rak.',
        };
    } finally {
        isSavingRack.value = false;
    }
}

function cancelRackEdit() {
    rackForm.value.label = props.rack.label || `Rak ${props.rack.rack_code}`;
    rackForm.value.zone = props.rack.zone || 'Zone A';
    rackForm.value.description = props.rack.description || '';
    rackForm.value.status = props.rack.status || 'ACTIVE';
    isEditingRackInfo.value = false;
    rackAlert.value = null;
}

// =========================================================================
// 2. Barcode Print Sticker Modal
// =========================================================================
const showPrintModal = ref(false);

// =========================================================================
// 3. Filter & Search List Barang
// =========================================================================
const searchQuery = ref('');
const selectedCategoryFilter = ref('ALL');
const selectedStatusFilter = ref('ALL');

const filteredItems = computed(() => {
    return (props.items || []).filter((item) => {
        const matchesSearch =
            !searchQuery.value ||
            item.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            item.code.toLowerCase().includes(searchQuery.value.toLowerCase());

        const matchesCat =
            selectedCategoryFilter.value === 'ALL' || item.category === selectedCategoryFilter.value;

        const matchesStatus =
            selectedStatusFilter.value === 'ALL' || item.status === selectedStatusFilter.value;

        return matchesSearch && matchesCat && matchesStatus;
    });
});

const totalItemsCount = computed(() => props.items?.length || 0);
const totalStockCount = computed(() => props.items?.reduce((acc, i) => acc + (i.stock || 0), 0) || 0);

// =========================================================================
// 4. CRUD Barang: CREATE (Tambah Barang Baru ke Rak)
// =========================================================================
const showAddModal = ref(false);
const isSubmittingAdd = ref(false);
const addError = ref(null);

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

const mergedCategories = computed(() => {
    return Array.from(new Set([...DEFAULT_CATEGORIES, ...(props.categories || [])])).filter(Boolean);
});

const isCustomCategoryAdd = ref(false);
const customCategoryInputAdd = ref('');
const enablePackagingAdd = ref(false);

const newItem = ref({
    code: '',
    name: '',
    category: 'Heavy Equipment',
    stock: 1,
    base_unit: 'Pcs',
    packaging_unit: 'Box',
    conversion_factor: 10,
    initial_unit: 'Pcs',
    min_stock: 2,
    status: 'Ready',
    condition: 'Good',
    shelf_detail: 'Rak 1',
});

function openAddModal() {
    const cleanRack = props.rack.rack_code.replace(/[^a-zA-Z0-9]/g, '');
    const rand = Math.floor(100 + Math.random() * 900);
    newItem.value = {
        code: `SKU-${cleanRack}-${rand}`,
        name: '',
        category: mergedCategories.value[0] || 'Heavy Equipment',
        stock: 1,
        base_unit: 'Pcs',
        packaging_unit: 'Box',
        conversion_factor: 10,
        initial_unit: 'Pcs',
        min_stock: 2,
        status: 'Ready',
        condition: 'Good',
        shelf_detail: 'Rak 1',
    };
    isCustomCategoryAdd.value = false;
    customCategoryInputAdd.value = '';
    enablePackagingAdd.value = false;
    addError.value = null;
    showAddModal.value = true;
}

function autoGenerateSkuAdd() {
    const cleanRack = props.rack.rack_code.replace(/[^a-zA-Z0-9]/g, '');
    const rand = Math.floor(100 + Math.random() * 900);
    newItem.value.code = `SKU-${cleanRack}-${rand}`;
}

async function submitAddItem() {
    let finalCat = newItem.value.category;
    if (isCustomCategoryAdd.value) {
        finalCat = customCategoryInputAdd.value.trim();
        if (!finalCat) {
            addError.value = 'Silakan masukkan nama kategori kustom!';
            return;
        }
    }

    if (!newItem.value.code.trim() || !newItem.value.name.trim()) {
        addError.value = 'Mohon lengkapi kode SKU dan nama barang!';
        return;
    }

    isSubmittingAdd.value = true;
    addError.value = null;

    const fullLocation = `Zone ${props.rack.zone || 'Zone A'} (${props.rack.rack_code}) ${newItem.value.shelf_detail || 'Rak 1'}`;

    const payload = {
        code: newItem.value.code.trim().toUpperCase(),
        name: newItem.value.name.trim(),
        category: finalCat,
        location: fullLocation,
        stock: parseInt(newItem.value.stock) || 0,
        base_unit: newItem.value.base_unit || 'Unit',
        packaging_unit: enablePackagingAdd.value ? (newItem.value.packaging_unit || null) : null,
        conversion_factor: enablePackagingAdd.value ? (parseInt(newItem.value.conversion_factor) || 1) : 1,
        initial_unit: enablePackagingAdd.value ? newItem.value.initial_unit : (newItem.value.base_unit || 'Unit'),
        min_stock: parseInt(newItem.value.min_stock) || 0,
        status: newItem.value.status,
        condition: newItem.value.condition,
    };

    try {
        await axios.post(route('warehouse.items.store'), payload);
        showAddModal.value = false;
        router.reload({ preserveScroll: true });
    } catch (err) {
        addError.value = err.response?.data?.message || 'Gagal menambahkan barang ke rak ini.';
    } finally {
        isSubmittingAdd.value = false;
    }
}

// =========================================================================
// 5. CRUD Barang: UPDATE (Edit Barang Terdaftar)
// =========================================================================
const showEditModal = ref(false);
const editingItem = ref(null);
const isSubmittingEdit = ref(false);
const editError = ref(null);
const enablePackagingEdit = ref(false);
const isCustomCategoryEdit = ref(false);
const customCategoryInputEdit = ref('');

const editForm = ref({
    id: null,
    code: '',
    name: '',
    category: '',
    location: '',
    stock: 0,
    base_unit: 'Pcs',
    packaging_unit: '',
    conversion_factor: 1,
    initial_unit: 'Pcs',
    min_stock: 2,
    status: 'Ready',
    condition: 'Good',
});

function openEditModal(item) {
    editingItem.value = item;
    const hasPkg = !!(item.packaging_unit && item.conversion_factor > 1);
    enablePackagingEdit.value = hasPkg;
    isCustomCategoryEdit.value = false;
    customCategoryInputEdit.value = '';

    editForm.value = {
        id: item.id,
        code: item.code,
        name: item.name,
        category: item.category,
        location: item.location,
        stock: item.stock,
        base_unit: item.base_unit || item.unit || 'Unit',
        packaging_unit: item.packaging_unit || 'Box',
        conversion_factor: item.conversion_factor || 10,
        initial_unit: item.base_unit || item.unit || 'Unit',
        min_stock: item.min_stock ?? 2,
        status: item.status || 'Ready',
        condition: item.condition || 'Good',
    };

    editError.value = null;
    showEditModal.value = true;
}

async function submitEditItem() {
    let finalCat = editForm.value.category;
    if (isCustomCategoryEdit.value) {
        finalCat = customCategoryInputEdit.value.trim();
        if (!finalCat) {
            editError.value = 'Silakan masukkan nama kategori kustom!';
            return;
        }
    }

    if (!editForm.value.name.trim() || !editForm.value.code.trim()) {
        editError.value = 'Kode SKU dan nama barang wajib diisi!';
        return;
    }

    isSubmittingEdit.value = true;
    editError.value = null;

    const payload = {
        code: editForm.value.code.trim().toUpperCase(),
        name: editForm.value.name.trim(),
        category: finalCat,
        location: editForm.value.location.trim(),
        stock: parseInt(editForm.value.stock) || 0,
        base_unit: editForm.value.base_unit || 'Unit',
        packaging_unit: enablePackagingEdit.value ? (editForm.value.packaging_unit || null) : null,
        conversion_factor: enablePackagingEdit.value ? (parseInt(editForm.value.conversion_factor) || 1) : 1,
        initial_unit: editForm.value.base_unit || 'Unit',
        min_stock: parseInt(editForm.value.min_stock) || 0,
        status: editForm.value.status,
        condition: editForm.value.condition,
    };

    try {
        await axios.put(route('warehouse.items.update', editForm.value.id), payload);
        showEditModal.value = false;
        router.reload({ preserveScroll: true });
    } catch (err) {
        editError.value = err.response?.data?.message || 'Gagal memperbarui data barang.';
    } finally {
        isSubmittingEdit.value = false;
    }
}

// =========================================================================
// 6. CRUD Barang: DELETE (Hapus Barang dari Rak)
// =========================================================================
const showDeleteModal = ref(false);
const itemToDelete = ref(null);
const isDeleting = ref(false);

function openDeleteModal(item) {
    itemToDelete.value = item;
    showDeleteModal.value = true;
}

async function confirmDeleteItem() {
    if (!itemToDelete.value) return;

    isDeleting.value = true;
    try {
        await axios.delete(route('warehouse.items.destroy', itemToDelete.value.id));
        showDeleteModal.value = false;
        itemToDelete.value = null;
        router.reload({ preserveScroll: true });
    } catch (err) {
        alert(err.response?.data?.message || 'Gagal menghapus barang.');
    } finally {
        isDeleting.value = false;
    }
}

// =========================================================================
// 7. Quick Stock Mutation Modal (+ Restock / - Dispatch)
// =========================================================================
const showStockModal = ref(false);
const stockModalItem = ref(null);
const stockActionType = ref('in');

function openStockModal(item, type) {
    stockModalItem.value = item;
    stockActionType.value = type;
    showStockModal.value = true;
}

async function handleStockConfirm(payload) {
    try {
        await axios.post(route('warehouse.items.mutate', payload.item.id), {
            type: payload.type,
            amount: payload.amount,
            input_unit: payload.input_unit,
            notes: payload.notes || (payload.type === 'in' ? `Inbound ke Rak ${props.rack.rack_code}` : `Outbound dari Rak ${props.rack.rack_code}`),
        });
        showStockModal.value = false;
        router.reload({ preserveScroll: true });
    } catch (err) {
        alert(err.response?.data?.message || 'Gagal memproses mutasi stok.');
    }
}

// Helper badge styles
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

function getConditionBadge(condition) {
    switch (condition) {
        case 'Good':
            return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30';
        case 'Fair':
            return 'bg-amber-500/10 text-amber-400 border-amber-500/30';
        case 'Damaged':
            return 'bg-rose-500/10 text-rose-400 border-rose-500/30';
        default:
            return 'bg-slate-800 text-slate-400 border-slate-700';
    }
}

function getStatusBadge(status) {
    switch (status) {
        case 'Ready':
            return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30';
        case 'Low Stock':
            return 'bg-rose-500/10 text-rose-400 border-rose-500/30';
        case 'In Maintenance':
            return 'bg-amber-500/10 text-amber-400 border-amber-500/30';
        default:
            return 'bg-slate-800 text-slate-300 border-slate-700';
    }
}
</script>

<template>
    <Head :title="`Kelola Rak ${rack.rack_code} - ${rack.label}`" />

    <AuthenticatedLayout>
        <div class="py-6 sm:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- 1. Breadcrumbs & Top Navigation -->
            <div class="flex flex-wrap items-center justify-between gap-3 pb-2 border-b border-slate-800/80">
                <div class="flex items-center gap-2 text-xs text-slate-400 font-medium">
                    <Link :href="route('dashboard')" class="hover:text-amber-400 transition-colors flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </Link>
                    <span>/</span>
                    <Link :href="route('barcodes.index')" class="hover:text-amber-400 transition-colors">
                        Master Barcode Rak
                    </Link>
                    <span>/</span>
                    <span class="text-amber-400 font-mono font-bold">Rak {{ rack.rack_code }}</span>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="showPrintModal = true"
                        class="px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold flex items-center gap-1.5 transition-all shadow-sm"
                    >
                        <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Cetak Stiker Barcode
                    </button>
                    <Link
                        :href="route('dashboard')"
                        class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-white border border-slate-800 text-xs font-semibold flex items-center gap-1 transition-all"
                    >
                        &larr; Kembali
                    </Link>
                </div>
            </div>

            <!-- Feedback Alert Banner -->
            <div
                v-if="rackAlert"
                class="p-4 rounded-xl border flex items-center justify-between transition-all"
                :class="rackAlert.type === 'success' ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-rose-500/10 border-rose-500/30 text-rose-300'"
            >
                <div class="flex items-center gap-2.5 text-sm">
                    <span v-if="rackAlert.type === 'success'" class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span v-else class="h-2 w-2 rounded-full bg-rose-400 animate-pulse"></span>
                    <span>{{ rackAlert.message }}</span>
                </div>
                <button @click="rackAlert = null" class="text-slate-400 hover:text-white text-xs">&times;</button>
            </div>

            <!-- 2. Header Card: Nama Rak & Fitur Ubah Nama Rak -->
            <div class="bg-slate-950/80 border border-slate-800 rounded-2xl p-6 shadow-xl relative overflow-hidden backdrop-blur-md">
                <div class="absolute -top-16 -right-16 w-52 h-52 bg-amber-500/5 rounded-full blur-3xl pointer-events-none"></div>

                <!-- View Mode Header -->
                <div v-if="!isEditingRackInfo" class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="px-3 py-1 rounded-md bg-amber-500/10 text-amber-400 border border-amber-500/30 font-mono text-sm font-extrabold tracking-wider">
                                LOKASI RAK: {{ rack.rack_code }}
                            </span>
                            <span class="px-2.5 py-1 rounded-md bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 font-mono text-xs font-semibold">
                                {{ rack.barcode_code }}
                            </span>
                            <span
                                class="px-2.5 py-1 rounded-md text-xs font-bold border"
                                :class="rack.status === 'ACTIVE' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'bg-slate-800 text-slate-400 border-slate-700'"
                            >
                                {{ rack.status }}
                            </span>
                            <span class="px-2 py-0.5 rounded bg-slate-900 border border-slate-800 text-slate-400 text-xs font-mono">
                                {{ rack.zone }}
                            </span>
                        </div>

                        <!-- Nama Rak Utama -->
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                            <span>{{ rack.label }}</span>
                        </h1>

                        <p class="text-sm text-slate-400 max-w-2xl leading-relaxed">
                            {{ rack.description || 'Tidak ada catatan atau deskripsi spesifik untuk lokasi rak ini.' }}
                        </p>
                    </div>

                    <!-- Action Buttons: Ubah Nama Rak -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <button
                            type="button"
                            @click="isEditingRackInfo = true"
                            class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-amber-500/20 active:scale-95 transition-all"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Ubah Nama & Info Rak
                        </button>

                        <button
                            type="button"
                            @click="openAddModal"
                            class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/20 active:scale-95 transition-all"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            + Tambah Barang ke Rak Ini
                        </button>
                    </div>
                </div>

                <!-- Edit Mode Form: Mengubah Nama Rak -->
                <form v-else @submit.prevent="submitRackUpdate" class="space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="p-1 bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </span>
                            <h3 class="text-sm font-bold text-white">Edit Nama & Informasi Rak {{ rack.rack_code }}</h3>
                        </div>
                        <span class="text-xs text-slate-500 font-mono">Kode Rak: {{ rack.rack_code }} (Permanen)</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Nama / Label Rak Input -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Nama / Label Rak <span class="text-rose-400">*</span>
                            </label>
                            <input
                                v-model="rackForm.label"
                                type="text"
                                placeholder="cth: Rak Pallet Utama Heavy Equipment"
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3.5 py-2 text-sm text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                                required
                            />
                            <p class="text-[11px] text-slate-500 mt-1">Nama ini akan tercetak pada stiker barcode dan muncul di pencarian sistem.</p>
                        </div>

                        <!-- Zona Gudang Input -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Zona Gudang
                            </label>
                            <input
                                v-model="rackForm.zone"
                                type="text"
                                placeholder="cth: Zone A"
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3.5 py-2 text-sm text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 font-mono"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Deskripsi / Catatan Rak -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Deskripsi / Catatan Lokasi Rak
                            </label>
                            <input
                                v-model="rackForm.description"
                                type="text"
                                placeholder="cth: Lantai 1 dekat pintu timur, khusus aset berat"
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3.5 py-2 text-sm text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                            />
                        </div>

                        <!-- Status Rak -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Status Rak
                            </label>
                            <select
                                v-model="rackForm.status"
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3.5 py-2 text-sm text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                            >
                                <option value="ACTIVE">ACTIVE (Tersedia)</option>
                                <option value="INACTIVE">INACTIVE (Nonaktif / Pemeliharaan)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Submit & Cancel buttons -->
                    <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-800">
                        <button
                            type="button"
                            @click="cancelRackEdit"
                            class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-all"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="isSavingRack"
                            class="px-5 py-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-md shadow-amber-500/20 disabled:opacity-50 transition-all flex items-center gap-1.5"
                        >
                            <svg v-if="isSavingRack" class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>{{ isSavingRack ? 'Menyimpan...' : 'Simpan Nama Rak' }}</span>
                        </button>
                    </div>
                </form>

                <!-- Mini Stats Bar inside header -->
                <div class="mt-5 pt-4 border-t border-slate-800/80 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-2.5 rounded-lg bg-slate-900/60 border border-slate-800/60">
                        <span class="text-slate-400 block text-[11px]">Total Item di Rak</span>
                        <span class="font-bold font-mono text-white text-base">{{ totalItemsCount }} SKU</span>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-900/60 border border-slate-800/60">
                        <span class="text-slate-400 block text-[11px]">Total Fisik Tersimpan</span>
                        <span class="font-bold font-mono text-emerald-400 text-base">{{ totalStockCount }} Unit</span>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-900/60 border border-slate-800/60">
                        <span class="text-slate-400 block text-[11px]">Total Dipindai</span>
                        <span class="font-bold font-mono text-cyan-400 text-base">{{ rack.scan_count }} Kali</span>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-900/60 border border-slate-800/60">
                        <span class="text-slate-400 block text-[11px]">Didaftarkan Oleh</span>
                        <span class="font-medium text-slate-300 text-xs truncate block">{{ rack.created_by || 'Petugas Terminal' }}</span>
                    </div>
                </div>
            </div>

            <!-- 3. Section CRUD Barang di Rak Ini ("Hanya CRUD Doang") -->
            <div class="bg-slate-950/80 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                <!-- Section Header & Controls -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <span class="p-1.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-lg">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-base font-bold text-white">Daftar Barang & Aset di Rak Ini</h2>
                            <p class="text-xs text-slate-400">Kelola inventaris fisik pada rak {{ rack.rack_code }} secara langsung (CRUD).</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="openAddModal"
                        class="px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition-all shadow-md shadow-emerald-600/20 active:scale-95"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Barang Baru
                    </button>
                </div>

                <!-- Filter & Search Toolbar -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari nama barang atau kode SKU di rak ini..."
                            class="w-full bg-slate-900 border border-slate-700 rounded-lg pl-9 pr-3 py-2 text-xs text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                        />
                        <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <div class="flex items-center gap-2">
                        <select
                            v-model="selectedCategoryFilter"
                            class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-300 focus:border-amber-500"
                        >
                            <option value="ALL">Semua Kategori</option>
                            <option v-for="cat in mergedCategories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>

                        <select
                            v-model="selectedStatusFilter"
                            class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-300 focus:border-amber-500"
                        >
                            <option value="ALL">Semua Status</option>
                            <option value="Ready">Ready</option>
                            <option value="Low Stock">Low Stock</option>
                            <option value="In Maintenance">In Maintenance</option>
                        </select>
                    </div>
                </div>

                <!-- Table of Items (READ) -->
                <div class="overflow-x-auto rounded-xl border border-slate-800">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-900/90 text-slate-400 font-semibold border-b border-slate-800">
                            <tr>
                                <th class="py-3 px-4">SKU / Kode</th>
                                <th class="py-3 px-4">Nama Barang</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4 text-right">Stok Aktual</th>
                                <th class="py-3 px-4">Kondisi</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-center">Aksi CRUD</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80">
                            <tr
                                v-for="item in filteredItems"
                                :key="item.id"
                                class="hover:bg-slate-900/50 transition-colors group"
                            >
                                <!-- SKU -->
                                <td class="py-3 px-4 font-mono font-bold text-amber-400 whitespace-nowrap">
                                    {{ item.code }}
                                </td>

                                <!-- Nama Barang -->
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-100 text-sm">{{ item.name }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono mt-0.5 truncate max-w-xs">
                                        {{ item.location }}
                                    </div>
                                </td>

                                <!-- Kategori -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium border" :class="getCategoryBadge(item.category)">
                                        {{ item.category }}
                                    </span>
                                </td>

                                <!-- Stok Aktual (Dual UoM) -->
                                <td class="py-3 px-4 text-right whitespace-nowrap font-mono">
                                    <div class="text-sm font-extrabold text-emerald-400">
                                        {{ item.formatted_stock }}
                                    </div>
                                    <div v-if="item.packaging_unit && item.conversion_factor > 1" class="text-[10px] text-slate-500">
                                        ({{ item.stock }} {{ item.base_unit }})
                                    </div>
                                </td>

                                <!-- Kondisi -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium border" :class="getConditionBadge(item.condition)">
                                        {{ item.condition }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border" :class="getStatusBadge(item.status)">
                                        {{ item.status }}
                                    </span>
                                </td>

                                <!-- Aksi CRUD: Mutasi, Edit, Hapus -->
                                <td class="py-3 px-4 whitespace-nowrap text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <!-- Quick Mutasi IN -->
                                        <button
                                            type="button"
                                            @click="openStockModal(item, 'in')"
                                            title="Tambah Stok (Inbound)"
                                            class="p-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 transition-all"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </button>

                                        <!-- Quick Mutasi OUT -->
                                        <button
                                            type="button"
                                            @click="openStockModal(item, 'out')"
                                            title="Keluarkan Stok (Outbound)"
                                            class="p-1.5 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 transition-all"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                            </svg>
                                        </button>

                                        <!-- Edit Barang (UPDATE) -->
                                        <button
                                            type="button"
                                            @click="openEditModal(item)"
                                            title="Edit Data Barang"
                                            class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold flex items-center gap-1 transition-all"
                                        >
                                            <svg class="w-3.5 h-3.5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                            Edit
                                        </button>

                                        <!-- Hapus Barang (DELETE) -->
                                        <button
                                            type="button"
                                            @click="openDeleteModal(item)"
                                            title="Hapus Barang"
                                            class="p-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 transition-all"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty State -->
                            <tr v-if="filteredItems.length === 0">
                                <td colspan="7" class="py-12 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="w-8 h-8 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                        <span class="text-sm font-semibold">Belum ada barang di rak ini</span>
                                        <p class="text-xs text-slate-600 max-w-sm">
                                            Klik tombol "+ Tambah Barang ke Rak Ini" di atas untuk mendaftarkan barang baru langsung ke rak {{ rack.rack_code }}.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- MODAL 1: Tambah Barang Baru ke Rak (CREATE)                        -->
        <!-- ================================================================= -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="p-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </span>
                        <h3 class="text-base font-bold text-white">Tambah Barang ke Rak {{ rack.rack_code }}</h3>
                    </div>
                    <button @click="showAddModal = false" class="text-slate-400 hover:text-white">&times;</button>
                </div>

                <div v-if="addError" class="p-3 rounded-lg bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs">
                    {{ addError }}
                </div>

                <form @submit.prevent="submitAddItem" class="space-y-4 text-xs">
                    <!-- SKU & Generator -->
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Kode SKU <span class="text-rose-400">*</span></label>
                        <div class="flex gap-2">
                            <input
                                v-model="newItem.code"
                                type="text"
                                placeholder="cth: SKU-A01-102"
                                class="flex-1 bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white font-mono uppercase focus:border-amber-500"
                                required
                            />
                            <button
                                type="button"
                                @click="autoGenerateSkuAdd"
                                class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg font-mono text-[11px]"
                            >
                                Auto SKU
                            </button>
                        </div>
                    </div>

                    <!-- Nama Barang -->
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nama Barang <span class="text-rose-400">*</span></label>
                        <input
                            v-model="newItem.name"
                            type="text"
                            placeholder="cth: Safety Helmet Pro Yellow"
                            class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:border-amber-500"
                            required
                        />
                    </div>

                    <!-- Kategori -->
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1 flex items-center justify-between">
                            <span>Kategori</span>
                            <button
                                type="button"
                                @click="isCustomCategoryAdd = !isCustomCategoryAdd"
                                class="text-[10px] text-amber-400 hover:underline"
                            >
                                {{ isCustomCategoryAdd ? 'Pilih Kategori Yang Ada' : '+ Kategori Baru' }}
                            </button>
                        </label>
                        <input
                            v-if="isCustomCategoryAdd"
                            v-model="customCategoryInputAdd"
                            type="text"
                            placeholder="Ketik kategori baru..."
                            class="w-full bg-slate-950 border border-amber-500/50 rounded-lg px-3 py-2 text-white focus:border-amber-500"
                        />
                        <select
                            v-else
                            v-model="newItem.category"
                            class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:border-amber-500"
                        >
                            <option v-for="cat in mergedCategories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>

                    <!-- Multi-UoM (Satuan Bertingkat) Toggle -->
                    <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-200">Konfigurasi Satuan (Multi-UoM)</span>
                            <label class="inline-flex items-center cursor-pointer gap-2">
                                <input type="checkbox" v-model="enablePackagingAdd" class="sr-only peer" />
                                <div class="w-8 h-4 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-amber-500"></div>
                                <span class="text-[11px] text-slate-400">Gunakan Kemasan (Box/Dus)</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-slate-400 text-[11px] mb-1">Satuan Dasar (Terkecil)</label>
                                <input
                                    v-model="newItem.base_unit"
                                    type="text"
                                    placeholder="cth: Pcs, Unit, Kg"
                                    class="w-full bg-slate-900 border border-slate-700 rounded px-2.5 py-1.5 text-white"
                                />
                            </div>
                            <div v-if="enablePackagingAdd">
                                <label class="block text-slate-400 text-[11px] mb-1">Satuan Kemasan Besar</label>
                                <input
                                    v-model="newItem.packaging_unit"
                                    type="text"
                                    placeholder="cth: Box, Dus, Karton"
                                    class="w-full bg-slate-900 border border-slate-700 rounded px-2.5 py-1.5 text-white"
                                />
                            </div>
                        </div>

                        <div v-if="enablePackagingAdd" class="grid grid-cols-2 gap-3 pt-1 border-t border-slate-900">
                            <div>
                                <label class="block text-slate-400 text-[11px] mb-1">Isi per Kemasan</label>
                                <input
                                    v-model.number="newItem.conversion_factor"
                                    type="number"
                                    min="2"
                                    placeholder="cth: 10"
                                    class="w-full bg-slate-900 border border-slate-700 rounded px-2.5 py-1.5 text-white font-mono"
                                />
                            </div>
                            <div>
                                <label class="block text-slate-400 text-[11px] mb-1">Input Stok Awal Dalam</label>
                                <select
                                    v-model="newItem.initial_unit"
                                    class="w-full bg-slate-900 border border-slate-700 rounded px-2.5 py-1.5 text-white"
                                >
                                    <option :value="newItem.packaging_unit">{{ newItem.packaging_unit || 'Kemasan' }}</option>
                                    <option :value="newItem.base_unit">{{ newItem.base_unit || 'Satuan Dasar' }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Stok & Minimum Stock -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">
                                Jumlah Stok Awal ({{ enablePackagingAdd ? newItem.initial_unit : newItem.base_unit }})
                            </label>
                            <input
                                v-model.number="newItem.stock"
                                type="number"
                                min="0"
                                class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white font-mono font-bold"
                                required
                            />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Batas Minimum Stok</label>
                            <input
                                v-model.number="newItem.min_stock"
                                type="number"
                                min="0"
                                class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white font-mono"
                            />
                        </div>
                    </div>

                    <!-- Kondisi & Status -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Kondisi Fisik</label>
                            <select v-model="newItem.condition" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white">
                                <option value="Good">Good (Bagus / Baru)</option>
                                <option value="Fair">Fair (Bekas / Normal)</option>
                                <option value="Damaged">Damaged (Rusak / Afkir)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Status Ketersediaan</label>
                            <select v-model="newItem.status" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white">
                                <option value="Ready">Ready</option>
                                <option value="Low Stock">Low Stock</option>
                                <option value="In Maintenance">In Maintenance</option>
                            </select>
                        </div>
                    </div>

                    <!-- Shelf Level Detail -->
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Detail Ambalan / Baris Rak</label>
                        <input
                            v-model="newItem.shelf_detail"
                            type="text"
                            placeholder="cth: Rak 1, Tingkat 2, Slot B"
                            class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white"
                        />
                    </div>

                    <!-- Modal Action Buttons -->
                    <div class="pt-4 flex items-center justify-end gap-2.5 border-t border-slate-800">
                        <button
                            type="button"
                            @click="showAddModal = false"
                            class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 font-semibold hover:bg-slate-700"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="isSubmittingAdd"
                            class="px-5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold shadow-lg shadow-emerald-600/20 disabled:opacity-50 flex items-center gap-1.5"
                        >
                            <svg v-if="isSubmittingAdd" class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>{{ isSubmittingAdd ? 'Menyimpan...' : 'Simpan Barang ke Rak' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- MODAL 2: Edit Barang Terdaftar (UPDATE)                           -->
        <!-- ================================================================= -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="p-1 bg-blue-500/10 text-blue-400 border border-blue-500/20 rounded">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </span>
                        <h3 class="text-base font-bold text-white">Edit Data Barang ({{ editForm.code }})</h3>
                    </div>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-white">&times;</button>
                </div>

                <div v-if="editError" class="p-3 rounded-lg bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs">
                    {{ editError }}
                </div>

                <form @submit.prevent="submitEditItem" class="space-y-4 text-xs">
                    <!-- SKU & Name -->
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Kode SKU <span class="text-rose-400">*</span></label>
                            <input
                                v-model="editForm.code"
                                type="text"
                                class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white font-mono uppercase focus:border-amber-500"
                                required
                            />
                        </div>
                        <div class="col-span-2">
                            <label class="block font-semibold text-slate-300 mb-1">Nama Barang <span class="text-rose-400">*</span></label>
                            <input
                                v-model="editForm.name"
                                type="text"
                                class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:border-amber-500"
                                required
                            />
                        </div>
                    </div>

                    <!-- Kategori -->
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1 flex items-center justify-between">
                            <span>Kategori</span>
                            <button
                                type="button"
                                @click="isCustomCategoryEdit = !isCustomCategoryEdit"
                                class="text-[10px] text-amber-400 hover:underline"
                            >
                                {{ isCustomCategoryEdit ? 'Pilih Kategori Yang Ada' : '+ Kategori Baru' }}
                            </button>
                        </label>
                        <input
                            v-if="isCustomCategoryEdit"
                            v-model="customCategoryInputEdit"
                            type="text"
                            placeholder="Ketik kategori baru..."
                            class="w-full bg-slate-950 border border-amber-500/50 rounded-lg px-3 py-2 text-white focus:border-amber-500"
                        />
                        <select
                            v-else
                            v-model="editForm.category"
                            class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:border-amber-500"
                        >
                            <option v-for="cat in mergedCategories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>

                    <!-- Lokasi Rak Penyimpanan -->
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Lokasi Rak & Ambalan</label>
                        <input
                            v-model="editForm.location"
                            type="text"
                            placeholder="cth: Zone A (A-01) Rak 1"
                            class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white font-mono focus:border-amber-500"
                            required
                        />
                    </div>

                    <!-- Multi-UoM Konfigurasi -->
                    <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-200">Satuan & Konversi Bertingkat</span>
                            <label class="inline-flex items-center cursor-pointer gap-2">
                                <input type="checkbox" v-model="enablePackagingEdit" class="sr-only peer" />
                                <div class="w-8 h-4 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-amber-500"></div>
                                <span class="text-[11px] text-slate-400">Gunakan Kemasan</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-slate-400 text-[11px] mb-1">Satuan Dasar</label>
                                <input
                                    v-model="editForm.base_unit"
                                    type="text"
                                    class="w-full bg-slate-900 border border-slate-700 rounded px-2.5 py-1.5 text-white"
                                />
                            </div>
                            <div v-if="enablePackagingEdit">
                                <label class="block text-slate-400 text-[11px] mb-1">Satuan Kemasan</label>
                                <input
                                    v-model="editForm.packaging_unit"
                                    type="text"
                                    class="w-full bg-slate-900 border border-slate-700 rounded px-2.5 py-1.5 text-white"
                                />
                            </div>
                        </div>

                        <div v-if="enablePackagingEdit">
                            <label class="block text-slate-400 text-[11px] mb-1">Faktor Konversi (1 {{ editForm.packaging_unit }} = X {{ editForm.base_unit }})</label>
                            <input
                                v-model.number="editForm.conversion_factor"
                                type="number"
                                min="2"
                                class="w-full bg-slate-900 border border-slate-700 rounded px-2.5 py-1.5 text-white font-mono"
                            />
                        </div>
                    </div>

                    <!-- Stok & Batas Minimum -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Stok Aktual (Dalam {{ editForm.base_unit }})</label>
                            <input
                                v-model.number="editForm.stock"
                                type="number"
                                min="0"
                                class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white font-mono font-bold"
                                required
                            />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Batas Minimum Stok</label>
                            <input
                                v-model.number="editForm.min_stock"
                                type="number"
                                min="0"
                                class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white font-mono"
                            />
                        </div>
                    </div>

                    <!-- Kondisi & Status -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Kondisi</label>
                            <select v-model="editForm.condition" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white">
                                <option value="Good">Good</option>
                                <option value="Fair">Fair</option>
                                <option value="Damaged">Damaged</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Status</label>
                            <select v-model="editForm.status" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white">
                                <option value="Ready">Ready</option>
                                <option value="Low Stock">Low Stock</option>
                                <option value="In Maintenance">In Maintenance</option>
                            </select>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="pt-4 flex items-center justify-end gap-2.5 border-t border-slate-800">
                        <button
                            type="button"
                            @click="showEditModal = false"
                            class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 font-semibold hover:bg-slate-700"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="isSubmittingEdit"
                            class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold shadow-lg shadow-blue-600/20 disabled:opacity-50 flex items-center gap-1.5"
                        >
                            <svg v-if="isSubmittingEdit" class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>{{ isSubmittingEdit ? 'Menyimpan...' : 'Perbarui Barang' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- MODAL 3: Konfirmasi Hapus Barang (DELETE)                         -->
        <!-- ================================================================= -->
        <div v-if="showDeleteModal && itemToDelete" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center gap-3 text-rose-400">
                    <span class="p-2 bg-rose-500/10 border border-rose-500/20 rounded-xl">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-white">Konfirmasi Hapus Barang</h3>
                        <p class="text-xs text-slate-400">Tindakan ini permanen dan tidak dapat dibatalkan.</p>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 text-xs space-y-1.5">
                    <div class="font-bold text-white text-sm">{{ itemToDelete.name }}</div>
                    <div class="text-slate-400">SKU: <span class="font-mono text-amber-400">{{ itemToDelete.code }}</span></div>
                    <div class="text-slate-400">Stok Saat Ini: <span class="font-mono text-emerald-400 font-bold">{{ itemToDelete.formatted_stock }}</span></div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button
                        type="button"
                        @click="showDeleteModal = false"
                        class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 font-semibold text-xs hover:bg-slate-700"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="confirmDeleteItem"
                        :disabled="isDeleting"
                        class="px-5 py-2 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-lg shadow-rose-600/20 disabled:opacity-50 flex items-center gap-1.5"
                    >
                        <svg v-if="isDeleting" class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>{{ isDeleting ? 'Menghapus...' : 'Ya, Hapus Barang' }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- MODAL 4: Quick Stock Action Modal (IN / OUT)                      -->
        <!-- ================================================================= -->
        <StockActionModal
            v-model="showStockModal"
            :item="stockModalItem"
            :action-type="stockActionType"
            @confirm="handleStockConfirm"
        />

        <!-- ================================================================= -->
        <!-- MODAL 5: Print Barcode Sticker Modal                             -->
        <!-- ================================================================= -->
        <PrintStickerModal
            v-model="showPrintModal"
            :barcode="rack"
        />
    </AuthenticatedLayout>
</template>
