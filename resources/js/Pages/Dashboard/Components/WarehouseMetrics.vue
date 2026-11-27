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
    <div class="mb-6 space-y-3">
        <!-- 1. Header Ringkas Satu Baris (Nordic Dim) -->
        <div class="flex items-center justify-between text-xs px-0.5">
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-[#A3BE8C] animate-pulse"></span>
                <span class="font-semibold uppercase tracking-wider text-[#D8DEE9] text-[11px] sm:text-xs">
                    Metrik & Indikator Kinerja Gudang
                </span>
                <span class="hidden sm:inline-block text-[#4C566A]">&bull;</span>
                <span class="hidden sm:inline text-[#A5B1C2] text-xs">Live Telemetri Aset</span>
            </div>
            <div class="flex items-center gap-2 text-[#A5B1C2] text-[11px] font-mono">
                <span class="text-[#88C0D0] font-medium">MySQL WMS</span>
                <span class="text-[#4C566A]">&bull;</span>
                <span class="text-[#A3BE8C] font-medium">Online</span>
            </div>
        </div>

        <!-- 2. 4 Kartu KPI: grid-cols-2 gap-3 di Mobile, lg:grid-cols-4 gap-4 di Desktop (Nordic Dim Surface: bg-[#3B4252] border-[#4C566A]/60) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
            <!-- Metric 1: Total SKU -->
            <div class="bg-[#3B4252] border border-[#4C566A]/60 rounded-xl p-3.5 lg:p-4 shadow-xs relative overflow-hidden group hover:border-[#88C0D0]/50 transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-[#A5B1C2]">Total SKU</span>
                    <span class="p-1 sm:p-1.5 rounded-lg bg-[#88C0D0]/15 text-[#88C0D0] border border-[#88C0D0]/30">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-1.5">
                    <span class="text-2xl lg:text-3xl font-bold font-mono tracking-tight text-[#ECEFF4]">{{ itemsCount }}</span>
                    <span class="text-[10px] sm:text-xs text-[#A5B1C2]">SKU</span>
                </div>
                <div class="mt-1.5 text-[10px] sm:text-xs text-[#A5B1C2] truncate">
                    Total unit: <span class="text-[#D8DEE9] font-mono font-semibold">{{ totalUnits }}</span>
                </div>
            </div>

            <!-- Metric 2: Ready / Operasional (Soft Aurora Aman: #A3BE8C) -->
            <div class="bg-[#3B4252] border border-[#4C566A]/60 rounded-xl p-3.5 lg:p-4 shadow-xs relative overflow-hidden group hover:border-[#A3BE8C]/50 transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-[#A5B1C2]">Siap Pakai</span>
                    <span class="p-1 sm:p-1.5 rounded-lg bg-[#A3BE8C]/15 text-[#A3BE8C] border border-[#A3BE8C]/30">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-1.5">
                    <span class="text-2xl lg:text-3xl font-bold font-mono tracking-tight text-[#A3BE8C]">{{ readyItems }}</span>
                    <span class="text-[10px] sm:text-xs text-[#A5B1C2]">SKU</span>
                </div>
                <div class="mt-1.5 text-[10px] sm:text-xs text-[#A5B1C2] truncate">
                    Kelayakan: <span class="text-[#A3BE8C] font-mono font-semibold">{{ itemsCount ? Math.round((readyItems / itemsCount) * 100) : 0 }}%</span>
                </div>
            </div>

            <!-- Metric 3: Low Stock Warning (Soft Aurora Menipis/Kritis: #EBCB8B / #BF616A) -->
            <div class="bg-[#3B4252] border border-[#4C566A]/60 rounded-xl p-3.5 lg:p-4 shadow-xs relative overflow-hidden group hover:border-[#EBCB8B]/50 transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-[#A5B1C2]">Stok Menipis</span>
                    <span
                        class="p-1 sm:p-1.5 rounded-lg border transition-colors"
                        :class="lowStockCount > 0 ? 'bg-[#EBCB8B]/15 text-[#EBCB8B] border-[#EBCB8B]/30' : 'bg-[#434C5E]/60 text-[#A5B1C2] border-[#4C566A]/40'"
                    >
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-1.5">
                    <span
                        class="text-2xl lg:text-3xl font-bold font-mono tracking-tight"
                        :class="lowStockCount > 0 ? 'text-[#EBCB8B]' : 'text-[#ECEFF4]'"
                    >
                        {{ lowStockCount }}
                    </span>
                    <span class="text-[10px] sm:text-xs text-[#A5B1C2]">kritis</span>
                </div>
                <div
                    class="mt-1.5 text-[10px] sm:text-xs font-medium truncate"
                    :class="lowStockCount > 0 ? 'text-[#EBCB8B]' : 'text-[#A5B1C2]'"
                >
                    {{ lowStockCount > 0 ? 'Perlu Restock' : 'Semua stok aman' }}
                </div>
            </div>

            <!-- Metric 4: Actual Rack Occupancy -->
            <div class="bg-[#3B4252] border border-[#4C566A]/60 rounded-xl p-3.5 lg:p-4 shadow-xs relative overflow-hidden group hover:border-[#81A1C1]/50 transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-[#A5B1C2]">Okupansi Rak</span>
                    <span class="p-1 sm:p-1.5 rounded-lg bg-[#81A1C1]/15 text-[#81A1C1] border border-[#81A1C1]/30">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-1.5">
                    <span class="text-2xl lg:text-3xl font-bold font-mono tracking-tight text-[#ECEFF4]">{{ occupancyPercent }}%</span>
                    <span class="text-[10px] sm:text-xs text-[#A5B1C2]">terisi</span>
                </div>
                <div class="mt-1.5 text-[10px] sm:text-xs text-[#A5B1C2] truncate font-mono">
                    {{ emptyRacksCount }} slot rak kosong
                </div>
            </div>
        </div>
    </div>
</template>
