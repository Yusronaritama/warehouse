<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

// Modular Warehouse Components
import WarehouseMetrics from './Components/WarehouseMetrics.vue';
import InventoryTable from './Components/InventoryTable.vue';
import StockMovementLog from './Components/StockMovementLog.vue';
import RackCapacityOverview from './Components/RackCapacityOverview.vue';
import ItemSlideOverDrawer from './Components/ItemSlideOverDrawer.vue';
import StockActionModal from './Components/StockActionModal.vue';
import RackScanModal from '@/Components/Warehouse/RackScanModal.vue';

// Props passed from WarehouseController via Inertia
const props = defineProps({
    dbItems: { type: Array, default: () => [] },
    dbMovements: { type: Array, default: () => [] },
    dbRacks: { type: Array, default: () => [] },
    actualOccupancyPercent: { type: Number, default: 0 },
    emptyRacksCount: { type: Number, default: 0 },
});

// Categories
const categories = ['All', 'Bahan Baku', 'Painting', 'Masking', 'Buffing', 'Packaging'];
const availableCategories = computed(() => Array.from(new Set(props.dbItems.map((i) => i.category).filter(Boolean))));

// Format Items from DB
const items = computed(() => {
    return props.dbItems.map(item => ({
        id: item.id,
        code: item.code,
        name: item.name,
        category: item.category,
        location: item.location,
        stock: item.stock,
        unit: item.unit,
        base_unit: item.base_unit || item.unit || 'Unit',
        packaging_unit: item.packaging_unit || null,
        conversion_factor: item.conversion_factor || 1,
        formatted_stock: item.formatted_stock || `${item.stock} ${item.unit || 'Unit'}`,
        minStock: item.min_stock,
        min_stock: item.min_stock,
        status: item.status,
        condition: item.condition,
        lastUpdated: item.updated_at ? new Date(item.updated_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : 'Hari ini',
    }));
});

// Computed Metrics from DB records
const totalAssetsCount = computed(() => items.value.reduce((acc, curr) => acc + curr.stock, 0));
const totalLowStockCount = computed(() => items.value.filter(i => i.stock <= i.minStock).length);
const totalReadyItems = computed(() => items.value.filter(i => i.status === 'Ready').length);

// Slide-Over Drawer State (Create & Edit)
const isDrawerOpen = ref(false);
const itemToEdit = ref(null);

function handleOpenCreateDrawer() {
    itemToEdit.value = null;
    isDrawerOpen.value = true;
}

function handleEditItem(item) {
    itemToEdit.value = item;
    isDrawerOpen.value = true;
}

// Stock Action Modal (In/Out)
const isStockModalOpen = ref(false);
const currentStockActionType = ref('in');
const currentItemForAction = ref(null);

// Dedicated Rack Barcode Scanner Modal State
const isScanModalOpen = ref(false);
const initialScanCode = ref('');

function handleScanRack(rackLocation) {
    initialScanCode.value = rackLocation;
    isScanModalOpen.value = true;
}

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const scanParam = urlParams.get('scan');
    if (scanParam) {
        router.visit(route('racks.show', scanParam));
    }
});

// Stock In / Out Handlers
function handleStockIn(item) {
    currentItemForAction.value = item;
    currentStockActionType.value = 'in';
    isStockModalOpen.value = true;
}

function handleStockOut(item) {
    currentItemForAction.value = item;
    currentStockActionType.value = 'out';
    isStockModalOpen.value = true;
}

// CRUD Operations via Inertia
function handleUpdateLocation({ item, newLocation }) {
    router.patch(route('warehouse.items.location', item.id), {
        location: newLocation,
    }, {
        preserveScroll: true,
    });
}

function handleSaveNewItem(newItemData) {
    router.post(route('warehouse.items.store'), {
        code: newItemData.code,
        name: newItemData.name,
        category: newItemData.category,
        location: newItemData.location,
        stock: newItemData.stock,
        unit: newItemData.base_unit || newItemData.unit || 'Unit',
        base_unit: newItemData.base_unit || newItemData.unit || 'Unit',
        packaging_unit: newItemData.packaging_unit || null,
        conversion_factor: newItemData.conversion_factor || 1,
        initial_unit: newItemData.initial_unit || null,
        min_stock: newItemData.min_stock ?? newItemData.minStock ?? 5,
        status: newItemData.status,
        condition: newItemData.condition,
    }, {
        preserveScroll: true,
    });
}

function handleUpdateItem(updatedItemData) {
    router.put(route('warehouse.items.update', updatedItemData.id), {
        code: updatedItemData.code,
        name: updatedItemData.name,
        category: updatedItemData.category,
        location: updatedItemData.location,
        stock: updatedItemData.stock,
        unit: updatedItemData.base_unit || updatedItemData.unit || 'Unit',
        base_unit: updatedItemData.base_unit || updatedItemData.unit || 'Unit',
        packaging_unit: updatedItemData.packaging_unit || null,
        conversion_factor: updatedItemData.conversion_factor || 1,
        initial_unit: updatedItemData.initial_unit || null,
        min_stock: updatedItemData.min_stock ?? updatedItemData.minStock ?? 5,
        status: updatedItemData.status,
        condition: updatedItemData.condition,
    }, {
        preserveScroll: true,
    });
}

function handleDeleteItem(item) {
    if (confirm(`Apakah Anda yakin ingin menghapus barang "${item.name}" (${item.code})? Seluruh riwayat log mutasi sebelumnya tetap tersimpan.`)) {
        router.delete(route('warehouse.items.destroy', item.id), {
            preserveScroll: true,
        });
    }
}

