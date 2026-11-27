<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    barcodes: {
        type: Array,
        required: true,
    },
    racks: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ status: 'ALL', rack: 'ALL', search: '' }),
    },
    metrics: {
        type: Object,
        default: () => ({ totalBarcodes: 0, activeCount: 0, inactiveCount: 0 }),
    },
});

const emit = defineEmits([
    'filterChange',
    'toggleStatus',
    'deleteBarcode',
    'openMutation',
    'openItems',
    'openPrint',
    'downloadVisual',
    'quickScan',
]);

const expandedRows = ref([]);
const copyNotification = ref('');

function toggleRowExpand(id) {
    const idx = expandedRows.value.indexOf(id);
    if (idx > -1) {
        expandedRows.value.splice(idx, 1);
    } else {
        expandedRows.value.push(id);
    }
}

function isRowExpanded(id) {
    return expandedRows.value.includes(id);
}

function copyCode(text) {
    navigator.clipboard.writeText(text);
    copyNotification.value = text;
    setTimeout(() => {
        copyNotification.value = '';
    }, 2000);
}

function formatDate(isoString) {
    if (!isoString) return '-';
    const date = new Date(isoString);
    return date.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

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
</script>

<template>
    <div class="bg-[#3B4252] border border-[#4C566A]/60 rounded-xl p-5 shadow-lg mb-8">
        <!-- Table Top Header & Filter Controls -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-[#4C566A]/60">
            <div>
                <h2 class="text-base font-bold text-[#ECEFF4] flex items-center gap-2">
                    <span class="p-1 bg-[#434C5E] text-[#88C0D0] border border-[#4C566A] rounded">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                    </span>
                    Daftar Barcode Rak Terdaftar & Status Aktif
                </h2>
                <p class="text-xs text-[#A5B1C2] mt-0.5">
                    Daftar barcode rak gudang yang aktif untuk tagging fisik, cetak stiker, atau pemantauan stok.
                </p>
            </div>

            <!-- Filters & Search Controls -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Status Filter Tabs -->
                <div class="inline-flex rounded-lg border border-[#4C566A]/60 bg-[#2E3440] p-1 text-xs">
                    <button
                        type="button"
                        @click="emit('filterChange', { status: 'ALL' })"
                        class="px-2.5 py-1 rounded-md transition-colors"
                        :class="filters.status === 'ALL' ? 'bg-[#5E81AC] text-[#ECEFF4] font-semibold shadow-xs' : 'text-[#A5B1C2] hover:text-[#ECEFF4]'"
                    >
                        Semua ({{ metrics.totalBarcodes }})
                    </button>
                    <button
                        type="button"
                        @click="emit('filterChange', { status: 'ACTIVE' })"
                        class="px-2.5 py-1 rounded-md transition-colors"
                        :class="filters.status === 'ACTIVE' ? 'bg-[#A3BE8C]/20 text-[#A3BE8C] font-semibold border border-[#A3BE8C]/30' : 'text-[#A5B1C2] hover:text-[#ECEFF4]'"
                    >
                        Aktif ({{ metrics.activeCount }})
                    </button>
                    <button
                        type="button"
                        @click="emit('filterChange', { status: 'INACTIVE' })"
                        class="px-2.5 py-1 rounded-md transition-colors"
                        :class="filters.status === 'INACTIVE' ? 'bg-[#BF616A]/20 text-[#BF616A] font-semibold border border-[#BF616A]/30' : 'text-[#A5B1C2] hover:text-[#ECEFF4]'"
                    >
                        Nonaktif ({{ metrics.inactiveCount }})
                    </button>
                </div>

                <!-- Rack Filter Select -->
                <select
                    :value="filters.rack"
                    @change="emit('filterChange', { rack: $event.target.value })"
                    class="bg-[#2E3440] border border-[#4C566A] rounded-lg px-2.5 py-1.5 text-xs text-[#ECEFF4] focus:border-[#88C0D0] focus:ring-0"
                >
                    <option value="ALL">Semua Lokasi Rak</option>
                    <option v-for="r in racks" :key="r.id" :value="r.code">
                        Rak {{ r.code }}
                    </option>
                </select>

                <!-- Search Input -->
                <div class="relative">
                    <input
                        :value="filters.search"
                        @input="emit('filterChange', { search: $event.target.value })"
                        type="text"
                        placeholder="Cari barcode / rak..."
                        class="bg-[#2E3440] border border-[#4C566A] rounded-lg pl-8 pr-3 py-1.5 text-xs text-[#ECEFF4] placeholder-[#8C96A9] focus:border-[#88C0D0] focus:ring-0 w-36 sm:w-48 font-mono"
                    />
                    <svg class="w-3.5 h-3.5 text-[#8C96A9] absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Copy Notification Banner -->
        <div v-if="copyNotification" class="mt-3 p-2 bg-[#88C0D0]/15 border border-[#88C0D0]/30 text-[#88C0D0] text-xs rounded-lg flex items-center justify-between">
            <span>Kode <strong>{{ copyNotification }}</strong> berhasil disalin ke clipboard!</span>
            <span class="text-[10px]">&times;</span>
        </div>

        <!-- Barcode Table -->
        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-xs text-[#D8DEE9]">
                <thead class="bg-[#434C5E]/60 text-[#D8DEE9] uppercase text-[10px] tracking-wider border-b border-[#4C566A]/60">
                    <tr>
                        <th class="py-3 px-3">Tipe</th>
                        <th class="py-3 px-3">Kode Barcode</th>
                        <th class="py-3 px-3">Lokasi Rak</th>
                        <th class="py-3 px-3">Label & Spesifikasi</th>
                        <th class="py-3 px-3 text-center">Total Scan</th>
                        <th class="py-3 px-3">Terakhir Dipindai</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#4C566A]/40">
                    <template v-for="item in barcodes" :key="item.id">
                        <!-- Main Barcode Row -->
                        <tr class="hover:bg-[#434C5E]/50 transition-colors">
                            <!-- Tipe Barcode Badge -->
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span
                                    class="px-2 py-0.5 rounded text-[10px] font-mono font-bold border"
                                    :class="item.barcode_type === 'QR_CODE' ? 'bg-[#EBCB8B]/15 text-[#EBCB8B] border-[#EBCB8B]/30' : 'bg-[#88C0D0]/15 text-[#88C0D0] border-[#88C0D0]/30'"
                                >
                                    {{ item.barcode_type === 'QR_CODE' ? 'QR Code' : 'Code 128' }}
                                </span>
                            </td>

                            <!-- Kode Barcode & Copy Button -->
                            <td class="py-3 px-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono font-bold text-[#ECEFF4] text-xs">{{ item.barcode_code }}</span>
                                    <button
                                        type="button"
                                        @click="copyCode(item.barcode_code)"
                                        title="Salin kode"
                                        class="p-1 hover:bg-[#434C5E] rounded text-[#A5B1C2] hover:text-[#88C0D0] transition-colors"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>

                            <!-- Lokasi Rak & Accordion Trigger -->
                            <td class="py-3 px-3">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono font-bold text-[#88C0D0]">Rak {{ item.rack_code }}</span>
                                        <span class="px-1.5 py-0.2 rounded bg-[#2E3440] text-[10px] text-[#D8DEE9] border border-[#4C566A]/60">
                                            {{ item.zone || 'Zone A' }}
                                        </span>
                                    </div>
                                    <button
                                        type="button"
                                        @click="toggleRowExpand(item.id)"
                                        title="Klik untuk melihat/sembunyikan item di dalam rak"
                                        class="text-[11px] text-[#A5B1C2] hover:text-[#88C0D0] mt-1 flex items-center gap-1 transition-colors text-left"
                                    >
                                        <span :class="item.items_count > 0 ? 'text-[#A3BE8C] font-semibold' : 'text-[#8C96A9]'">
                                            {{ item.items_count }} item ({{ item.total_quantity || 0 }} total)
                                        </span>
                                        <svg
                                            class="w-3.5 h-3.5 transition-transform"
                                            :class="isRowExpanded(item.id) ? 'rotate-180 text-[#88C0D0]' : 'text-[#A5B1C2]'"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>
                            </td>

                            <!-- Label & Spesifikasi -->
                            <td class="py-3 px-3">
                                <div class="font-medium text-[#ECEFF4]">{{ item.label }}</div>
                                <div v-if="item.description" class="text-[11px] text-[#A5B1C2] line-clamp-1 mt-0.5">
                                    {{ item.description }}
                                </div>
                            </td>

                            <!-- Total Scan Button -->
                            <td class="py-3 px-3 text-center">
                                <button
                                    type="button"
                                    @click="emit('quickScan', item.barcode_code)"
                                    title="Klik untuk uji scan barcode ini"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded font-mono font-semibold text-xs transition-colors hover:bg-[#88C0D0]/20"
                                    :class="item.scan_count > 0 ? 'bg-[#88C0D0]/15 text-[#88C0D0] border border-[#88C0D0]/30' : 'bg-[#2E3440] text-[#A5B1C2] border border-[#4C566A]/60'"
                                >
                                    {{ item.scan_count }} scan
                                </button>
                            </td>

                            <!-- Terakhir Dipindai -->
                            <td class="py-3 px-3 text-[#A5B1C2] font-mono text-[11px]">
                                {{ formatDate(item.last_scanned_at) }}
                            </td>

                            <!-- Status Toggle Badge -->
                            <td class="py-3 px-3 text-center">
                                <button
                                    type="button"
                                    @click="emit('toggleStatus', item)"
                                    title="Klik untuk ubah status aktif/nonaktif"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold border transition-all"
                                    :class="item.status === 'ACTIVE' ? 'bg-[#A3BE8C]/15 text-[#A3BE8C] border-[#A3BE8C]/30 hover:bg-[#A3BE8C]/25' : 'bg-[#BF616A]/15 text-[#BF616A] border-[#BF616A]/30 hover:bg-[#BF616A]/25'"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full" :class="item.status === 'ACTIVE' ? 'bg-[#A3BE8C] animate-pulse' : 'bg-[#BF616A]'"></span>
                                    {{ item.status }}
                                </button>
                            </td>

                            <!-- Aksi -->
                            <td class="py-3 px-3 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Kelola Rak & CRUD Dedicated Page -->
                                    <Link
                                        :href="route('racks.show', item.rack_code)"
                                        title="Buka Halaman Kelola Rak (CRUD Barang & Ubah Nama Rak)"
                                        class="px-2.5 py-1.5 bg-[#5E81AC] hover:bg-[#81A1C1] text-[#ECEFF4] rounded-lg transition-all flex items-center gap-1.5 font-bold text-xs shadow-xs"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <span>Kelola Rak</span>
                                    </Link>

                                    <!-- Quick Scan & Stock Mutation Modal Button -->
                                    <button
                                        type="button"
                                        @click="emit('openMutation', item, item.items)"
                                        title="Buka Popup Mutasi Stok Rak Ini (IN / OUT)"
                                        class="px-2 py-1.5 bg-[#434C5E] hover:bg-[#4C566A] text-[#ECEFF4] rounded-lg border border-[#4C566A] transition-all flex items-center gap-1 font-semibold text-xs shadow-xs"
                                    >
                                        <svg class="w-3.5 h-3.5 text-[#EBCB8B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        <span>Mutasi</span>
                                    </button>

                                    <!-- Detail Items Modal Button -->
                                    <button
                                        type="button"
                                        @click="emit('openItems', item)"
                                        title="Lihat Detail Barang & Kuantitas di Rak Ini"
                                        class="p-1.5 bg-[#434C5E] hover:bg-[#88C0D0]/15 hover:text-[#88C0D0] text-[#A5B1C2] rounded border border-[#4C566A] transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </button>

                                    <!-- Print Sticker Modal Button -->
                                    <button
                                        type="button"
                                        @click="emit('openPrint', item)"
                                        title="Cetak Stiker Rak Fisik"
                                        class="p-1.5 bg-[#434C5E] hover:bg-[#EBCB8B]/15 hover:text-[#EBCB8B] text-[#A5B1C2] rounded border border-[#4C566A] transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                    </button>

                                    <!-- Download Barcode -->
                                    <button
                                        type="button"
                                        @click="emit('downloadVisual', item)"
                                        title="Download Gambar Barcode"
                                        class="p-1.5 bg-[#434C5E] hover:bg-[#81A1C1]/15 hover:text-[#81A1C1] text-[#A5B1C2] rounded border border-[#4C566A] transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </button>

                                    <!-- Delete Barcode -->
                                    <button
                                        type="button"
                                        @click="emit('deleteBarcode', item)"
                                        title="Hapus Barcode"
                                        class="p-1.5 bg-[#434C5E] hover:bg-[#BF616A]/15 hover:text-[#BF616A] text-[#A5B1C2] rounded border border-[#4C566A] transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Expandable Items Subrow (Accordion) -->
                        <tr v-if="isRowExpanded(item.id)" class="bg-[#2E3440]/60 border-b border-[#4C566A]/60">
                            <td colspan="8" class="p-3.5 sm:p-4">
                                <div class="bg-[#2E3440] rounded-xl p-4 border border-[#4C566A]/60">
                                    <div class="flex items-center justify-between pb-3 border-b border-[#4C566A]/60 mb-3">
                                        <div class="flex items-center gap-2">
                                            <span class="p-1 bg-[#434C5E] text-[#88C0D0] border border-[#4C566A] rounded">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                </svg>
                                            </span>
                                            <span class="font-bold text-xs text-[#ECEFF4]">
                                                Daftar Barang & Aset di Rak [{{ item.rack_code }}] &bull; Barcode {{ item.barcode_code }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded text-[10px] bg-[#3B4252] text-[#D8DEE9] border border-[#4C566A]/60 font-mono">
                                                {{ item.items?.length || 0 }} SKU &bull; {{ item.total_quantity || 0 }} Total Kuantitas
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <Link
                                                :href="route('racks.show', item.rack_code)"
                                                class="text-xs text-[#ECEFF4] bg-[#5E81AC] hover:bg-[#81A1C1] flex items-center gap-1 font-bold px-2.5 py-1 rounded-lg transition-colors shadow-xs"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                <span>Kelola Rak & CRUD</span>
                                            </Link>
                                            <button
                                                type="button"
                                                @click="emit('openMutation', item, item.items)"
                                                class="text-xs text-[#ECEFF4] hover:text-white flex items-center gap-1 font-semibold bg-[#434C5E] hover:bg-[#4C566A] px-2.5 py-1 rounded-lg border border-[#4C566A] transition-colors"
                                            >
                                                <svg class="w-3.5 h-3.5 text-[#EBCB8B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                                <span>Mutasi Stok</span>
                                            </button>
                                            <button
                                                type="button"
                                                @click="emit('openItems', item)"
                                                class="text-xs text-[#A5B1C2] hover:text-[#ECEFF4] flex items-center gap-1 font-semibold"
                                            >
                                                <span>Detail Penuh</span>
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Mini items table -->
                                    <div v-if="item.items && item.items.length > 0" class="overflow-x-auto">
                                        <table class="w-full text-left text-xs">
                                            <thead>
                                                <tr class="text-[10px] uppercase tracking-wider text-[#A5B1C2] border-b border-[#4C566A]/60">
                                                    <th class="py-2 px-2.5">Kode / SKU</th>
                                                    <th class="py-2 px-2.5">Nama Barang</th>
                                                    <th class="py-2 px-2.5">Kategori</th>
                                                    <th class="py-2 px-2.5 text-right">Jumlah & Satuan</th>
                                                    <th class="py-2 px-2.5 text-center">Status</th>
                                                    <th class="py-2 px-2.5 text-center">Kondisi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-[#4C566A]/40">
                                                <tr v-for="it in item.items" :key="it.id" class="hover:bg-[#434C5E]/40">
                                                    <td class="py-2 px-2.5 font-mono text-[11px] text-[#88C0D0] font-bold whitespace-nowrap">
                                                        {{ it.code }}
                                                    </td>
                                                    <td class="py-2 px-2.5 font-medium text-[#ECEFF4]">
                                                        {{ it.name }}
                                                    </td>
                                                    <td class="py-2 px-2.5">
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium border" :class="getCategoryBadge(it.category)">
                                                            {{ it.category }}
                                                        </span>
                                                    </td>
                                                    <td class="py-2 px-2.5 text-right font-mono">
                                                        <span class="text-sm font-bold text-[#ECEFF4]">{{ it.stock }}</span>
                                                        <span class="text-[11px] text-[#A5B1C2] ml-1">{{ it.unit }}</span>
                                                    </td>
                                                    <td class="py-2 px-2.5 text-center">
                                                        <span
                                                            class="px-2 py-0.5 rounded text-[10px] font-semibold border"
                                                            :class="it.stock <= (it.min_stock || 5) ? 'bg-[#EBCB8B]/15 text-[#EBCB8B] border-[#EBCB8B]/30' : 'bg-[#A3BE8C]/15 text-[#A3BE8C] border-[#A3BE8C]/30'"
                                                        >
                                                            {{ it.status }}
                                                        </span>
                                                    </td>
                                                    <td class="py-2 px-2.5 text-center text-[11px] text-[#A5B1C2]">
                                                        {{ it.condition || 'Good' }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div v-else class="text-xs text-[#A5B1C2] py-3 text-center">
                                        Belum ada barang/aset yang disimpan di lokasi rak ini.
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty State -->
                    <tr v-if="barcodes.length === 0">
                        <td colspan="8" class="py-8 text-center text-[#A5B1C2]">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-10 h-10 text-[#4C566A] mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-sm font-semibold text-[#D8DEE9]">Belum ada barcode yang cocok dengan filter</p>
                                <p class="text-xs text-[#8C96A9] mt-1">Gunakan form di atas untuk men-generate barcode rak pertama Anda.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
