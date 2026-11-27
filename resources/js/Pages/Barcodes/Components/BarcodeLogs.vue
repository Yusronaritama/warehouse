<script setup>
defineProps({
    logs: {
        type: Array,
        required: true,
    },
});

function formatDate(isoString) {
    if (!isoString) return '-';
    const date = new Date(isoString);
    return date.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <div class="bg-[#3B4252] border border-[#4C566A]/60 rounded-xl p-5 shadow-lg">
        <div class="flex items-center justify-between pb-4 border-b border-[#4C566A]/60">
            <div class="flex items-center gap-2.5">
                <span class="p-1.5 bg-[#434C5E] text-[#88C0D0] border border-[#4C566A] rounded-md">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-base font-bold text-[#ECEFF4]">Log Aktivitas & Audit Pemindaian Barcode</h2>
                    <p class="text-xs text-[#A5B1C2]">Riwayat kronologis pemindaian, registrasi, status toggle, dan pencetakan stiker rak.</p>
                </div>
            </div>
            <span class="text-xs px-2.5 py-1 rounded bg-[#2E3440] border border-[#4C566A]/60 text-[#D8DEE9] font-mono">
                {{ logs.length }} Entri Terakhir
            </span>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-xs text-[#D8DEE9]">
                <thead class="bg-[#434C5E]/60 text-[#D8DEE9] uppercase text-[10px] tracking-wider border-b border-[#4C566A]/60">
                    <tr>
                        <th class="py-3 px-3">Waktu & Tanggal</th>
                        <th class="py-3 px-3">Aksi</th>
                        <th class="py-3 px-3">Kode Barcode</th>
                        <th class="py-3 px-3">Lokasi Rak</th>
                        <th class="py-3 px-3">Operator</th>
                        <th class="py-3 px-3">Catatan / Detail Audit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#4C566A]/40">
                    <tr v-for="log in logs" :key="log.id" class="hover:bg-[#434C5E]/50 transition-colors">
                        <!-- Waktu -->
                        <td class="py-2.5 px-3 font-mono text-[11px] text-[#A5B1C2] whitespace-nowrap">
                            {{ formatDate(log.created_at) }}
                        </td>

                        <!-- Action Badge -->
                        <td class="py-2.5 px-3">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border font-mono"
                                :class="{
                                    'bg-[#A3BE8C]/15 text-[#A3BE8C] border-[#A3BE8C]/30': log.action === 'GENERATE',
                                    'bg-[#88C0D0]/15 text-[#88C0D0] border-[#88C0D0]/30': log.action === 'SCAN',
                                    'bg-[#EBCB8B]/15 text-[#EBCB8B] border-[#EBCB8B]/30': log.action === 'PRINT',
                                    'bg-[#81A1C1]/15 text-[#81A1C1] border-[#81A1C1]/30': log.action === 'STATUS_CHANGE',
                                    'bg-[#BF616A]/15 text-[#BF616A] border-[#BF616A]/30': log.action === 'DELETE' || log.action === 'SCAN_FAILED',
                                }"
                            >
                                {{ log.action }}
                            </span>
                        </td>

                        <!-- Barcode -->
                        <td class="py-2.5 px-3 font-mono font-bold text-[#ECEFF4] text-xs">
                            {{ log.barcode_code || '-' }}
                        </td>

                        <!-- Rak -->
                        <td class="py-2.5 px-3">
                            <span v-if="log.rack_code" class="font-mono text-[#88C0D0] font-semibold">
                                Rak {{ log.rack_code }}
                            </span>
                            <span v-else class="text-[#8C96A9]">-</span>
                        </td>

                        <!-- Operator -->
                        <td class="py-2.5 px-3 text-[#ECEFF4]">
                            {{ log.operator }}
                        </td>

                        <!-- Notes -->
                        <td class="py-2.5 px-3 text-[#A5B1C2] text-[11px]">
                            {{ log.notes || '-' }}
                        </td>
                    </tr>

                    <!-- Empty Log State -->
                    <tr v-if="logs.length === 0">
                        <td colspan="6" class="py-6 text-center text-[#A5B1C2] text-xs">
                            Belum ada riwayat aktivitas atau pemindaian barcode yang tercatat.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
