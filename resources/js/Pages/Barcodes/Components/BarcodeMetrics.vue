<script setup>
defineProps({
    metrics: {
        type: Object,
        default: () => ({
            totalBarcodes: 0,
            activeCount: 0,
            inactiveCount: 0,
            totalScans: 0,
        }),
    },
    filterStatus: {
        type: String,
        default: 'ALL',
    },
});

const emit = defineEmits(['selectStatus']);
</script>

<template>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <!-- Card 1: Total Barcodes -->
        <div
            @click="emit('selectStatus', 'ALL')"
            class="cursor-pointer bg-slate-950/70 border rounded-xl p-4 transition-all"
            :class="filterStatus === 'ALL' ? 'border-amber-500 bg-amber-500/5 ring-1 ring-amber-500/30' : 'border-slate-800 hover:border-slate-700'"
        >
            <div class="text-xs text-slate-400 font-semibold uppercase">Total Barcode Rak</div>
            <div class="text-2xl font-bold text-white mt-1">{{ metrics.totalBarcodes }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Semua entri rak terdaftar</div>
        </div>

        <!-- Card 2: Barcode Aktif -->
        <div
            @click="emit('selectStatus', 'ACTIVE')"
            class="cursor-pointer bg-slate-950/70 border rounded-xl p-4 transition-all"
            :class="filterStatus === 'ACTIVE' ? 'border-emerald-500 bg-emerald-500/5 ring-1 ring-emerald-500/30' : 'border-slate-800 hover:border-slate-700'"
        >
            <div class="text-xs text-slate-400 font-semibold uppercase flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                Barcode Aktif
            </div>
            <div class="text-2xl font-bold text-emerald-400 mt-1">{{ metrics.activeCount }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Siap scan di rak gudang</div>
        </div>

        <!-- Card 3: Barcode Nonaktif -->
        <div
            @click="emit('selectStatus', 'INACTIVE')"
            class="cursor-pointer bg-slate-950/70 border rounded-xl p-4 transition-all"
            :class="filterStatus === 'INACTIVE' ? 'border-rose-500 bg-rose-500/5 ring-1 ring-rose-500/30' : 'border-slate-800 hover:border-slate-700'"
        >
            <div class="text-xs text-slate-400 font-semibold uppercase flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full bg-rose-400"></span>
                Barcode Nonaktif
            </div>
            <div class="text-2xl font-bold text-rose-400 mt-1">{{ metrics.inactiveCount }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Ditangguhkan / diganti</div>
        </div>

        <!-- Card 4: Total Pemindaian -->
        <div class="bg-slate-950/70 border border-slate-800 rounded-xl p-4">
            <div class="text-xs text-slate-400 font-semibold uppercase flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full bg-cyan-400"></span>
                Total Pemindaian
            </div>
            <div class="text-2xl font-bold text-cyan-400 mt-1">{{ metrics.totalScans }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Akumulasi scan fisik</div>
        </div>
    </div>
</template>
