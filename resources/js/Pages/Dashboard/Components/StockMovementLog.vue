<script setup>
defineProps({
    movements: { type: Array, required: true },
});
</script>

<template>
    <div id="stock-movement" class="bg-[#3B4252] border border-[#4C566A]/60 rounded-xl p-4 lg:p-5 shadow-xs">
        <!-- Header -->
        <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-[#4C566A]/60">
            <div class="flex items-center gap-2">
                <span class="p-1.5 rounded-lg bg-[#434C5E] text-[#88C0D0] border border-[#4C566A]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-[#ECEFF4]">Riwayat Mutasi Stok</h3>
                    <p class="text-[11px] text-[#A5B1C2]">Vertical Timeline Transaksi</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-mono text-[#A3BE8C] bg-[#A3BE8C]/15 border border-[#A3BE8C]/30 font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-[#A3BE8C] animate-pulse"></span>
                Live Stream
            </span>
        </div>

        <!-- Vertical Timeline List (before:bg-[#4C566A]) -->
        <div class="relative pl-4 space-y-4 before:absolute before:left-1.5 before:top-2 before:bottom-2 before:w-px before:bg-[#4C566A]/70">
            <div
                v-for="log in movements"
                :key="log.id"
                class="relative pl-3 group"
            >
                <!-- Timeline Dot Indicator (Soft Aurora) -->
                <span
                    class="absolute -left-[14px] top-1.5 w-2.5 h-2.5 rounded-full border-2 border-[#3B4252] transition-transform group-hover:scale-125"
                    :class="{
                        'bg-[#A3BE8C] ring-2 ring-[#A3BE8C]/30': log.type === 'IN',
                        'bg-[#EBCB8B] ring-2 ring-[#EBCB8B]/30': log.type === 'OUT',
                        'bg-[#88C0D0] ring-2 ring-[#88C0D0]/30': log.type !== 'IN' && log.type !== 'OUT',
                    }"
                ></span>

                <!-- Content Card Inside Timeline (bg-[#2E3440] border-[#4C566A]/60) -->
                <div class="p-2.5 rounded-lg bg-[#2E3440] border border-[#4C566A]/60 hover:bg-[#434C5E]/50 transition-colors">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span
                                    class="px-1.5 py-0.5 rounded text-[10px] font-bold font-mono border"
                                    :class="{
                                        'bg-[#A3BE8C]/15 text-[#A3BE8C] border-[#A3BE8C]/30': log.type === 'IN',
                                        'bg-[#EBCB8B]/15 text-[#EBCB8B] border-[#EBCB8B]/30': log.type === 'OUT',
                                        'bg-[#88C0D0]/15 text-[#88C0D0] border-[#88C0D0]/30': log.type !== 'IN' && log.type !== 'OUT',
                                    }"
                                >
                                    {{ log.type }}
                                </span>
                                <span class="text-xs font-semibold text-[#ECEFF4] truncate block">
                                    {{ log.itemName }}
                                </span>
                            </div>
                            <div class="text-[11px] text-[#A5B1C2] mt-1 truncate">
                                {{ log.sourceOrTarget }}
                            </div>
                        </div>

                        <!-- Quantity Pill -->
                        <div class="text-right shrink-0">
                            <div
                                class="text-xs font-mono font-bold"
                                :class="log.quantity.startsWith('+') ? 'text-[#A3BE8C]' : 'text-[#EBCB8B]'"
                            >
                                {{ log.quantity }}
                            </div>
                        </div>
                    </div>

                    <!-- Footer: Operator & Timestamp -->
                    <div class="flex items-center justify-between text-[10px] text-[#A5B1C2] pt-1.5 mt-1.5 border-t border-[#4C566A]/40">
                        <span class="truncate">Opr: {{ log.operator }}</span>
                        <span class="font-mono text-[#D8DEE9] shrink-0">{{ log.timestamp }}</span>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="movements.length === 0" class="py-8 text-center text-[#A5B1C2] text-xs">
                Belum ada catatan mutasi stok hari ini.
            </div>
        </div>
    </div>
</template>
