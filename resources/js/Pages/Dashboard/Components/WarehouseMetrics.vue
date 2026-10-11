<script setup>
defineProps({
    itemsCount: { type: Number, required: true },
    totalUnits: { type: Number, required: true },
    readyItems: { type: Number, required: true },
    lowStockCount: { type: Number, required: true },
    occupancyPercent: { type: Number, default: 0 },
    emptyRacksCount: { type: Number, default: 0 },
});
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Metric 1: Total SKU -->
        <div class="bg-slate-950/60 border border-slate-800/80 rounded-xl p-4.5 relative overflow-hidden group hover:border-slate-700 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total SKU Terdaftar</span>
                <span class="p-1.5 rounded-lg bg-blue-500/10 text-blue-400 border border-blue-500/20">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-bold tracking-tight text-white">{{ itemsCount }}</span>
                <span class="text-xs text-slate-400">katalog aset</span>
            </div>
            <div class="mt-2 text-xs text-slate-500">
                Akumulasi kuantitas: <span class="text-slate-300 font-semibold">{{ totalUnits }} total unit</span>
            </div>
        </div>

        <!-- Metric 2: Ready / Operasional -->
        <div class="bg-slate-950/60 border border-slate-800/80 rounded-xl p-4.5 relative overflow-hidden group hover:border-slate-700 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kondisi Siap Pakai</span>
                <span class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-bold tracking-tight text-emerald-400">{{ readyItems }}</span>
                <span class="text-xs text-slate-400">SKU Operasional</span>
            </div>
            <div class="mt-2 text-xs text-slate-500">
                Kelayakan alat: <span class="text-emerald-400 font-semibold">{{ itemsCount ? Math.round((readyItems / itemsCount) * 100) : 0 }}%</span>
            </div>
        </div>

        <!-- Metric 3: Low Stock Warning -->
        <div class="bg-slate-950/60 border border-slate-800/80 rounded-xl p-4.5 relative overflow-hidden group hover:border-slate-700 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Peringatan Menipis</span>
                <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-bold tracking-tight text-amber-400">{{ lowStockCount }}</span>
                <span class="text-xs text-slate-400">perlu restock</span>
            </div>
            <div class="mt-2 text-xs text-amber-500/90 font-medium">
                {{ lowStockCount > 0 ? 'Segera buat purchase order inbound' : 'Semua stok aman' }}
            </div>
        </div>

        <!-- Metric 4: Actual Rack Occupancy -->
        <div class="bg-slate-950/60 border border-slate-800/80 rounded-xl p-4.5 relative overflow-hidden group hover:border-slate-700 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Okupansi Rak Gudang</span>
                <span class="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-bold tracking-tight text-white">{{ occupancyPercent }}%</span>
                <span class="text-xs text-slate-400">kapasitas terisi</span>
            </div>
            <div class="mt-2 text-xs text-slate-500">
                <template v-if="itemsCount === 0">
                    Gudang kosong (0% terpakai) &bull; Semua slot tersedia
                </template>
                <template v-else>
                    Tersedia {{ emptyRacksCount }} rak kosong siap pakai
                </template>
            </div>
        </div>
    </div>
</template>
