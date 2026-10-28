<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

// Modular Warehouse Components (in local ./Components subfolder)
import WarehouseMetrics from './Components/WarehouseMetrics.vue';
import InventoryTable from './Components/InventoryTable.vue';
import StockMovementLog from './Components/StockMovementLog.vue';
import RackCapacityOverview from './Components/RackCapacityOverview.vue';
import AddItemModal from './Components/AddItemModal.vue';
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
const categories = ['All', 'Heavy Equipment', 'Material Handlers', 'Safety Gear', 'Electronics', 'Spare Parts'];
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
        status: item.status,
        condition: item.condition,
        lastUpdated: item.updated_at ? new Date(item.updated_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : 'Hari ini',
    }));
});

// Computed Metrics from DB records
const totalAssetsCount = computed(() => items.value.reduce((acc, curr) => acc + curr.stock, 0));
const totalLowStockCount = computed(() => items.value.filter(i => i.stock <= i.minStock).length);
const totalReadyItems = computed(() => items.value.filter(i => i.status === 'Ready').length);

// Modals State
const isAddModalOpen = ref(false);
const isStockModalOpen = ref(false);
const currentStockActionType = ref('in');
const currentItemForAction = ref(null);

// Dedicated Rack Barcode Scanner & Quick Mutation Modal State
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

// Event Handlers interacting with backend via Inertia router
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
        min_stock: newItemData.minStock,
        status: newItemData.status,
        condition: newItemData.condition,
    }, {
        preserveScroll: true,
    });
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
    <Head title="Warehouse Inventory & Asset Manager" />

    <AuthenticatedLayout>
        <!-- Page Title & Header Bar -->
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white flex items-center gap-2.5">
                        <span class="p-2 bg-amber-500/10 border border-amber-500/30 rounded-lg text-amber-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </span>
                        Manajemen Stok & Aset Gudang
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">
                        Monitoring live database MySQL &bull; Pencatatan rak fisik dan transaksi inbound/outbound.
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <!-- Quick Barcode Scanner & Mutation Modal Button -->
                    <button
                        @click="isScanModalOpen = true; initialScanCode = ''"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 hover:border-cyan-400 font-semibold text-sm transition-all shadow-md active:scale-95"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        Scan Barcode Rak
                    </button>

                    <Link
                        :href="route('barcodes.index')"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 hover:border-amber-500/50 font-semibold text-sm transition-all shadow-md active:scale-95"
                        title="Buka Generator & Master Barcode Rak"
                    >
                        <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Master Barcode
                    </Link>

                    <button
                        @click="isAddModalOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold text-sm transition-all shadow-lg shadow-amber-500/20 active:scale-95"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Barang / Aset
                    </button>
                </div>
            </div>
        </template>

        <!-- 1. Modular KPI Metrics -->
        <WarehouseMetrics
            :items-count="items.length"
            :total-units="totalAssetsCount"
            :ready-items="totalReadyItems"
            :low-stock-count="totalLowStockCount"
            :occupancy-percent="actualOccupancyPercent"
            :empty-racks-count="emptyRacksCount"
        />

        <!-- 2. Modular Inventory Table with Dedicated Lokasi Rak Column & Inline Editing -->
        <InventoryTable
            :items="items"
            :categories="categories"
            @stock-in="handleStockIn"
            @stock-out="handleStockOut"
            @update-location="handleUpdateLocation"
            @scan-rack="handleScanRack"
        />

        <!-- 3. Bottom Row: Modular Movements Log & Rack Zones (From DB) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <StockMovementLog :movements="dbMovements" />
            <RackCapacityOverview :rack-locations="dbRacks" />
        </div>

        <!-- 4. Modular Modals -->
        <AddItemModal
            v-model="isAddModalOpen"
            :rack-locations="dbRacks"
            :available-categories="availableCategories"
            @save="handleSaveNewItem"
        />

        <StockActionModal
            v-model="isStockModalOpen"
            :item="currentItemForAction"
            :action-type="currentStockActionType"
            @confirm="handleConfirmStockAction"
        />

        <!-- 5. Rack Barcode Scan & Instant Stock Mutation Modal (IN / OUT) -->
        <RackScanModal
            v-model="isScanModalOpen"
            :initial-code="initialScanCode"
            :suggested-racks="dbRacks"
        />
    </AuthenticatedLayout>
</template>
