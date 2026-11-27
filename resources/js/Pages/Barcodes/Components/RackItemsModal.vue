<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    barcode: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue', 'openPrint', 'openMutation']);

const itemsModalSearch = ref('');

const filteredModalItems = computed(() => {
    if (!props.barcode || !props.barcode.items) return [];
    const q = itemsModalSearch.value.trim().toLowerCase();
    if (!q) return props.barcode.items;

    return props.barcode.items.filter(
        (it) =>
            (it.name && it.name.toLowerCase().includes(q)) ||
            (it.code && it.code.toLowerCase().includes(q)) ||
            (it.category && it.category.toLowerCase().includes(q))
    );
});

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

function closeModal() {
    emit('update:modelValue', false);
    itemsModalSearch.value = '';
}

function handlePrint() {
    const b = props.barcode;
    closeModal();
    emit('openPrint', b);
}

function handleMutation() {
    const b = props.barcode;
    closeModal();
    emit('openMutation', b);
}
</script>

<template>
    <div
        v-if="modelValue && barcode"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
        @click.self="closeModal"
    >
        <div class="bg-[#3B4252] border border-[#4C566A]/60 rounded-2xl max-w-3xl w-full p-6 shadow-2xl relative text-[#ECEFF4] flex flex-col max-h-[90vh]">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-[#4C566A]/60">
                <div class="flex items-center gap-3">
                    <span class="p-2 bg-[#434C5E] border border-[#4C566A] text-[#88C0D0] rounded-xl">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base sm:text-lg font-bold text-[#ECEFF4]">
                                Daftar Item di Rak {{ barcode.rack_code }}
                            </h3>
                            <span class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-[#88C0D0]/15 text-[#88C0D0] border border-[#88C0D0]/30">
                                {{ barcode.barcode_code }}
                            </span>
                        </div>
                        <p class="text-xs text-[#A5B1C2] mt-0.5">
                            {{ barcode.zone || 'Zone A' }} &bull; {{ barcode.label }}
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="closeModal"
                    class="p-1.5 rounded-lg text-[#A5B1C2] hover:text-[#ECEFF4] hover:bg-[#434C5E] transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Summary Ribbon inside Modal -->
            <div class="grid grid-cols-3 gap-3 my-4">
                <div class="bg-[#2E3440] border border-[#4C566A]/60 rounded-xl p-3">
                    <div class="text-[10px] text-[#A5B1C2] uppercase font-semibold">Total Ragam SKU</div>
                    <div class="text-xl font-bold text-[#ECEFF4] mt-0.5 font-mono">
                        {{ barcode.items?.length || 0 }} SKU
                    </div>
                </div>
                <div class="bg-[#2E3440] border border-[#4C566A]/60 rounded-xl p-3">
                    <div class="text-[10px] text-[#A5B1C2] uppercase font-semibold">Total Kuantitas Fisik</div>
                    <div class="text-xl font-bold text-[#A3BE8C] mt-0.5 font-mono">
                        {{ barcode.total_quantity || 0 }} Total
                    </div>
                </div>
                <div class="bg-[#2E3440] border border-[#4C566A]/60 rounded-xl p-3">
                    <div class="text-[10px] text-[#A5B1C2] uppercase font-semibold">Status Barcode</div>
                    <div class="text-xs font-bold mt-1.5 flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full" :class="barcode.status === 'ACTIVE' ? 'bg-[#A3BE8C]' : 'bg-[#BF616A]'"></span>
                        <span :class="barcode.status === 'ACTIVE' ? 'text-[#A3BE8C]' : 'text-[#BF616A]'">
                            {{ barcode.status }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Search Input inside Modal -->
            <div class="mb-3 flex items-center justify-between gap-3">
                <div class="relative flex-1">
                    <input
                        v-model="itemsModalSearch"
                        type="text"
                        placeholder="Cari item dalam rak ini berdasarkan nama, kode SKU, atau kategori..."
                        class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg pl-8 pr-3 py-1.5 text-xs text-[#ECEFF4] placeholder-[#8C96A9] focus:border-[#88C0D0] focus:ring-1 focus:ring-[#88C0D0]"
                    />
                    <svg class="w-3.5 h-3.5 text-[#8C96A9] absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <span class="text-xs text-[#A5B1C2] whitespace-nowrap font-mono">
                    {{ filteredModalItems.length }} item ditampilkan
                </span>
            </div>

            <!-- Scrollable Items Table -->
            <div class="flex-1 overflow-y-auto border border-[#4C566A]/60 rounded-xl bg-[#2E3440]/60">
                <table class="w-full text-left text-xs text-[#D8DEE9]">
                    <thead class="bg-[#434C5E]/60 sticky top-0 text-[#D8DEE9] uppercase text-[10px] tracking-wider border-b border-[#4C566A]/60">
                        <tr>
                            <th class="py-2.5 px-3">No.</th>
                            <th class="py-2.5 px-3">Kode SKU</th>
                            <th class="py-2.5 px-3">Nama Barang / Aset</th>
                            <th class="py-2.5 px-3">Kategori</th>
                            <th class="py-2.5 px-3 text-right">Jumlah (Stok)</th>
                            <th class="py-2.5 px-3">Satuan</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                            <th class="py-2.5 px-3 text-center">Kondisi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#4C566A]/40">
                        <tr v-for="(it, idx) in filteredModalItems" :key="it.id" class="hover:bg-[#434C5E]/50 transition-colors">
                            <td class="py-3 px-3 text-[#A5B1C2] font-mono text-[11px]">{{ idx + 1 }}</td>
                            <td class="py-3 px-3 font-mono font-bold text-[#88C0D0] whitespace-nowrap">{{ it.code }}</td>
                            <td class="py-3 px-3">
                                <div class="font-medium text-[#ECEFF4]">{{ it.name }}</div>
                                <div class="text-[10px] text-[#A5B1C2] font-mono mt-0.5">{{ it.location }}</div>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium border whitespace-nowrap" :class="getCategoryBadge(it.category)">
                                    {{ it.category }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-sm text-[#ECEFF4]">{{ it.stock }}</td>
                            <td class="py-3 px-3 text-[#A5B1C2] font-mono text-xs">{{ it.unit }}</td>
                            <td class="py-3 px-3 text-center">
                                <span
                                    class="px-2 py-0.5 rounded text-[10px] font-semibold border"
                                    :class="it.status === 'Ready' ? 'bg-[#A3BE8C]/15 text-[#A3BE8C] border-[#A3BE8C]/30' : 'bg-[#EBCB8B]/15 text-[#EBCB8B] border-[#EBCB8B]/30'"
                                >
                                    {{ it.status }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center text-[#A5B1C2] text-[11px]">{{ it.condition || 'Good' }}</td>
                        </tr>
                        <tr v-if="filteredModalItems.length === 0">
                            <td colspan="8" class="py-8 text-center text-[#A5B1C2] text-xs">
                                <p class="font-semibold text-[#ECEFF4]">Tidak ada item yang ditemukan pada rak ini.</p>
                                <p class="text-[11px] text-[#A5B1C2] mt-1">
                                    Anda dapat mengalokasikan barang ke rak ini melalui form Tambah Barang di Dashboard.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modal Actions Footer -->
            <div class="flex items-center justify-between pt-4 border-t border-[#4C566A]/60 mt-4">
                <button
                    type="button"
                    @click="closeModal"
                    class="px-4 py-2 text-xs font-semibold text-[#A5B1C2] hover:text-[#ECEFF4] hover:bg-[#434C5E] rounded-lg transition-colors"
                >
                    Tutup
                </button>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="handleMutation"
                        class="px-4 py-2 text-xs font-semibold text-[#ECEFF4] bg-[#434C5E] hover:bg-[#4C566A] border border-[#4C566A] rounded-lg transition-colors flex items-center gap-1.5"
                    >
                        <svg class="w-4 h-4 text-[#EBCB8B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                        </svg>
                        Mutasi Stok (IN / OUT)
                    </button>
                    <button
                        type="button"
                        @click="handlePrint"
                        class="px-4 py-2 text-xs font-bold text-[#ECEFF4] bg-[#5E81AC] hover:bg-[#81A1C1] rounded-lg shadow-md flex items-center gap-2 transition-all active:scale-95"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Stiker Rak Ini
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
