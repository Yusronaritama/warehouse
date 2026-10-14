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
    <div class="bg-slate-950/80 border border-slate-800 rounded-xl p-5 shadow-lg">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-2.5">
                <span class="p-1.5 bg-blue-500/10 text-blue-400 border border-blue-500/20 rounded-md">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-base font-bold text-white">Log Aktivitas & Audit Pemindaian Barcode</h2>
                    <p class="text-xs text-slate-400">Riwayat kronologis pemindaian, registrasi, status toggle, dan pencetakan stiker rak.</p>
                </div>
            </div>
            <span class="text-xs px-2.5 py-1 rounded bg-slate-900 border border-slate-800 text-slate-400 font-mono">
                {{ logs.length }} Entri Terakhir
            </span>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/90 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-3">Waktu & Tanggal</th>
                        <th class="py-3 px-3">Aksi</th>
                        <th class="py-3 px-3">Kode Barcode</th>
                        <th class="py-3 px-3">Lokasi Rak</th>
                        <th class="py-3 px-3">Operator</th>
                        <th class="py-3 px-3">Catatan / Detail Audit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    <tr v-for="log in logs" :key="log.id" class="hover:bg-slate-900/40 transition-colors">
                        <!-- Waktu -->
                        <td class="py-2.5 px-3 font-mono text-[11px] text-slate-400 whitespace-nowrap">
                            {{ formatDate(log.created_at) }}
                        </td>

                        <!-- Action Badge -->
                        <td class="py-2.5 px-3">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border font-mono"
                                :class="{
                                    'bg-emerald-500/10 text-emerald-400 border-emerald-500/30': log.action === 'GENERATE',
                                    'bg-cyan-500/10 text-cyan-400 border-cyan-500/30': log.action === 'SCAN',
                                    'bg-amber-500/10 text-amber-400 border-amber-500/30': log.action === 'PRINT',
                                    'bg-blue-500/10 text-blue-400 border-blue-500/30': log.action === 'STATUS_CHANGE',
                                    'bg-rose-500/10 text-rose-400 border-rose-500/30': log.action === 'DELETE' || log.action === 'SCAN_FAILED',
                                }"
                            >
                                {{ log.action }}
                            </span>
                        </td>

                        <!-- Barcode -->
                        <td class="py-2.5 px-3 font-mono font-bold text-white text-xs">
                            {{ log.barcode_code || '-' }}
                        </td>

                        <!-- Rak -->
                        <td class="py-2.5 px-3">
                            <span v-if="log.rack_code" class="font-mono text-amber-300 font-semibold">
                                Rak {{ log.rack_code }}
                            </span>
                            <span v-else class="text-slate-500">-</span>
                        </td>

                        <!-- Operator -->
                        <td class="py-2.5 px-3 text-slate-300">
                            {{ log.operator }}
                        </td>

                        <!-- Notes -->
                        <td class="py-2.5 px-3 text-slate-300 text-[11px]">
                            {{ log.notes || '-' }}
                        </td>
                    </tr>

                    <!-- Empty Log State -->
                    <tr v-if="logs.length === 0">
                        <td colspan="6" class="py-6 text-center text-slate-400 text-xs">
                            Belum ada riwayat aktivitas atau pemindaian barcode yang tercatat.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
