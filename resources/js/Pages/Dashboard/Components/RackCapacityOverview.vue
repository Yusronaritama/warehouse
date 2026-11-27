<script setup>
defineProps({
    rackLocations: { type: Array, required: true },
});
</script>

<template>
    <div id="rack-locations" class="bg-[#3B4252] border border-[#4C566A]/60 rounded-xl p-4 lg:p-5 shadow-xs">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-[#ECEFF4] flex items-center gap-2">
                <span class="p-1.5 rounded-lg bg-[#434C5E] text-[#88C0D0] border border-[#4C566A]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </span>
                <span>Pemetaan Rak & Kapasitas Fisik</span>
            </h3>
            <span class="text-xs text-[#A3BE8C] font-mono font-medium bg-[#A3BE8C]/15 px-2 py-0.5 rounded-full border border-[#A3BE8C]/30">
                {{ rackLocations.length }} Zona Terpetakan
            </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <div
                v-for="rack in rackLocations"
                :key="rack.code"
                class="p-3 rounded-lg bg-[#2E3440] border border-[#4C566A]/60 flex flex-col justify-between hover:border-[#88C0D0]/50 transition-colors"
            >
                <div>
                    <div class="flex items-center justify-between">
                        <span class="font-mono font-bold text-sm text-[#88C0D0]">{{ rack.code }}</span>
                        <span
                            class="text-[10px] px-1.5 py-0.5 rounded font-mono font-medium border"
                            :class="rack.status === 'high' ? 'bg-[#BF616A]/15 text-[#BF616A] border-[#BF616A]/30' : 'bg-[#A3BE8C]/15 text-[#A3BE8C] border-[#A3BE8C]/30'"
                        >
                            {{ rack.capacity }}
                        </span>
                    </div>
                    <div class="text-xs text-[#ECEFF4] mt-1 font-medium">{{ rack.zone }}</div>
                    <div class="text-[11px] text-[#A5B1C2]">{{ rack.type }}</div>
                </div>

                <!-- Mini progress bar -->
                <div class="mt-3 w-full bg-[#434C5E] h-1.5 rounded-full overflow-hidden">
                    <div
                        class="h-full rounded-full transition-all duration-500"
                        :class="rack.status === 'high' ? 'bg-[#BF616A]' : 'bg-[#5E81AC]'"
                        :style="{ width: rack.capacity }"
                    ></div>
                </div>
            </div>
        </div>

        <div class="mt-4 p-2.5 rounded-lg bg-[#2E3440] border border-[#4C566A]/60 text-xs text-[#A5B1C2] flex items-center justify-between">
            <span class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-[#88C0D0]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Perlu pemindahan stok antar rak?
            </span>
            <span class="text-[#88C0D0] font-medium">Gunakan tombol Relokasi 1-klik di tabel</span>
        </div>
    </div>
</template>
