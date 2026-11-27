<script setup>
import { ref } from 'vue';

const activeTab = ref('racks'); // 'racks' | 'scanner' | 'activity'

// Simulated Rack Data
const rackSlots = ref([
    { id: 'A-01-L1', zone: 'Aisle 01', level: 'Level 1 (Ground Heavy)', usage: 85, item: 'Pallet Jack & Heavy Parts', sku: 'SKU-PLT-004', qty: '24 Unit', status: 'optimal' },
    { id: 'A-01-L2', zone: 'Aisle 01', level: 'Level 2 (Medium Bin)', usage: 45, item: 'Industrial Carton Containers', sku: 'SKU-BOX-112', qty: '80 Pcs', status: 'normal' },
    { id: 'A-01-L3', zone: 'Aisle 01', level: 'Level 3 (Top Rack)', usage: 0, item: 'Slot Kosong', sku: '-', qty: '0 Unit', status: 'empty' },
    { id: 'B-02-L1', zone: 'Aisle 02', level: 'Level 1 (Ground Heavy)', usage: 92, item: 'Hydraulic Valve Assemblies', sku: 'SKU-HYD-550', qty: '32 Unit', status: 'warning' },
    { id: 'B-02-L2', zone: 'Aisle 02', level: 'Level 2 (Medium Bin)', usage: 60, item: 'Stretch Film Packaging Rolls', sku: 'SKU-FLM-201', qty: '45 Roll', status: 'normal' },
    { id: 'B-02-L3', zone: 'Aisle 02', level: 'Level 3 (Top Rack)', usage: 15, item: 'Label Thermal 100x50mm', sku: 'SKU-LBL-010', qty: '180 Roll', status: 'normal' },
]);

const selectedSlot = ref(rackSlots.value[0]);

function selectSlot(slot) {
    selectedSlot.value = slot;
}

// Scanner Simulator State
const scannedCode = ref('WH-KRWNG-RACK-B02');
const scanResult = ref({
    valid: true,
    type: 'Lokasi Rak Fisik',
    title: 'Rak Penyimpanan B-02 (Aisle Barat)',
    capacity: 'Kapasitas 60% Terpakai (3 dari 4 Level Aktif)',
    itemsCount: '77 Item Tersimpan',
    updatedAt: 'Terverifikasi sistem WMS',
});

function testScan(code) {
    scannedCode.value = code;
    if (code.includes('RACK')) {
        scanResult.value = {
            valid: true,
            type: 'Lokasi Rak Fisik',
            title: `Lokasi Rak: ${code}`,
            capacity: 'Status: Aktif terdaftar dalam denah gudang',
            itemsCount: '4 Level Rak (Tersedia Slot Kosong di L-3)',
            updatedAt: 'Terverifikasi sistem WMS',
        };
    } else {
        scanResult.value = {
            valid: true,
            type: 'Barang SKU Inventaris',
            title: 'Hydraulic Valve Assemblies (SKU-HYD-550)',
            capacity: 'Lokasi Simpan: Rak B-02 Level 1',
            itemsCount: 'Jumlah Stok: 32 Unit (Kondisi Baik)',
            updatedAt: 'Terakhir mutasi: Hari ini pukul 08:30 WIB',
        };
    }
}

// Activity Log Data
const recentActivities = [
    { time: '09:42 WIB', type: 'INBOUND', badge: 'Masuk', text: 'Penerimaan 50 Roll Stretch Film dari Vendor Logistik', rack: 'Rak B-02-L2', operator: 'Ahmad S.' },
    { time: '09:15 WIB', type: 'MUTASI', badge: 'Relokasi', text: 'Pemindahan 8 Unit Pallet Jack dari Transit Bay ke Rak Utama', rack: 'Rak A-01-L1', operator: 'Budi W.' },
    { time: '08:50 WIB', type: 'BARCODE', badge: 'Cetak', text: 'Cetak 25 Label Thermal Barcode Code-128 untuk SKU-BOX-112', rack: 'Station Cetak 01', operator: 'Rian P.' },
    { time: '08:10 WIB', type: 'OUTBOUND', badge: 'Keluar', text: 'Pengeluaran 12 Unit Hydraulic Valve untuk Pengiriman', rack: 'Rak B-02-L1', operator: 'Siti M.' },
];
</script>

