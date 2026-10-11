<script setup>
defineProps({
    rackLocations: { type: Array, required: true },
});
</script>

<template>
    <div id="rack-locations" class="bg-slate-950/80 border border-slate-800 rounded-xl p-5 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Pemetaan Rak & Kapasitas Fisik
            </h3>
            <span class="text-xs text-emerald-400 font-medium">{{ rackLocations.length }} Zona Terpetakan</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <div
                v-for="rack in rackLocations"
                :key="rack.code"
                class="p-3 rounded-lg bg-slate-900/60 border border-slate-800 flex flex-col justify-between"
            >
                <div>
                    <div class="flex items-center justify-between">
                        <span class="font-mono font-bold text-sm text-amber-400">{{ rack.code }}</span>
                        <span
                            class="text-[10px] px-1.5 py-0.5 rounded font-medium"
                            :class="rack.status === 'high' ? 'bg-rose-500/20 text-rose-300' : 'bg-emerald-500/20 text-emerald-300'"
                        >
                            {{ rack.capacity }}
                        </span>
                    </div>
                    <div class="text-xs text-slate-300 mt-1 font-medium">{{ rack.zone }}</div>
                    <div class="text-[11px] text-slate-400">{{ rack.type }}</div>
                </div>

                <!-- Mini progress bar -->
                <div class="mt-3 w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                    <div
                        class="h-full rounded-full"
                        :class="rack.status === 'high' ? 'bg-rose-500' : 'bg-amber-500'"
                        :style="{ width: rack.capacity }"
                    ></div>
                </div>
            </div>
        </div>

        <div class="mt-4 p-3 rounded-lg bg-slate-900 border border-slate-800 text-xs text-slate-400 flex items-center justify-between">
            <span>Butuh relokasi barang antar rak?</span>
            <span class="text-amber-400 font-medium">Ubah langsung lokasi rak di tabel atas</span>
        </div>
    </div>
</template>
