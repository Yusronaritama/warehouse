<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    items: { type: Array, required: true },
    categories: { type: Array, required: true },
    rackLocations: { type: Array, default: () => [] },
});

const emit = defineEmits([
    'stockIn',
    'stockOut',
    'updateLocation',
    'scanRack',
    'editItem',
    'deleteItem',
    'openCreateDrawer',
]);

// Filter & Quick Tab States
const quickTab = ref('all'); // 'all' | 'critical' | 'recent'
const searchQuery = ref('');
const selectedCategory = ref('All');
const selectedRack = ref('All');

// Inline editing location state
const editingItemId = ref(null);
const editLocationText = ref('');
const isCustomLocation = ref(false);

// Generate rack options strictly from DB racks (1 option per rack in DB)
const dbRackOptions = computed(() => {
    const options = new Set();

    // 1. Dari props.rackLocations (data rak asli dari tabel rack_locations di DB)
    if (Array.isArray(props.rackLocations)) {
        props.rackLocations.forEach((r) => {
            if (r && r.code) {
                const rawZone = (r.zone || '').trim();
                const zone = rawZone
                    ? (rawZone.toLowerCase().startsWith('zone') ? rawZone : `Zone ${rawZone}`)
                    : `Zone ${r.code.charAt(0)}`;
                options.add(`${zone} (${r.code})`);
            }
        });
    }

    // 2. Pastikan nilai lokasi item yang sedang diedit tetap tampil sebagai opsi aktif
    if (editLocationText.value && editLocationText.value.trim()) {
        options.add(editLocationText.value.trim());
    }

    return Array.from(options).sort();
});

function startEditLocation(item) {
    editingItemId.value = item.id;
    editLocationText.value = item.location;
    // Default mode: pilih dari opsi rak yang ada di DB
    isCustomLocation.value = false;
}

function saveLocation(item) {
    if (editLocationText.value.trim() && editLocationText.value.trim() !== item.location) {
        emit('updateLocation', { item, newLocation: editLocationText.value.trim() });
    }
    editingItemId.value = null;
    isCustomLocation.value = false;
}

function cancelEditLocation() {
    editingItemId.value = null;
    isCustomLocation.value = false;
}

// Extract available rack codes from items & props
const availableRacks = computed(() => {
    const list = new Set();
    if (props.rackLocations && props.rackLocations.length > 0) {
        props.rackLocations.forEach(r => list.add(r.code));
    }
    props.items.forEach(item => {
        if (item.location) {
            const match = item.location.match(/\(([A-Z0-9-]+)\)/);
            if (match && match[1]) list.add(match[1]);
        }
    });
    return Array.from(list);
});

// Filtered and sorted items
const filteredItems = computed(() => {
    let result = [...props.items];

    // Quick Tab filter
    if (quickTab.value === 'critical') {
        result = result.filter(item => item.stock <= item.minStock || item.status === 'Low Stock');
    } else if (quickTab.value === 'recent') {
        result.sort((a, b) => b.id - a.id);
    }

    // Search query filter
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase().trim();
        result = result.filter(item =>
            item.name.toLowerCase().includes(query) ||
            item.code.toLowerCase().includes(query) ||
            item.location.toLowerCase().includes(query) ||
            (item.category && item.category.toLowerCase().includes(query))
        );
    }

    // Category filter
    if (selectedCategory.value !== 'All') {
        result = result.filter(item => item.category === selectedCategory.value);
    }

    // Rack filter
    if (selectedRack.value !== 'All') {
        result = result.filter(item => item.location.includes(selectedRack.value));
    }

    return result;
});
</script>