<template>
    <div class="wh-console-card">
        <!-- Terminal Header -->
        <div class="wh-console-header">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-[#242933] border border-[#4C566A]/60 text-xs font-mono font-medium text-[#D8DEE9]">
                    <span class="w-2 h-2 rounded-full bg-[#A3BE8C]"></span>
                    SIMULASI TERMINAL GUDANG
                </span>
            </div>

            <!-- Tab Switcher (Nordic Dim) -->
            <div class="flex items-center gap-1.5 bg-[#242933] p-1.5 rounded-xl border border-[#4C566A]/60 text-xs">
                <button
                    type="button"
                    class="wh-tab-btn"
                    :class="{ 'wh-tab-btn--active': activeTab === 'racks' }"
                    @click="activeTab = 'racks'"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <span>Kapasitas Rak</span>
                </button>
                <button
                    type="button"
                    class="wh-tab-btn"
                    :class="{ 'wh-tab-btn--active': activeTab === 'scanner' }"
                    @click="activeTab = 'scanner'"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <span>Uji Scan Barcode</span>
                </button>
                <button
                    type="button"
                    class="wh-tab-btn"
                    :class="{ 'wh-tab-btn--active': activeTab === 'activity' }"
                    @click="activeTab = 'activity'"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>Log Mutasi</span>
                </button>
            </div>
        </div>

        <!-- Terminal Body Area -->
        <div class="wh-console-body">
            <!-- TAB 1: RACK MATRIX VIEW -->
            <div v-if="activeTab === 'racks'" class="space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-[#D8DEE9] pb-1">
                    <span>Pilih salah satu slot rak untuk memeriksa alokasi SKU dan kuantitas:</span>
                    <span class="inline-flex items-center gap-1.5 font-mono text-[#88C0D0] bg-[#88C0D0]/10 px-2.5 py-1 rounded-md border border-[#88C0D0]/25">
                        Rata-rata Utilisasi: 65.8%
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    <button
                        v-for="slot in rackSlots"
                        :key="slot.id"
                        type="button"
                        class="wh-slot-item"
                        :class="{ 'wh-slot-item--selected': selectedSlot.id === slot.id }"
                        @click="selectSlot(slot)"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-mono font-bold text-sm text-[#ECEFF4]">{{ slot.id }}</span>
                            <span
                                class="text-[11px] font-mono px-2 py-0.5 rounded-full font-semibold"
                                :class="{
                                    'bg-[#A3BE8C]/15 text-[#A3BE8C] border border-[#A3BE8C]/30': slot.status === 'optimal',
                                    'bg-[#88C0D0]/15 text-[#88C0D0] border border-[#88C0D0]/30': slot.status === 'normal',
                                    'bg-[#EBCB8B]/15 text-[#EBCB8B] border border-[#EBCB8B]/30': slot.status === 'warning',
                                    'bg-[#434C5E]/60 text-[#A5B1C2] border border-[#4C566A]': slot.status === 'empty'
                                }"
                            >
                                {{ slot.usage }}% Terisi
                            </span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="w-full bg-[#242933] rounded-full h-2 mb-2.5 overflow-hidden border border-[#4C566A]/40">
                            <div
                                class="h-2 rounded-full transition-all duration-300"
                                :class="{
                                    'bg-[#A3BE8C]': slot.status === 'optimal',
                                    'bg-[#88C0D0]': slot.status === 'normal',
                                    'bg-[#EBCB8B]': slot.status === 'warning',
                                    'bg-[#4C566A]': slot.status === 'empty'
                                }"
                                :style="{ width: `${slot.usage}%` }"
                            ></div>
                        </div>

                        <div class="text-left">
                            <p class="text-xs text-[#ECEFF4] font-medium truncate">{{ slot.item }}</p>
                            <p class="text-[11px] text-[#A5B1C2] font-mono mt-0.5">{{ slot.level }}</p>
                        </div>
                    </button>
                </div>

                <!-- Selected Slot Inspector Panel (Nordic Dim) -->
                <div class="wh-inspector-box">
                    <div class="flex items-start justify-between flex-wrap gap-4">
                        <div>
                            <span class="text-xs font-mono uppercase tracking-wider text-[#88C0D0] font-semibold">
                                Detail Slot Terpilih
                            </span>
                            <h4 class="text-base font-bold text-[#ECEFF4] mt-1">
                                {{ selectedSlot.id }} ({{ selectedSlot.zone }})
                            </h4>
                            <p class="text-xs text-[#D8DEE9] mt-1">
                                Konten: <strong class="text-[#ECEFF4]">{{ selectedSlot.item }}</strong> &bull; SKU: <span class="font-mono text-[#88C0D0]">{{ selectedSlot.sku }}</span>
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-[#A5B1C2] block">Kuantitas Tersimpan</span>
                            <span class="text-xl font-mono font-bold text-[#A3BE8C]">{{ selectedSlot.qty }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: BARCODE SIMULATION VIEW -->
            <div v-if="activeTab === 'scanner'" class="space-y-5">
                <div class="text-xs text-[#D8DEE9]">
                    Uji coba bagaimana sistem WAREFIVE membaca barcode rak atau label SKU barang:
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="text-xs text-[#A5B1C2]">Pilih sampel uji:</span>
                    <button
                        type="button"
                        class="px-3 py-1.5 rounded-lg bg-[#2E3440] hover:bg-[#434C5E] text-xs text-[#88C0D0] font-mono border border-[#4C566A] transition-colors"
                        @click="testScan('WH-KRWNG-RACK-B02')"
                    >
                        Barcode Rak: WH-KRWNG-RACK-B02
                    </button>
                    <button
                        type="button"
                        class="px-3 py-1.5 rounded-lg bg-[#2E3440] hover:bg-[#434C5E] text-xs text-[#81A1C1] font-mono border border-[#4C566A] transition-colors"
                        @click="testScan('SKU-HYD-550')"
                    >
                        Barcode Barang: SKU-HYD-550
                    </button>
                </div>

                <!-- Simulated Scanner Display -->
                <div class="p-5 rounded-xl bg-[#2E3440] border border-[#4C566A]/60 flex flex-col md:flex-row items-center gap-6">
                    <!-- SVG Barcode Graphic Mockup -->
                    <div class="flex flex-col items-center bg-white p-3.5 rounded-lg border border-slate-300 shadow-sm">
                        <svg class="h-14 w-48 text-black" viewBox="0 0 200 60" fill="currentColor">
                            <rect x="0" y="0" width="3" height="50"/>
                            <rect x="5" y="0" width="2" height="50"/>
                            <rect x="10" y="0" width="4" height="50"/>
                            <rect x="17" y="0" width="2" height="50"/>
                            <rect x="22" y="0" width="5" height="50"/>
                            <rect x="30" y="0" width="3" height="50"/>
                            <rect x="36" y="0" width="6" height="50"/>
                            <rect x="45" y="0" width="2" height="50"/>
                            <rect x="50" y="0" width="4" height="50"/>
                            <rect x="57" y="0" width="3" height="50"/>
                            <rect x="63" y="0" width="6" height="50"/>
                            <rect x="72" y="0" width="2" height="50"/>
                            <rect x="77" y="0" width="4" height="50"/>
                            <rect x="84" y="0" width="3" height="50"/>
                            <rect x="90" y="0" width="5" height="50"/>
                            <rect x="98" y="0" width="3" height="50"/>
                            <rect x="104" y="0" width="6" height="50"/>
                            <rect x="113" y="0" width="2" height="50"/>
                            <rect x="118" y="0" width="4" height="50"/>
                            <rect x="125" y="0" width="3" height="50"/>
                            <rect x="131" y="0" width="6" height="50"/>
                            <rect x="140" y="0" width="2" height="50"/>
                            <rect x="145" y="0" width="5" height="50"/>
                            <rect x="153" y="0" width="3" height="50"/>
                            <rect x="159" y="0" width="4" height="50"/>
                            <rect x="166" y="0" width="2" height="50"/>
                            <rect x="171" y="0" width="5" height="50"/>
                            <rect x="179" y="0" width="3" height="50"/>
                            <rect x="185" y="0" width="4" height="50"/>
                            <rect x="192" y="0" width="3" height="50"/>
                            <rect x="198" y="0" width="2" height="50"/>
                        </svg>
                        <span class="text-[11px] font-mono text-slate-800 font-bold mt-1 tracking-wider">{{ scannedCode }}</span>
                    </div>

                    <!-- Scan Telemetry Response -->
                    <div class="flex-1 w-full space-y-2 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#A3BE8C]"></span>
                            <span class="font-mono text-[#A3BE8C] font-bold uppercase tracking-wide">{{ scanResult.type }}</span>
                        </div>
                        <h4 class="text-sm font-bold text-[#ECEFF4]">{{ scanResult.title }}</h4>
                        <p class="text-[#D8DEE9]">{{ scanResult.capacity }}</p>
                        <p class="text-[#A5B1C2] font-mono">{{ scanResult.itemsCount }} &bull; {{ scanResult.updatedAt }}</p>
                    </div>
                </div>
            </div>

            <!-- TAB 3: ACTIVITY LOGS VIEW -->
            <div v-if="activeTab === 'activity'" class="space-y-3">
                <div class="text-xs text-[#D8DEE9]">
                    Aktivitas mutasi barang dan pencatatan audit log terkini di gudang:
                </div>

                <div class="space-y-2.5">
                    <div
                        v-for="(act, idx) in recentActivities"
                        :key="idx"
                        class="p-3.5 rounded-xl bg-[#2E3440] border border-[#4C566A]/60 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs"
                    >
                        <div class="flex items-start sm:items-center gap-3">
                            <span class="font-mono text-[#A5B1C2] whitespace-nowrap">{{ act.time }}</span>
                            <span
                                class="px-2.5 py-0.5 rounded text-[10px] font-mono font-bold"
                                :class="{
                                    'bg-[#A3BE8C]/15 text-[#A3BE8C] border border-[#A3BE8C]/30': act.type === 'INBOUND',
                                    'bg-[#EBCB8B]/15 text-[#EBCB8B] border border-[#EBCB8B]/30': act.type === 'MUTASI',
                                    'bg-[#88C0D0]/15 text-[#88C0D0] border border-[#88C0D0]/30': act.type === 'BARCODE',
                                    'bg-[#BF616A]/15 text-[#BF616A] border border-[#BF616A]/30': act.type === 'OUTBOUND',
                                }"
                            >
                                {{ act.badge }}
                            </span>
                            <span class="text-[#ECEFF4]">{{ act.text }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-[#A5B1C2] sm:text-right text-[11px]">
                            <span class="font-mono text-[#88C0D0]">{{ act.rack }}</span>
                            <span class="text-[#4C566A]">&bull;</span>
                            <span class="text-[#A5B1C2]">{{ act.operator }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.wh-console-card {
    width: 100%;
    max-width: 1100px;
    margin: 0 auto;
    background: #3B4252;
    border: 1px solid rgba(76, 86, 106, 0.6);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 35px -10px rgba(36, 41, 51, 0.6);
    transition: border-color 0.2s ease;
}

.wh-console-card:hover {
    border-color: rgba(136, 192, 208, 0.4);
}

.wh-console-header {
    background: #2E3440;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid rgba(76, 86, 106, 0.6);
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.wh-tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.45rem 0.85rem;
    border-radius: 8px;
    color: #A5B1C2;
    background: transparent;
    border: none;
    cursor: pointer;
    font-size: 0.82rem;
    font-weight: 500;
    transition: color 0.15s ease, background-color 0.15s ease;
    min-height: 36px;
}

.wh-tab-btn:hover {
    color: #ECEFF4;
    background: rgba(255, 255, 255, 0.06);
}

.wh-tab-btn:focus-visible {
    outline: 2px solid #88C0D0;
}

.wh-tab-btn--active {
    color: #ECEFF4;
    background: #5E81AC;
    font-weight: 600;
}

.wh-console-body {
    padding: 1.75rem;
    min-height: 290px;
}

.wh-slot-item {
    background: #2E3440;
    border: 1px solid rgba(76, 86, 106, 0.6);
    border-radius: 12px;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.15s ease;
    text-align: left;
    width: 100%;
}

.wh-slot-item:hover {
    border-color: rgba(136, 192, 208, 0.4);
    transform: translateY(-1px);
}

.wh-slot-item:focus-visible {
    outline: 2px solid #88C0D0;
}

.wh-slot-item--selected {
    border-color: #88C0D0;
    background: #434C5E;
    box-shadow: 0 0 14px rgba(136, 192, 208, 0.2);
}

.wh-inspector-box {
    margin-top: 1.25rem;
    padding: 1.25rem 1.5rem;
    border-radius: 12px;
    background: #2E3440;
    border: 1px solid rgba(136, 192, 208, 0.3);
}
</style>