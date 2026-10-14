<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    items: { type: Array, required: true },
    categories: { type: Array, required: true },
});

const emit = defineEmits(['stockIn', 'stockOut', 'updateLocation', 'scanRack']);

const searchQuery = ref('');
const selectedCategory = ref('All');
const selectedStatus = ref('All');

// Inline editing location state
const editingItemId = ref(null);
const editLocationText = ref('');

function startEditLocation(item) {
    editingItemId.value = item.id;
    editLocationText.value = item.location;
}

function saveLocation(item) {
    if (editLocationText.value.trim()) {
        emit('updateLocation', { item, newLocation: editLocationText.value.trim() });
    }
    editingItemId.value = null;
}

function cancelEditLocation() {
    editingItemId.value = null;
}

const filteredItems = computed(() => {
    return props.items.filter(item => {
        const query = searchQuery.value.toLowerCase();
        const matchesQuery = item.name.toLowerCase().includes(query) ||
                             item.code.toLowerCase().includes(query) ||
                             item.location.toLowerCase().includes(query);
        const matchesCategory = selectedCategory.value === 'All' || item.category === selectedCategory.value;
        const matchesStatus = selectedStatus.value === 'All' || item.status === selectedStatus.value;
        return matchesQuery && matchesCategory && matchesStatus;
    });
});
</script>

