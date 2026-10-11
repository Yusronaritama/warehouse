<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    logs: { type: Object, required: true },
    filters: { type: Object, default: () => ({ type: 'ALL', search: '' }) },
    metrics: { type: Object, default: () => ({ totalLogs: 0, inboundCount: 0, outboundCount: 0, loginCount: 0 }) },
});

const selectedType = ref(props.filters.type || 'ALL');
const searchInput = ref(props.filters.search || '');

function applyFilters() {
    router.get(route('logs.index'), {
        type: selectedType.value,
        search: searchInput.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function selectFilterType(type) {
    selectedType.value = type;
    applyFilters();
}

function formatDate(isoString) {
    if (!isoString) return '-';
    const date = new Date(isoString);
    return date.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
}
</script>

<template>
    <Head title="Audit Log & Aktivitas Sistem" />

    <AuthenticatedLayout>
        <!-- Header Section -->
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white flex items-center gap-2.5">
                        <span class="p-2 bg-blue-500/10 border border-blue-500/30 rounded-lg text-blue-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </span>
                        Log Aktivitas & Audit Trail Seluruh Sistem
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">
                        Pencatatan riwayat kronologis lengkap barang masuk, barang keluar, relokasi rak, hingga autentikasi login pengguna.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-mono font-semibold bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Auto-Reset: Retensi 3 Hari Aktif
                    </span>
                </div>
            </div>
        </template>

        <!-- KPI Metrics Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div
                @click="selectFilterType('ALL')"
                class="cursor-pointer bg-slate-950/70 border rounded-xl p-4 transition-all"
                :class="selectedType === 'ALL' ? 'border-amber-500 bg-amber-500/5 ring-1 ring-amber-500/30' : 'border-slate-800 hover:border-slate-700'"
            >
                <div class="text-xs text-slate-400 font-semibold uppercase">Total Log Tercatat</div>
                <div class="text-2xl font-bold text-white mt-1">{{ metrics.totalLogs }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Semua entri audit</div>
            </div>

            <div
                @click="selectFilterType('INBOUND')"
                class="cursor-pointer bg-slate-950/70 border rounded-xl p-4 transition-all"
                :class="selectedType === 'INBOUND' ? 'border-emerald-500 bg-emerald-500/5 ring-1 ring-emerald-500/30' : 'border-slate-800 hover:border-slate-700'"
            >
                <div class="text-xs text-slate-400 font-semibold uppercase flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    Barang Masuk (Inbound)
                </div>
                <div class="text-2xl font-bold text-emerald-400 mt-1">{{ metrics.inboundCount }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Penerimaan & restock</div>
            </div>

            <div
                @click="selectFilterType('OUTBOUND')"
                class="cursor-pointer bg-slate-950/70 border rounded-xl p-4 transition-all"
                :class="selectedType === 'OUTBOUND' ? 'border-amber-500 bg-amber-500/5 ring-1 ring-amber-500/30' : 'border-slate-800 hover:border-slate-700'"
            >
                <div class="text-xs text-slate-400 font-semibold uppercase flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                    Barang Keluar (Outbound)
                </div>
                <div class="text-2xl font-bold text-amber-400 mt-1">{{ metrics.outboundCount }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Pengeluaran & pengiriman</div>
            </div>

            <div
                @click="selectFilterType('LOGIN')"
                class="cursor-pointer bg-slate-950/70 border rounded-xl p-4 transition-all"
                :class="selectedType === 'LOGIN' ? 'border-blue-500 bg-blue-500/5 ring-1 ring-blue-500/30' : 'border-slate-800 hover:border-slate-700'"
            >
                <div class="text-xs text-slate-400 font-semibold uppercase flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-blue-400"></span>
                    Sesi Login Pengguna
                </div>
                <div class="text-2xl font-bold text-blue-400 mt-1">{{ metrics.loginCount }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Akses terminal & sesi</div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-slate-950/80 border border-slate-800 rounded-xl p-4 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Search -->
            <div class="relative w-full md:max-w-md">
                <input
                    v-model="searchInput"
                    @keyup.enter="applyFilters"
                    type="text"
                    placeholder="Cari aktivitas, username, SKU, atau keterangan..."
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg pl-9 pr-20 py-2 text-sm text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-500"
                />
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <button
                    @click="applyFilters"
                    class="absolute right-1.5 top-1.5 px-3 py-1 bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300 rounded border border-slate-700"
                >
                    Cari
                </button>
            </div>

            <!-- Category Pills Filter -->
            <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto pb-1 md:pb-0">
                <button
                    v-for="cat in ['ALL', 'INBOUND', 'OUTBOUND', 'LOGIN', 'MAINTENANCE', 'LOGOUT']"
                    :key="cat"
                    @click="selectFilterType(cat)"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors whitespace-nowrap"
                    :class="selectedType === cat ? 'bg-blue-500 text-slate-950 font-bold' : 'bg-slate-900 text-slate-400 hover:bg-slate-800 hover:text-slate-200 border border-slate-800'"
                >
                    {{ cat }}
                </button>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="bg-slate-950/80 border border-slate-800 rounded-xl overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="bg-slate-900/90 text-xs uppercase font-semibold text-slate-400 border-b border-slate-800">
                        <tr>
                            <th class="px-5 py-3.5">Waktu & Tanggal</th>
                            <th class="px-5 py-3.5">Tipe Event</th>
                            <th class="px-5 py-3.5">User / Operator</th>
                            <th class="px-5 py-3.5">Aktivitas & Uraian Detail</th>
                            <th class="px-5 py-3.5 text-right">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80">
                        <tr
                            v-for="log in logs.data"
                            :key="log.id"
                            class="hover:bg-slate-900/40 transition-colors"
                        >
                            <!-- Time -->
                            <td class="px-5 py-4 whitespace-nowrap text-xs text-slate-400 font-mono">
                                {{ formatDate(log.created_at) }}
                            </td>

                            <!-- Type Badge -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-mono font-bold border"
                                    :class="{
                                        'bg-emerald-500/10 text-emerald-400 border-emerald-500/20': log.type === 'INBOUND',
                                        'bg-amber-500/10 text-amber-400 border-amber-500/20': log.type === 'OUTBOUND',
                                        'bg-blue-500/10 text-blue-400 border-blue-500/20': log.type === 'LOGIN',
                                        'bg-slate-500/10 text-slate-400 border-slate-500/20': log.type === 'LOGOUT',
                                        'bg-purple-500/10 text-purple-400 border-purple-500/20': log.type === 'MAINTENANCE',
                                    }"
                                >
                                    {{ log.type }}
                                </span>
                            </td>

                            <!-- User -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-6 rounded-full bg-slate-800 border border-slate-700 text-slate-300 flex items-center justify-center font-bold text-xs uppercase">
                                        {{ log.user_name ? log.user_name.charAt(0) : '?' }}
                                    </div>
                                    <span class="font-medium text-white">{{ log.user_name || 'System' }}</span>
                                </div>
                            </td>

                            <!-- Title & Description -->
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-200">{{ log.title }}</div>
                                <div v-if="log.description" class="text-xs text-slate-400 mt-0.5">
                                    {{ log.description }}
                                </div>
                            </td>

                            <!-- IP Address -->
                            <td class="px-5 py-4 whitespace-nowrap text-right font-mono text-xs text-slate-500">
                                {{ log.ip_address || '127.0.0.1' }}
                            </td>
                        </tr>

                        <tr v-if="logs.data.length === 0">
                            <td colspan="5" class="px-5 py-12 text-center text-slate-400">
                                Tidak ada log aktivitas yang cocok dengan filter.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div v-if="logs.links && logs.links.length > 3" class="p-4 border-t border-slate-800 bg-slate-900/40 flex items-center justify-between">
                <span class="text-xs text-slate-400">
                    Menampilkan {{ logs.from || 0 }} - {{ logs.to || 0 }} dari {{ logs.total }} total log
                </span>
                <div class="flex items-center gap-1">
                    <component
                        :is="link.url ? 'Link' : 'span'"
                        v-for="(link, i) in logs.links"
                        :key="i"
                        :href="link.url"
                        v-html="link.label"
                        class="px-2.5 py-1 rounded text-xs transition-colors"
                        :class="[
                            link.active ? 'bg-blue-500 text-slate-950 font-bold' : 'text-slate-400 hover:bg-slate-800',
                            !link.url ? 'opacity-40 cursor-not-allowed' : ''
                        ]"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
