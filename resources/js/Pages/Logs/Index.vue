<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

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
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#ECEFF4] flex items-center gap-2.5">
                        <span class="p-2 bg-[#434C5E] border border-[#4C566A] rounded-lg text-[#88C0D0]">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </span>
                        Log Aktivitas & Audit Trail Seluruh Sistem
                    </h1>
                    <p class="text-xs sm:text-sm text-[#A5B1C2] mt-1">
                        Pencatatan riwayat kronologis lengkap barang masuk, barang keluar, relokasi rak, hingga autentikasi login pengguna.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-mono font-semibold bg-[#A3BE8C]/15 border border-[#A3BE8C]/30 text-[#A3BE8C] shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-[#A3BE8C] animate-pulse"></span>
                        Auto-Reset: Retensi 3 Hari Aktif
                    </span>
                </div>
            </div>
        </template>

        <!-- KPI Metrics Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div
                @click="selectFilterType('ALL')"
                class="cursor-pointer bg-[#3B4252] border rounded-xl p-4 transition-all"
                :class="selectedType === 'ALL' ? 'border-[#88C0D0] bg-[#88C0D0]/10 ring-1 ring-[#88C0D0]/30' : 'border-[#4C566A]/60 hover:border-[#88C0D0]/50'"
            >
                <div class="text-xs text-[#A5B1C2] font-semibold uppercase">Total Log Tercatat</div>
                <div class="text-2xl font-bold font-mono text-[#ECEFF4] mt-1">{{ metrics.totalLogs }}</div>
                <div class="text-[11px] text-[#A5B1C2] mt-0.5">Semua entri audit</div>
            </div>

            <div
                @click="selectFilterType('INBOUND')"
                class="cursor-pointer bg-[#3B4252] border rounded-xl p-4 transition-all"
                :class="selectedType === 'INBOUND' ? 'border-[#A3BE8C] bg-[#A3BE8C]/10 ring-1 ring-[#A3BE8C]/30' : 'border-[#4C566A]/60 hover:border-[#A3BE8C]/50'"
            >
                <div class="text-xs text-[#A5B1C2] font-semibold uppercase flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-[#A3BE8C]"></span>
                    Barang Masuk (Inbound)
                </div>
                <div class="text-2xl font-bold font-mono text-[#A3BE8C] mt-1">{{ metrics.inboundCount }}</div>
                <div class="text-[11px] text-[#A5B1C2] mt-0.5">Penerimaan & restock</div>
            </div>

            <div
                @click="selectFilterType('OUTBOUND')"
                class="cursor-pointer bg-[#3B4252] border rounded-xl p-4 transition-all"
                :class="selectedType === 'OUTBOUND' ? 'border-[#EBCB8B] bg-[#EBCB8B]/10 ring-1 ring-[#EBCB8B]/30' : 'border-[#4C566A]/60 hover:border-[#EBCB8B]/50'"
            >
                <div class="text-xs text-[#A5B1C2] font-semibold uppercase flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-[#EBCB8B]"></span>
                    Barang Keluar (Outbound)
                </div>
                <div class="text-2xl font-bold font-mono text-[#EBCB8B] mt-1">{{ metrics.outboundCount }}</div>
                <div class="text-[11px] text-[#A5B1C2] mt-0.5">Pengeluaran & pengiriman</div>
            </div>

            <div
                @click="selectFilterType('LOGIN')"
                class="cursor-pointer bg-[#3B4252] border rounded-xl p-4 transition-all"
                :class="selectedType === 'LOGIN' ? 'border-[#81A1C1] bg-[#81A1C1]/10 ring-1 ring-[#81A1C1]/30' : 'border-[#4C566A]/60 hover:border-[#81A1C1]/50'"
            >
                <div class="text-xs text-[#A5B1C2] font-semibold uppercase flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-[#81A1C1]"></span>
                    Sesi Login Pengguna
                </div>
                <div class="text-2xl font-bold font-mono text-[#81A1C1] mt-1">{{ metrics.loginCount }}</div>
                <div class="text-[11px] text-[#A5B1C2] mt-0.5">Akses terminal & sesi</div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-[#3B4252] border border-[#4C566A]/60 rounded-xl p-4 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Search -->
            <div class="relative w-full md:max-w-md">
                <input
                    v-model="searchInput"
                    @keyup.enter="applyFilters"
                    type="text"
                    placeholder="Cari aktivitas, username, SKU, atau keterangan..."
                    class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg pl-9 pr-20 py-2 text-sm text-[#ECEFF4] placeholder-[#8C96A9] focus:outline-none focus:border-[#88C0D0]"
                />
                <svg class="w-4 h-4 text-[#8C96A9] absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <button
                    @click="applyFilters"
                    class="absolute right-1.5 top-1.5 px-3 py-1 bg-[#434C5E] hover:bg-[#4C566A] text-xs font-semibold text-[#ECEFF4] rounded border border-[#4C566A] transition-colors"
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
                    :class="selectedType === cat ? 'bg-[#5E81AC] text-[#ECEFF4] font-bold shadow-xs' : 'bg-[#2E3440] text-[#A5B1C2] hover:bg-[#434C5E] hover:text-[#ECEFF4] border border-[#4C566A]/60'"
                >
                    {{ cat }}
                </button>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="bg-[#3B4252] border border-[#4C566A]/60 rounded-xl overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-[#D8DEE9]">
                    <thead class="bg-[#434C5E]/60 text-xs uppercase font-semibold text-[#D8DEE9] border-b border-[#4C566A]/60">
                        <tr>
                            <th class="px-5 py-3.5">Waktu & Tanggal</th>
                            <th class="px-5 py-3.5">Tipe Event</th>
                            <th class="px-5 py-3.5">User / Operator</th>
                            <th class="px-5 py-3.5">Aktivitas & Uraian Detail</th>
                            <th class="px-5 py-3.5 text-right">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#4C566A]/40">
                        <tr
                            v-for="log in logs.data"
                            :key="log.id"
                            class="hover:bg-[#434C5E]/50 transition-colors"
                        >
                            <!-- Time -->
                            <td class="px-5 py-4 whitespace-nowrap text-xs text-[#A5B1C2] font-mono">
                                {{ formatDate(log.created_at) }}
                            </td>

                            <!-- Type Badge -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-mono font-bold border"
                                    :class="{
                                        'bg-[#A3BE8C]/15 text-[#A3BE8C] border-[#A3BE8C]/30': log.type === 'INBOUND',
                                        'bg-[#EBCB8B]/15 text-[#EBCB8B] border-[#EBCB8B]/30': log.type === 'OUTBOUND',
                                        'bg-[#88C0D0]/15 text-[#88C0D0] border-[#88C0D0]/30': log.type === 'LOGIN',
                                        'bg-[#434C5E] text-[#D8DEE9] border-[#4C566A]/60': log.type === 'LOGOUT',
                                        'bg-[#B48EAD]/15 text-[#B48EAD] border-[#B48EAD]/30': log.type === 'MAINTENANCE',
                                    }"
                                >
                                    {{ log.type }}
                                </span>
                            </td>

                            <!-- User -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-6 rounded-full bg-[#2E3440] border border-[#4C566A] text-[#D8DEE9] flex items-center justify-center font-bold text-xs uppercase">
                                        {{ log.user_name ? log.user_name.charAt(0) : '?' }}
                                    </div>
                                    <span class="font-medium text-[#ECEFF4]">{{ log.user_name || 'System' }}</span>
                                </div>
                            </td>

                            <!-- Title & Description -->
                            <td class="px-5 py-4">
                                <div class="font-semibold text-[#ECEFF4]">{{ log.title }}</div>
                                <div v-if="log.description" class="text-xs text-[#A5B1C2] mt-0.5">
                                    {{ log.description }}
                                </div>
                            </td>

                            <!-- IP Address -->
                            <td class="px-5 py-4 whitespace-nowrap text-right font-mono text-xs text-[#8C96A9]">
                                {{ log.ip_address || '127.0.0.1' }}
                            </td>
                        </tr>

                        <tr v-if="logs.data.length === 0">
                            <td colspan="5" class="px-5 py-12 text-center text-[#A5B1C2]">
                                Tidak ada log aktivitas yang cocok dengan filter.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div v-if="logs.links && logs.links.length > 3" class="p-4 border-t border-[#4C566A]/60 bg-[#2E3440]/60 flex items-center justify-between">
                <span class="text-xs text-[#A5B1C2]">
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
                            link.active ? 'bg-[#5E81AC] text-[#ECEFF4] font-bold' : 'text-[#A5B1C2] hover:bg-[#434C5E]',
                            !link.url ? 'opacity-40 cursor-not-allowed' : ''
                        ]"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
