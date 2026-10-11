<script setup>
defineProps({
    movements: { type: Array, required: true },
});
</script>

<template>
    <div id="stock-movement" class="bg-slate-950/80 border border-slate-800 rounded-xl p-5 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Aktivitas Mutasi & Pergerakan Stok Terbaru
            </h3>
            <span class="text-xs text-slate-400 font-mono">Live Stream</span>
        </div>

        <div class="space-y-3">
            <div
                v-for="log in movements"
                :key="log.id"
                class="p-3.5 rounded-lg bg-slate-900/60 border border-slate-800 flex items-center justify-between gap-3 hover:border-slate-700 transition-colors"
            >
                <div class="flex items-center gap-3">
                    <span
                        class="px-2 py-1 rounded text-xs font-bold border font-mono"
                        :class="log.badgeClass"
                    >
                        {{ log.type }}
                    </span>
                    <div>
                        <div class="text-sm font-semibold text-slate-200">{{ log.itemName }}</div>
                        <div class="text-xs text-slate-400 flex items-center gap-2">
                            <span>{{ log.sourceOrTarget }}</span>
                            <span>&bull;</span>
                            <span>By {{ log.operator }}</span>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <div class="text-sm font-mono font-bold" :class="log.quantity.startsWith('+') ? 'text-emerald-400' : 'text-amber-400'">
                        {{ log.quantity }}
                    </div>
                    <div class="text-[11px] text-slate-400">{{ log.timestamp }}</div>
                </div>
            </div>
        </div>
    </div>
</template>
