<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

// Modular components (all inside resources/js/Pages/Barcodes/Components/)
import BarcodeMetrics from './Components/BarcodeMetrics.vue';
import BarcodeGenerator from './Components/BarcodeGenerator.vue';
import BarcodePreviewCard from './Components/BarcodePreviewCard.vue';
import BarcodeScannerBox from './Components/BarcodeScannerBox.vue';
import BarcodeTable from './Components/BarcodeTable.vue';
import BarcodeLogs from './Components/BarcodeLogs.vue';
import PrintStickerModal from './Components/PrintStickerModal.vue';
import RackItemsModal from './Components/RackItemsModal.vue';
import RackScanModal from '@/Components/Warehouse/RackScanModal.vue';

const props = defineProps({
    barcodes: {
        type: Array,
        required: true,
    },
    racks: {
        type: Array,
        required: true,
    },
    logs: {
        type: Array,
        required: true,
    },
    allItems: {
        type: Array,
        default: () => [],
    },
    metrics: {
        type: Object,
        default: () => ({ totalBarcodes: 0, activeCount: 0, inactiveCount: 0, totalScans: 0 }),
    },
    filters: {
        type: Object,
        default: () => ({ status: 'ALL', rack: 'ALL', search: '' }),
    },
});

// ==========================================
// Generator Form State & Actions
// ==========================================
const form = useForm({
    rack_code: props.racks.length > 0 ? props.racks[0].code : 'A-01',
    zone: props.racks.length > 0 ? (props.racks[0].zone || 'Zone A') : 'Zone A',
    label: 'Rak Penyimpanan Komponen',
    barcode_type: 'CODE128',
    custom_code: '',
    description: '',
});

const isSubmitting = ref(false);

const samplePreviewCode = computed(() => {
    if (form.custom_code && form.custom_code.trim() !== '') {
        return form.custom_code.trim().toUpperCase();
    }
    const cleanRack = (form.rack_code || 'A01').replace(/[^a-zA-Z0-9]/g, '');
    const prefix = form.barcode_type === 'QR_CODE' ? 'QR' : 'BC';
    return `${prefix}-${cleanRack}-8942`;
});

function submitGenerate() {
    isSubmitting.value = true;
    form.post(route('barcodes.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isSubmitting.value = false;
            form.reset('custom_code', 'description');
            if (props.racks.length > 0) {
                form.rack_code = props.racks[0].code;
                form.zone = props.racks[0].zone || 'Zone A';
            }
        },
        onError: () => {
            isSubmitting.value = false;
        },
    });
}

function resetForm() {
    form.reset();
    if (props.racks.length > 0) {
        form.rack_code = props.racks[0].code;
        form.zone = props.racks[0].zone || 'Zone A';
    }
}

// ==========================================
// Filtering & Search
// ==========================================
const filterStatus = ref(props.filters.status || 'ALL');
const filterRack = ref(props.filters.rack || 'ALL');
const searchKeyword = ref(props.filters.search || '');