<template>
    <div id="inventory-table" class="bg-[#3B4252] border border-[#4C566A]/60 rounded-xl overflow-hidden shadow-xs mb-6">
        <!-- 1. Action Toolbar: 1 Baris Menempel di Atas Tabel (Nordic Dim Surface: bg-[#3B4252]) -->
        <div class="p-3.5 lg:p-4 border-b border-[#4C566A]/60 bg-[#3B4252] flex flex-col gap-3">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                <!-- Sisi Kiri: Tab Cepat, Search Input, & Filter Dropdowns -->
                <div class="flex flex-wrap items-center gap-2.5 flex-1">
                    <!-- Tab Cepat ('Semua', 'Stok Kritis', 'Terbaru') -->
                    <div class="inline-flex p-1 rounded-lg bg-[#2E3440] border border-[#4C566A] shrink-0">
                        <button
                            type="button"
                            @click="quickTab = 'all'"
                            class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all"
                            :class="quickTab === 'all' ? 'bg-[#5E81AC] text-[#ECEFF4] shadow-xs font-bold' : 'text-[#A5B1C2] hover:text-[#ECEFF4]'"
                        >
                            Semua
                        </button>
                        <button
                            type="button"
                            @click="quickTab = 'critical'"
                            class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all flex items-center gap-1.5"
                            :class="quickTab === 'critical' ? 'bg-[#BF616A]/20 text-[#BF616A] border border-[#BF616A]/40 shadow-xs font-bold' : 'text-[#A5B1C2] hover:text-[#BF616A]'"
                        >
                            <span>Stok Kritis</span>
                            <span class="w-1.5 h-1.5 rounded-full bg-[#BF616A]"></span>
                        </button>
                        <button
                            type="button"
                            @click="quickTab = 'recent'"
                            class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all"
                            :class="quickTab === 'recent' ? 'bg-[#434C5E] text-[#ECEFF4] border border-[#4C566A] shadow-xs font-bold' : 'text-[#A5B1C2] hover:text-[#ECEFF4]'"
                        >
                            Terbaru
                        </button>
                    </div>

                    <!-- Input Search (bg-[#2E3440] border-[#4C566A] text-[#ECEFF4] placeholder-[#8C96A9]) -->
                    <div class="relative flex-1 min-w-[180px] max-w-sm">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari SKU, Nama, atau Rak..."
                            class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg pl-8 pr-3 py-1.5 text-xs text-[#ECEFF4] placeholder-[#8C96A9] focus:outline-none focus:border-[#88C0D0] focus:ring-1 focus:ring-[#88C0D0] transition-colors"
                        />
                        <svg class="w-3.5 h-3.5 text-[#8C96A9] absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <button
                            v-if="searchQuery"
                            @click="searchQuery = ''"
                            class="absolute right-2 top-2 text-[#8C96A9] hover:text-[#ECEFF4]"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Filter Kategori & Rak -->
                    <div class="flex items-center gap-2">
                        <select
                            v-model="selectedCategory"
                            class="bg-[#2E3440] border border-[#4C566A] rounded-lg py-1.5 px-2.5 text-xs text-[#ECEFF4] focus:border-[#88C0D0] focus:ring-0"
                        >
                            <option value="All">Semua Kategori</option>
                            <option v-for="cat in categories.filter(c => c !== 'All')" :key="cat" :value="cat">{{ cat }}</option>
                        </select>

                        <select
                            v-if="availableRacks.length > 0"
                            v-model="selectedRack"
                            class="bg-[#2E3440] border border-[#4C566A] rounded-lg py-1.5 px-2.5 text-xs text-[#ECEFF4] font-mono focus:border-[#88C0D0] focus:ring-0"
                        >
                            <option value="All">Semua Rak</option>
                            <option v-for="rack in availableRacks" :key="rack" :value="rack">Rak {{ rack }}</option>
                        </select>
                    </div>
                </div>

                <!-- Sisi Kanan: Tombol Solid + Tambah Barang & Tombol Sekunder Scan Rak -->
                <div class="flex items-center gap-2 shrink-0 self-end lg:self-center">
                    <!-- Tombol Sekunder: Scan Rak (bg-[#434C5E] hover:bg-[#4C566A] text-[#ECEFF4] border border-[#4C566A]) -->
                    <button
                        type="button"
                        @click="emit('scanRack', '')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#434C5E] hover:bg-[#4C566A] text-[#ECEFF4] border border-[#4C566A] text-xs font-semibold transition-all active:scale-95 shadow-xs"
                        title="Buka Pemindai Barcode Rak"
                    >
                        <svg class="w-4 h-4 text-[#88C0D0]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        <span>Scan Rak</span>
                    </button>

                    <!-- Tombol CTA Utama: + Tambah Barang (bg-[#5E81AC] hover:bg-[#81A1C1] text-[#ECEFF4]) -->
                    <button
                        type="button"
                        @click="emit('openCreateDrawer')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-[#5E81AC] hover:bg-[#81A1C1] text-[#ECEFF4] text-xs font-bold shadow-xs active:scale-95 transition-all"
                        title="Buka Slide-Over Tambah Barang"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        <span>+ Tambah Barang</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 2. Mode Desktop (hidden md:block): Tabel Ringkas Maksimal 5 Kolom -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-sm text-[#D8DEE9]">
                <!-- Header Tabel: bg-[#434C5E]/60 text-[#D8DEE9] border-b border-[#4C566A]/60 -->
                <thead class="bg-[#434C5E]/60 text-[11px] uppercase font-semibold text-[#D8DEE9] border-b border-[#4C566A]/60 tracking-wider">
                    <tr>
                        <!-- Kolom 1: Nama + SKU font-mono di 1 sel -->
                        <th class="px-4 py-3">Nama Barang & SKU</th>
                        <!-- Kolom 2: Kategori & Kondisi -->
                        <th class="px-4 py-3">Kategori</th>
                        <!-- Kolom 3: Lokasi Rak + Kapasitas di 1 sel -->
                        <th class="px-4 py-3">Lokasi Rak & Kapasitas</th>
                        <!-- Kolom 4: Jumlah Stok dengan Quick Stepper (-) dan (+) -->
                        <th class="px-4 py-3 text-center">Stok Fisik</th>
                        <!-- Kolom 5: Aksi 1-Klik Langsung (Relokasi, Edit, Hapus) -->
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <!-- Efek Hover Baris Tabel: hover:bg-[#434C5E]/50 -->
                <tbody class="divide-y divide-[#4C566A]/40">
                    <tr
                        v-for="item in filteredItems"
                        :key="item.id"
                        class="hover:bg-[#434C5E]/50 transition-colors group"
                    >
                        <!-- Kolom 1: Nama Barang + SKU font-mono + Soft Aurora Badge Status di 1 sel -->
                        <td class="px-4 py-3.5">
                            <div>
                                <div class="font-medium text-[#ECEFF4] group-hover:text-[#88C0D0] transition-colors">
                                    {{ item.name }}
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="font-mono text-xs font-semibold text-[#D8DEE9] bg-[#2E3440] px-1.5 py-0.5 rounded border border-[#4C566A]/60">
                                        {{ item.code }}
                                    </span>
                                    <!-- Badge Status Soft Aurora -->
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium border"
                                        :class="{
                                            'bg-[#A3BE8C]/15 text-[#A3BE8C] border-[#A3BE8C]/30': item.status === 'Ready',
                                            'bg-[#EBCB8B]/15 text-[#EBCB8B] border-[#EBCB8B]/30': item.status === 'Low Stock',
                                            'bg-[#BF616A]/15 text-[#BF616A] border-[#BF616A]/30': item.status === 'In Maintenance',
                                        }"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="{
                                            'bg-[#A3BE8C]': item.status === 'Ready',
                                            'bg-[#EBCB8B]': item.status === 'Low Stock',
                                            'bg-[#BF616A]': item.status === 'In Maintenance',
                                        }"></span>
                                        {{ item.status }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        <!-- Kolom 2: Kategori & Kondisi -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-block text-xs text-[#D8DEE9] bg-[#2E3440] px-2.5 py-1 rounded-md border border-[#4C566A]/60">
                                {{ item.category }}
                            </span>
                            <div class="text-[11px] text-[#A5B1C2] mt-1">
                                Kondisi: <span class="text-[#ECEFF4] font-medium">{{ item.condition }}</span>
                            </div>
                        </td>

                        <!-- Kolom 3: Lokasi Rak + Kapasitas di 1 sel -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <!-- View Mode -->
                            <div v-if="editingItemId !== item.id" class="flex flex-col gap-1 items-start">
                                <button
                                    type="button"
                                    @click="emit('scanRack', item.location)"
                                    title="Klik untuk membuka mutasi rak ini"
                                    class="inline-flex items-center gap-1.5 font-mono text-xs text-[#ECEFF4] bg-[#2E3440] hover:bg-[#434C5E] hover:text-[#88C0D0] hover:border-[#88C0D0]/50 px-2.5 py-1 rounded-md border border-[#4C566A]/60 transition-all"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#EBCB8B]"></span>
                                    <span>{{ item.location }}</span>
                                    <svg class="w-3 h-3 text-[#A5B1C2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01" />
                                    </svg>
                                </button>
                                <div class="text-[10px] text-[#A5B1C2] flex items-center gap-1 pl-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#A3BE8C]"></span>
                                    <span>Slot Rak Tersedia</span>
                                </div>
                            </div>

                            <!-- Inline Edit Mode -->
                            <div v-else class="flex flex-col gap-1.5 min-w-[210px]">
                                <div class="flex items-center gap-1">
                                    <!-- Option Box (Select Rak dari DB) -->
                                    <select
                                        v-if="!isCustomLocation"
                                        v-model="editLocationText"
                                        class="bg-[#2E3440] border border-[#88C0D0] rounded px-2 py-1 text-xs text-[#ECEFF4] font-mono flex-1 focus:outline-none focus:ring-1 focus:ring-[#88C0D0]"
                                    >
                                        <option value="" disabled>-- Pilih Rak DB --</option>
                                        <option v-for="opt in dbRackOptions" :key="opt" :value="opt">
                                            {{ opt }}
                                        </option>
                                    </select>

                                    <!-- Input Box (Ketik Manual / Rename) -->
                                    <input
                                        v-else
                                        v-model="editLocationText"
                                        type="text"
                                        class="bg-[#2E3440] border border-[#88C0D0] rounded px-2 py-1 text-xs text-[#ECEFF4] font-mono flex-1 focus:outline-none focus:ring-1 focus:ring-[#88C0D0]"
                                        @keyup.enter="saveLocation(item)"
                                        @keyup.esc="cancelEditLocation"
                                        placeholder="cth: Zone A (A-01) Rak 1"
                                        autofocus
                                    />

                                    <!-- Toggle Button -->
                                    <button
                                        type="button"
                                        @click="isCustomLocation = !isCustomLocation"
                                        class="p-1 rounded bg-[#3B4252] border border-[#4C566A] text-[#88C0D0] hover:bg-[#434C5E] text-[10px] font-mono"
                                        :title="isCustomLocation ? 'Pilih Rak dari DB' : 'Ketik Manual / Rename'"
                                    >
                                        {{ isCustomLocation ? 'DB' : '✏️' }}
                                    </button>

                                    <!-- Simpan -->
                                    <button
                                        @click="saveLocation(item)"
                                        class="p-1 rounded bg-[#5E81AC] text-[#ECEFF4] hover:bg-[#81A1C1]"
                                        title="Simpan Lokasi"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>

                                    <!-- Batal -->
                                    <button
                                        @click="cancelEditLocation"
                                        class="p-1 rounded bg-[#434C5E] text-[#A5B1C2] hover:text-[#ECEFF4]"
                                        title="Batal"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex items-center justify-between text-[10px] text-[#A5B1C2]">
                                    <span>{{ isCustomLocation ? 'Mode: Rename Manual' : 'Mode: Opsi Rak DB' }}</span>
                                    <button
                                        type="button"
                                        @click="isCustomLocation = !isCustomLocation"
                                        class="text-[#88C0D0] hover:underline"
                                    >
                                        {{ isCustomLocation ? 'Pilih opsi DB' : '+ Ketik Manual' }}
                                    </button>
                                </div>
                            </div>
                        </td>

                        <!-- Kolom 4: Jumlah Stok dengan Quick Stepper (-) dan (+) -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-center">
                            <div class="inline-flex items-center gap-1.5 bg-[#2E3440] border border-[#4C566A]/60 rounded-lg p-1">
                                <!-- Ghost minus button -->
                                <button
                                    type="button"
                                    @click="emit('stockOut', item)"
                                    class="w-6 h-6 rounded flex items-center justify-center text-[#A5B1C2] hover:bg-[#434C5E] hover:text-[#ECEFF4] active:scale-95 transition-all text-xs font-bold"
                                    title="Stok Keluar (Outbound)"
                                >
                                    -
                                </button>

                                <!-- Angka Stok Font-Mono -->
                                <span
                                    class="font-mono font-bold text-sm min-w-[36px] text-center"
                                    :class="item.stock <= item.minStock ? 'text-[#BF616A]' : 'text-[#ECEFF4]'"
                                >
                                    {{ item.stock }}
                                </span>

                                <!-- Ghost plus button -->
                                <button
                                    type="button"
                                    @click="emit('stockIn', item)"
                                    class="w-6 h-6 rounded flex items-center justify-center text-[#A5B1C2] hover:bg-[#434C5E] hover:text-[#ECEFF4] active:scale-95 transition-all text-xs font-bold"
                                    title="Stok Masuk (Inbound)"
                                >
                                    +
                                </button>
                            </div>
                            <div class="text-[10px] text-[#A5B1C2] mt-1 font-mono">
                                {{ item.base_unit || item.unit }} (Min: {{ item.minStock }})
                            </div>
                        </td>

                        <!-- Kolom 5: Aksi 1-Klik Langsung (Ghost Buttons Soft Aurora) -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-1">
                                <!-- Tombol Relokasi (hover:bg-[#88C0D0]/15 hover:text-[#88C0D0]) -->
                                <button
                                    type="button"
                                    @click="startEditLocation(item)"
                                    class="p-1.5 rounded-lg text-[#A5B1C2] hover:bg-[#88C0D0]/15 hover:text-[#88C0D0] transition-colors"
                                    title="Relokasi Rak"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                </button>

                                <!-- Tombol Edit (hover:bg-[#EBCB8B]/15 hover:text-[#EBCB8B]) -->
                                <button
                                    type="button"
                                    @click="emit('editItem', item)"
                                    class="p-1.5 rounded-lg text-[#A5B1C2] hover:bg-[#EBCB8B]/15 hover:text-[#EBCB8B] transition-colors"
                                    title="Edit Barang"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </button>

                                <!-- Tombol Hapus (hover:bg-[#BF616A]/15 hover:text-[#BF616A]) -->
                                <button
                                    type="button"
                                    @click="emit('deleteItem', item)"
                                    class="p-1.5 rounded-lg text-[#A5B1C2] hover:bg-[#BF616A]/15 hover:text-[#BF616A] transition-colors"
                                    title="Hapus Barang"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="filteredItems.length === 0">
                        <td colspan="5" class="px-5 py-12 text-center text-[#A5B1C2]">
                            <p class="text-sm font-medium">Tidak ada barang yang cocok dengan filter pencarian.</p>
                            <p class="text-xs text-[#8C96A9] mt-1">Coba sesuaikan kata kunci pencarian atau reset filter.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 3. Mode Mobile (md:hidden): Card List Vertikal Tanpa Horizontal Scroll (Nordic Dim: bg-[#3B4252] border-[#4C566A]/60) -->
        <div class="block md:hidden p-3 space-y-3">
            <div
                v-for="item in filteredItems"
                :key="item.id"
                class="bg-[#3B4252] border border-[#4C566A]/60 rounded-xl p-3.5 space-y-3 shadow-xs"
            >
                <!-- Card Header: Nama & Status Badge Soft Aurora -->
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h4 class="text-sm font-semibold text-[#ECEFF4] leading-tight">{{ item.name }}</h4>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="font-mono text-xs font-bold text-[#D8DEE9] bg-[#2E3440] px-1.5 py-0.5 rounded border border-[#4C566A]/60">
                                {{ item.code }}
                            </span>
                            <span class="text-[11px] text-[#A5B1C2] bg-[#2E3440] px-2 py-0.5 rounded border border-[#4C566A]/60">
                                {{ item.category }}
                            </span>
                        </div>
                    </div>

                    <!-- Badge Status Soft Aurora -->
                    <span
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium border shrink-0"
                        :class="{
                            'bg-[#A3BE8C]/15 text-[#A3BE8C] border-[#A3BE8C]/30': item.status === 'Ready',
                            'bg-[#EBCB8B]/15 text-[#EBCB8B] border-[#EBCB8B]/30': item.status === 'Low Stock',
                            'bg-[#BF616A]/15 text-[#BF616A] border-[#BF616A]/30': item.status === 'In Maintenance',
                        }"
                    >
                        <span class="w-1.5 h-1.5 rounded-full" :class="{
                            'bg-[#A3BE8C]': item.status === 'Ready',
                            'bg-[#EBCB8B]': item.status === 'Low Stock',
                            'bg-[#BF616A]': item.status === 'In Maintenance',
                        }"></span>
                        {{ item.status }}
                    </span>
                </div>

                <!-- Card Meta: Lokasi Rak & Kondisi -->
                <div class="flex items-center justify-between text-xs text-[#A5B1C2] pt-2 border-t border-[#4C566A]/40">
                    <button
                        type="button"
                        @click="emit('scanRack', item.location)"
                        class="inline-flex items-center gap-1.5 font-mono text-xs text-[#ECEFF4] bg-[#2E3440] px-2 py-1 rounded border border-[#4C566A]/60"
                    >
                        <svg class="w-3.5 h-3.5 text-[#EBCB8B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <span>{{ item.location }}</span>
                    </button>
                    <span class="text-[11px] text-[#A5B1C2]">Kondisi: <span class="text-[#ECEFF4] font-medium">{{ item.condition }}</span></span>
                </div>

                <!-- Card Footer Action Bar: Stepper (-) (+), Relokasi, Edit, Hapus -->
                <div class="flex items-center justify-between gap-2 pt-2 border-t border-[#4C566A]/40">
                    <!-- Stock Stepper Cepat -->
                    <div class="inline-flex items-center gap-1.5 bg-[#2E3440] border border-[#4C566A]/60 rounded-lg p-1">
                        <button
                            type="button"
                            @click="emit('stockOut', item)"
                            class="w-7 h-7 rounded flex items-center justify-center text-[#A5B1C2] hover:bg-[#434C5E] hover:text-[#ECEFF4] border border-[#4C566A]/60 text-xs font-bold active:scale-95 transition-all"
                            title="Keluar"
                        >
                            -
                        </button>
                        <div class="px-1.5 text-center">
                            <span class="font-mono font-bold text-xs" :class="item.stock <= item.minStock ? 'text-[#BF616A]' : 'text-[#ECEFF4]'">
                                {{ item.stock }}
                            </span>
                            <span class="text-[9px] text-[#A5B1C2] block font-mono">{{ item.base_unit || item.unit }}</span>
                        </div>
                        <button
                            type="button"
                            @click="emit('stockIn', item)"
                            class="w-7 h-7 rounded flex items-center justify-center text-[#A5B1C2] hover:bg-[#434C5E] hover:text-[#ECEFF4] border border-[#4C566A]/60 text-xs font-bold active:scale-95 transition-all"
                            title="Masuk"
                        >
                            +
                        </button>
                    </div>

                    <!-- Direct Action Buttons (Relokasi, Edit, Hapus) -->
                    <div class="flex items-center gap-1">
                        <!-- Relokasi (Soft Aurora Frost) -->
                        <button
                            type="button"
                            @click="startEditLocation(item)"
                            class="p-2 rounded-lg bg-[#88C0D0]/15 text-[#88C0D0] border border-[#88C0D0]/30 hover:bg-[#88C0D0]/25 transition-colors"
                            title="Relokasi Rak"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                        </button>

                        <!-- Edit (Soft Aurora Yellow) -->
                        <button
                            type="button"
                            @click="emit('editItem', item)"
                            class="p-2 rounded-lg bg-[#EBCB8B]/15 text-[#EBCB8B] border border-[#EBCB8B]/30 hover:bg-[#EBCB8B]/25 transition-colors"
                            title="Edit Barang"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </button>

                        <!-- Hapus (Soft Aurora Red) -->
                        <button
                            type="button"
                            @click="emit('deleteItem', item)"
                            class="p-2 rounded-lg bg-[#BF616A]/15 text-[#BF616A] border border-[#BF616A]/30 hover:bg-[#BF616A]/25 transition-colors"
                            title="Hapus Barang"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Inline Edit Location on Mobile if activated -->
                <div v-if="editingItemId === item.id" class="p-3 bg-[#2E3440] rounded-xl border border-[#88C0D0]/60 space-y-2.5 shadow-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold text-[#88C0D0] flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ isCustomLocation ? 'Rename Lokasi Rak' : 'Pilih Rak dari Database' }}
                        </span>
                        <!-- Toggle Mode Button -->
                        <button
                            type="button"
                            @click="isCustomLocation = !isCustomLocation"
                            class="text-[11px] text-[#81A1C1] hover:text-[#88C0D0] underline font-medium"
                        >
                            {{ isCustomLocation ? '📋 Pilih Rak dari DB' : '✏️ Ketik Manual / Rename' }}
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Mode 1: Opsi Box (Select Dropdown dengan Rak dari DB) -->
                        <select
                            v-if="!isCustomLocation"
                            v-model="editLocationText"
                            class="flex-1 bg-[#3B4252] border border-[#4C566A] rounded-lg px-2.5 py-2 text-xs text-[#ECEFF4] font-mono focus:outline-none focus:border-[#88C0D0]"
                        >
                            <option value="" disabled>-- Pilih Rak dari Database --</option>
                            <option v-for="opt in dbRackOptions" :key="opt" :value="opt">
                                {{ opt }}
                            </option>
                        </select>

                        <!-- Mode 2: Input Box (Ketik Manual / Rename Bebas) -->
                        <input
                            v-else
                            v-model="editLocationText"
                            type="text"
                            class="flex-1 bg-[#3B4252] border border-[#4C566A] rounded-lg px-2.5 py-2 text-xs text-[#ECEFF4] font-mono focus:outline-none focus:border-[#88C0D0]"
                            placeholder="cth: Zone A (A-01) Rak 1"
                            autofocus
                        />

                        <!-- Tombol Simpan -->
                        <button
                            @click="saveLocation(item)"
                            class="px-3.5 py-2 rounded-lg bg-[#5E81AC] hover:bg-[#81A1C1] text-[#ECEFF4] text-xs font-semibold active:scale-95 transition-all shadow-xs"
                        >
                            Simpan
                        </button>

                        <!-- Tombol Batal -->
                        <button
                            @click="cancelEditLocation"
                            class="px-3 py-2 rounded-lg bg-[#434C5E] hover:bg-[#4C566A] text-[#A5B1C2] hover:text-[#ECEFF4] text-xs font-medium transition-colors"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State Mobile -->
            <div v-if="filteredItems.length === 0" class="py-8 text-center text-[#A5B1C2] text-xs">
                Tidak ada data barang yang sesuai filter.
            </div>
        </div>
    </div>
</template>