<template>
    <div id="inventory-table" class="bg-slate-950/80 border border-slate-800 rounded-xl overflow-hidden shadow-xl mb-8">
        <!-- Filter Header Bar -->
        <div class="p-4 sm:p-5 border-b border-slate-800 bg-slate-900/40 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Search -->
            <div class="relative flex-1 max-w-md">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari SKU, Nama Barang, atau Lokasi Rak..."
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg pl-9 pr-4 py-2 text-sm text-slate-100 placeholder-slate-400 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                />
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <!-- Category & Status Filters -->
            <div class="flex flex-wrap items-center gap-2.5">
                <div class="flex items-center gap-1.5 text-xs text-slate-400">
                    <span>Kategori:</span>
                    <select
                        v-model="selectedCategory"
                        class="bg-slate-900 border border-slate-700 rounded-lg py-1.5 px-3 text-xs text-slate-200 focus:border-amber-500 focus:ring-0"
                    >
                        <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                    </select>
                </div>

                <div class="flex items-center gap-1.5 text-xs text-slate-400">
                    <span>Status:</span>
                    <select
                        v-model="selectedStatus"
                        class="bg-slate-900 border border-slate-700 rounded-lg py-1.5 px-3 text-xs text-slate-200 focus:border-amber-500 focus:ring-0"
                    >
                        <option value="All">Semua Status</option>
                        <option value="Ready">Ready</option>
                        <option value="Low Stock">Low Stock</option>
                        <option value="In Maintenance">In Maintenance</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table with explicit Lokasi Rak column -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-900/90 text-xs uppercase font-semibold text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5">Kode SKU / Aset</th>
                        <th class="px-5 py-3.5">Nama Barang & Kategori</th>
                        <th class="px-5 py-3.5 bg-slate-900/40 border-x border-slate-800/60 text-amber-400">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                Lokasi Rak & Posisi Fisik
                            </span>
                        </th>
                        <th class="px-5 py-3.5 text-center">Jumlah Stok</th>
                        <th class="px-5 py-3.5">Status & Kondisi</th>
                        <th class="px-5 py-3.5 text-right">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <tr
                        v-for="item in filteredItems"
                        :key="item.id"
                        class="hover:bg-slate-900/40 transition-colors"
                    >
                        <!-- SKU -->
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="font-mono text-xs font-bold text-amber-400">{{ item.code }}</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">{{ item.lastUpdated }}</div>
                        </td>

                        <!-- Name & Category -->
                        <td class="px-5 py-4">
                            <div class="font-medium text-white">{{ item.name }}</div>
                            <span class="inline-block mt-1 text-[11px] text-slate-400 bg-slate-800/80 px-2 py-0.5 rounded border border-slate-700">
                                {{ item.category }}
                            </span>
                        </td>

                        <!-- Dedicated Interactive Lokasi Rak Column -->
                        <td class="px-5 py-4 whitespace-nowrap bg-slate-900/20 border-x border-slate-800/50">
                            <!-- View Mode -->
                            <div v-if="editingItemId !== item.id" class="flex items-center justify-between gap-3 group">
                                <div class="flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                                    <button
                                        type="button"
                                        @click="emit('scanRack', item.location)"
                                        title="Buka popup mutasi stok untuk rak ini"
                                        class="font-mono text-xs font-semibold text-slate-100 bg-slate-800 hover:bg-amber-500/20 hover:text-amber-300 hover:border-amber-500/50 px-2.5 py-1 rounded border border-slate-700 transition-all flex items-center gap-1.5"
                                    >
                                        <span>{{ item.location }}</span>
                                        <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01" />
                                        </svg>
                                    </button>
                                </div>
                                <button
                                    @click="startEditLocation(item)"
                                    title="Ubah teks lokasi rak"
                                    class="text-slate-400 hover:text-amber-400 text-xs px-1.5 py-0.5 rounded hover:bg-slate-800 transition-colors opacity-75 group-hover:opacity-100 flex items-center gap-1"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                    <span>Edit</span>
                                </button>
                            </div>

                            <!-- Edit Mode -->
                            <div v-else class="flex items-center gap-1.5">
                                <input
                                    v-model="editLocationText"
                                    type="text"
                                    class="bg-slate-950 border border-amber-500 rounded px-2 py-1 text-xs text-white font-mono w-44 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                    @keyup.enter="saveLocation(item)"
                                    @keyup.esc="cancelEditLocation"
                                    placeholder="cth: Zone A (A-01) Rak 2"
                                    autofocus
                                />
                                <button
                                    @click="saveLocation(item)"
                                    class="p-1 rounded bg-amber-500 text-slate-950 font-bold hover:bg-amber-400"
                                    title="Simpan lokasi"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                                <button
                                    @click="cancelEditLocation"
                                    class="p-1 rounded bg-slate-800 text-slate-400 hover:text-white"
                                    title="Batal"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </td>

                        <!-- Stock Multi-UoM -->
                        <td class="px-5 py-4 whitespace-nowrap text-center">
                            <div v-if="item.packaging_unit && item.conversion_factor > 1">
                                <div class="text-sm font-bold font-mono" :class="item.stock <= item.minStock ? 'text-rose-400' : 'text-amber-300'">
                                    {{ item.formatted_stock }}
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                    Total: {{ item.stock }} {{ item.base_unit || item.unit }} &bull; 1 {{ item.packaging_unit }} = {{ item.conversion_factor }} {{ item.base_unit || item.unit }}
                                </div>
                            </div>
                            <div v-else>
                                <div class="text-base font-bold font-mono" :class="item.stock <= item.minStock ? 'text-rose-400' : 'text-slate-100'">
                                    {{ item.stock }}
                                </div>
                                <div class="text-[11px] text-slate-400">{{ item.base_unit || item.unit }} (Min: {{ item.minStock }})</div>
                            </div>
                        </td>

                        <!-- Status & Condition -->
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="flex flex-col gap-1 items-start">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-medium border"
                                    :class="{
                                        'bg-emerald-500/10 text-emerald-400 border-emerald-500/20': item.status === 'Ready',
                                        'bg-amber-500/10 text-amber-400 border-amber-500/20': item.status === 'Low Stock',
                                        'bg-rose-500/10 text-rose-400 border-rose-500/20': item.status === 'In Maintenance',
                                    }"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full" :class="{
                                        'bg-emerald-400': item.status === 'Ready',
                                        'bg-amber-400': item.status === 'Low Stock',
                                        'bg-rose-400': item.status === 'In Maintenance',
                                    }"></span>
                                    {{ item.status }}
                                </span>
                                <span class="text-[11px] text-slate-400">Kondisi: {{ item.condition }}</span>
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="px-5 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <button
                                    @click="emit('stockIn', item)"
                                    title="Catat Stok Masuk (Inbound)"
                                    class="px-2.5 py-1.5 rounded bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 border border-emerald-500/30 text-xs font-semibold flex items-center gap-1 transition-colors"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    In
                                </button>
                                <button
                                    @click="emit('stockOut', item)"
                                    title="Catat Stok Keluar (Outbound)"
                                    class="px-2.5 py-1.5 rounded bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 border border-amber-500/30 text-xs font-semibold flex items-center gap-1 transition-colors"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                    </svg>
                                    Out
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="filteredItems.length === 0">
                        <td colspan="6" class="px-5 py-10 text-center text-slate-400">
                            Tidak ada barang atau aset yang cocok dengan filter pencarian.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