function applyFilters(newFilters = {}) {
    if (newFilters.status !== undefined) filterStatus.value = newFilters.status;
    if (newFilters.rack !== undefined) filterRack.value = newFilters.rack;
    if (newFilters.search !== undefined) searchKeyword.value = newFilters.search;

    router.get(
        route('barcodes.index'),
        {
            status: filterStatus.value,
            rack: filterRack.value,
            search: searchKeyword.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
}

function setStatusFilter(status) {
    filterStatus.value = status;
    applyFilters({ status });
}

// ==========================================
// Status Toggle & Delete Actions
// ==========================================
function toggleStatus(barcode) {
    router.patch(
        route('barcodes.toggle', barcode.id),
        {},
        {
            preserveScroll: true,
        }
    );
}

function deleteBarcode(barcode) {
    if (confirm(`Yakin ingin menghapus barcode ${barcode.barcode_code} untuk Rak ${barcode.rack_code}?`)) {
        router.delete(route('barcodes.destroy', barcode.id), {
            preserveScroll: true,
        });
    }
}

// ==========================================
// Quick Scanner & Stock Mutation Flow
// ==========================================
const isScanning = ref(false);
const scanFeedback = ref(null);
const scanError = ref(null);

const showScanMutationModal = ref(false);
const scanMutationInitialCode = ref('');

async function handleQuickScan(code) {
    if (!code || !code.trim()) return;

    isScanning.value = true;
    scanFeedback.value = null;
    scanError.value = null;

    try {
        const response = await axios.post(route('barcodes.scan'), { code: code.trim() });
        const data = response.data;

        if (data && data.success) {
            scanFeedback.value = data;
            const targetRack = data.barcode ? data.barcode.rack_code : code.trim();
            router.visit(route('racks.show', targetRack));
        } else {
            scanError.value = data?.message || `Barcode '${code}' tidak terdaftar di sistem.`;
        }
    } catch (err) {
        scanError.value = err.response?.data?.message || 'Gagal melakukan pemindaian barcode. Periksa kode rak.';
    } finally {
        isScanning.value = false;
    }
}

function quickFillScan(code) {
    handleQuickScan(code);
}

function openScanMutationModal(codeOrBarcode) {
    const code = typeof codeOrBarcode === 'string'
        ? codeOrBarcode
        : (codeOrBarcode?.barcode_code || codeOrBarcode?.code || '');
    scanMutationInitialCode.value = code;
    showScanMutationModal.value = true;
}

function handleMutationSuccess() {
    router.reload({ preserveScroll: true });
}

// ==========================================
// Modals State
// ==========================================
const showPrintModal = ref(false);
const selectedBarcodeForPrint = ref(null);

function openPrintModal(barcode) {
    selectedBarcodeForPrint.value = barcode;
    showPrintModal.value = true;
}

const showItemsModal = ref(false);
const selectedBarcodeForItems = ref(null);

function openItemsModal(barcode) {
    selectedBarcodeForItems.value = barcode;
    showItemsModal.value = true;
}
</script>

<template>
    <Head title="Manajemen & Generator Barcode Rak" />

    <AuthenticatedLayout>
        <!-- Header Page Section -->
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white flex items-center gap-2.5">
                        <span class="p-2 bg-amber-500/10 border border-amber-500/30 rounded-lg text-amber-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </span>
                        Manajemen & Generator Barcode Rak Gudang
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">
                        Generate barcode fisik dan QR code terhubung lokasi rak, pantau barcode aktif, cetak stiker rak, serta audit pemindaian.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-mono font-semibold bg-emerald-500/10 border border-emerald-500/25 text-emerald-400">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Modul Barcode: Aktif & Siap Cetak
                    </span>
                </div>
            </div>
        </template>

        <!-- KPI Metrics Strip -->
        <BarcodeMetrics
            :metrics="props.metrics"
            :filter-status="filterStatus"
            @select-status="setStatusFilter"
        />

        <!-- Upper Grid: Generator Form & Live Sticker Preview + Quick Scanner -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
            <!-- Left: Generator Form (7 cols) -->
            <div class="lg:col-span-7">
                <BarcodeGenerator
                    :form="form"
                    :racks="props.racks"
                    :is-submitting="isSubmitting"
                    @submit="submitGenerate"
                    @reset="resetForm"
                />
            </div>

            <!-- Right: Live Physical Sticker Preview & Quick Scanner (5 cols) -->
            <div class="lg:col-span-5 flex flex-col gap-6">
                <!-- Physical Warehouse Sticker Card Preview -->
                <BarcodePreviewCard
                    :form="form"
                    :sample-code="samplePreviewCode"
                />

                <!-- Quick Scan Simulator Box -->
                <BarcodeScannerBox
                    :is-scanning="isScanning"
                    :scan-feedback="scanFeedback"
                    :scan-error="scanError"
                    @scan="handleQuickScan"
                    @open-mutation="openScanMutationModal"
                />
            </div>
        </div>

        <!-- Active Barcodes Table Section -->
        <BarcodeTable
            :barcodes="props.barcodes"
            :racks="props.racks"
            :filters="props.filters"
            :metrics="props.metrics"
            @filter-change="applyFilters"
            @toggle-status="toggleStatus"
            @delete-barcode="deleteBarcode"
            @open-mutation="openScanMutationModal"
            @open-items="openItemsModal"
            @open-print="openPrintModal"
            @quick-scan="quickFillScan"
        />

        <!-- Scan Activity & Audit Logs -->
        <BarcodeLogs :logs="props.logs" class="mb-8" />

        <!-- Modals -->
        <!-- Print Sticker Modal -->
        <PrintStickerModal
            v-model="showPrintModal"
            :barcode="selectedBarcodeForPrint"
        />

        <!-- Rack Items Detail Modal -->
        <RackItemsModal
            v-model="showItemsModal"
            :barcode="selectedBarcodeForItems"
            @open-print="openPrintModal"
            @open-mutation="openScanMutationModal"
        />

        <!-- Scan & Stock Mutation Modal -->
        <RackScanModal
            v-model="showScanMutationModal"
            :initial-code="scanMutationInitialCode"
            :suggested-racks="props.racks"
            @mutated="handleMutationSuccess"
        />
    </AuthenticatedLayout>
</template>