function handleConfirmStockAction({ item, type, amount, input_unit, notes }) {
    router.post(route('warehouse.items.mutate', item.id), {
        type: type,
        amount: amount,
        input_unit: input_unit || null,
        notes: notes,
    }, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Warehouse Inventory & Asset Manager (Nordic Dim)" />

    <AuthenticatedLayout>
        <!-- Page Title & Header Bar (Nordic Dim: bg-[#2E3440]/95 border-b border-[#434C5E]) -->
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="p-1.5 bg-[#434C5E] border border-[#4C566A] rounded-lg text-[#88C0D0]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </span>
                    <div>
                        <h1 class="text-lg sm:text-xl font-bold tracking-tight text-[#ECEFF4] flex items-center gap-2">
                            <span>Manajemen Stok & Aset Gudang</span>
                            <span class="hidden sm:inline-flex text-[11px] font-mono font-medium px-2 py-0.5 rounded-full bg-[#A3BE8C]/15 text-[#A3BE8C] border border-[#A3BE8C]/30">
                                Live Database
                            </span>
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Tombol Sekunder: Scan Barcode Rak (bg-[#434C5E] hover:bg-[#4C566A] text-[#ECEFF4] border border-[#4C566A]) -->
                    <button
                        @click="isScanModalOpen = true; initialScanCode = ''"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#434C5E] hover:bg-[#4C566A] text-[#ECEFF4] border border-[#4C566A] font-semibold text-xs transition-all shadow-xs active:scale-95"
                    >
                        <svg class="w-4 h-4 text-[#88C0D0]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        <span>Scan Rak</span>
                    </button>

                    <!-- Tombol Sekunder: Master Barcode Link -->
                    <Link
                        :href="route('barcodes.index')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#434C5E] hover:bg-[#4C566A] text-[#ECEFF4] border border-[#4C566A] font-semibold text-xs transition-all shadow-xs active:scale-95"
                        title="Master Barcode Rak"
                    >
                        <svg class="w-4 h-4 text-[#88C0D0]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Master Barcode</span>
                    </Link>

                    <!-- Tombol CTA Utama: Tambah Barang (bg-[#5E81AC] hover:bg-[#81A1C1] text-[#ECEFF4]) -->
                    <button
                        @click="handleOpenCreateDrawer"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-[#5E81AC] hover:bg-[#81A1C1] text-[#ECEFF4] font-semibold text-xs transition-all shadow-xs active:scale-95"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        <span>+ Tambah</span>
                    </button>
                </div>
            </div>
        </template>

        <!-- 1. Header & KPI (WarehouseMetrics.vue): Header Ringkas & 4 Kartu KPI (Mobile grid-cols-2, Desktop lg:grid-cols-4) -->
        <WarehouseMetrics
            :items-count="items.length"
            :total-units="totalAssetsCount"
            :ready-items="totalReadyItems"
            :low-stock-count="totalLowStockCount"
            :occupancy-percent="actualOccupancyPercent"
            :empty-racks-count="emptyRacksCount"
        />

        <!-- 2. Vertical Stack Layout: Tabel Inventaris -> Kapasitas Rak -> Riwayat Mutasi Stok di Paling Bawah -->
        <div class="space-y-6">
            <!-- Action Toolbar + Tabel CRUD 1-Klik (Full Width) -->
            <InventoryTable
                :items="items"
                :categories="categories"
                :rack-locations="dbRacks"
                @stock-in="handleStockIn"
                @stock-out="handleStockOut"
                @update-location="handleUpdateLocation"
                @scan-rack="handleScanRack"
                @edit-item="handleEditItem"
                @delete-item="handleDeleteItem"
                @open-create-drawer="handleOpenCreateDrawer"
            />

            <!-- Pemetaan Rak & Kapasitas Fisik -->
            <RackCapacityOverview :rack-locations="dbRacks" />

            <!-- Panel Riwayat Mutasi Stok di Paling Bawah -->
            <StockMovementLog :movements="dbMovements" />
        </div>

        <!-- 3. Slide-Over Drawer dari Sisi Kanan Layar untuk Tambah & Edit Barang -->
        <ItemSlideOverDrawer
            v-model="isDrawerOpen"
            :item-to-edit="itemToEdit"
            :rack-locations="dbRacks"
            :available-categories="availableCategories"
            @save="handleSaveNewItem"
            @update="handleUpdateItem"
        />

        <!-- 4. Modal Aksi Stok Masuk / Keluar (In/Out) -->
        <StockActionModal
            v-model="isStockModalOpen"
            :item="currentItemForAction"
            :action-type="currentStockActionType"
            @confirm="handleConfirmStockAction"
        />

        <!-- 5. Modal Scan Rak Barcode / QR -->
        <RackScanModal
            v-model="isScanModalOpen"
            :initial-code="initialScanCode"
            :suggested-racks="dbRacks"
        />

        <!-- 6. Floating Action Button (FAB) di Area Bawah Layar (< md) untuk Akses Langsung RackScanModal (bg-[#88C0D0] text-[#2E3440] font-semibold shadow-lg) -->
        <button
            type="button"
            @click="isScanModalOpen = true; initialScanCode = ''"
            class="fixed bottom-20 right-4 z-40 md:hidden w-14 h-14 rounded-full bg-[#88C0D0] hover:bg-[#81A1C1] text-[#2E3440] font-bold shadow-lg shadow-[#88C0D0]/20 flex items-center justify-center active:scale-95 transition-all focus:outline-none focus:ring-2 focus:ring-[#88C0D0]"
            title="Scan Barcode Rak"
            aria-label="Scan Barcode Rak"
        >
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
            </svg>
        </button>
    </AuthenticatedLayout>
</template>
