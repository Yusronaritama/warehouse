<script setup>
import { ref, watch } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import LoginOpeningOverlay from '@/Components/Warehouse/LoginOpeningOverlay.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const showingAccountSheet = ref(false);
const showLoginOpening = ref(Boolean(page.props.flash?.login_opening));

watch(
    () => page.props.flash?.login_opening,
    (val) => {
        if (val) {
            showLoginOpening.value = true;
        }
    }
);
</script>

<template>
    <div class="min-h-screen bg-[#2E3440] text-[#ECEFF4] flex flex-col font-sans selection:bg-[#5E81AC] selection:text-white">
        <!-- Opening Screen Setelah Login Berhasil -->
        <LoginOpeningOverlay
            v-if="showLoginOpening"
            :show="showLoginOpening"
            :user-name="$page.props.auth?.user?.name || 'Operator'"
            :facility-name="$page.props.facility?.name || 'WH-KRWNG Barat'"
            @done="showLoginOpening = false"
        />
        <!-- Top Warehouse Navigation Bar (Nordic Shell: bg-[#242933] border-b border-[#3B4252] text-[#D8DEE9]) -->
        <header class="border-b border-[#3B4252] bg-[#242933] sticky top-0 z-40 backdrop-blur-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Brand & Facility Selector -->
                    <div class="flex items-center gap-4 sm:gap-6">
                        <Link :href="route('dashboard')" class="flex items-center gap-2">
                            <ApplicationLogo />
                        </Link>

                        <div class="flex items-center gap-2 pl-3 sm:pl-4 border-l border-[#3B4252] text-xs">
                            <span class="inline-flex items-center gap-1.5 px-2 sm:px-2.5 py-1 rounded-full bg-[#3B4252] text-[#88C0D0] border border-[#4C566A]/60 font-medium text-[11px] sm:text-xs">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#88C0D0] animate-pulse"></span>
                                {{ $page.props.facility?.name || 'WH-KRWNG Barat' }}
                            </span>
                            <span class="hidden sm:inline text-[#A5B1C2]">
                                Kapasitas Rak:
                                <span class="font-mono font-semibold" :class="($page.props.facility?.actualRackCapacity || 0) > 0 ? 'text-[#88C0D0]' : 'text-[#D8DEE9]'">
                                    {{ $page.props.facility?.actualRackCapacity ?? 0 }}%
                                </span>
                            </span>
                        </div>
                    </div>

                    <!-- Main Navigation Links (Desktop: Menu aktif bg-[#3B4252] text-[#ECEFF4] icon text-[#88C0D0]) -->
                    <nav class="hidden md:flex items-center gap-1.5" aria-label="Navigasi Desktop">
                        <Link
                            :href="route('dashboard')"
                            class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
                            :class="route().current('dashboard') ? 'bg-[#3B4252] text-[#ECEFF4] border border-[#4C566A]/60 shadow-xs' : 'text-[#D8DEE9] hover:text-[#ECEFF4] hover:bg-[#3B4252]/60'"
                        >
                            <svg class="w-4 h-4 text-[#88C0D0]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            Inventory Overview
                        </Link>

                        <Link
                            :href="route('barcodes.index')"
                            class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
                            :class="route().current('barcodes.index') ? 'bg-[#3B4252] text-[#ECEFF4] border border-[#4C566A]/60 shadow-xs' : 'text-[#D8DEE9] hover:text-[#ECEFF4] hover:bg-[#3B4252]/60'"
                        >
                            <svg class="w-4 h-4 text-[#88C0D0]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            Barcode & QR Rak
                        </Link>

                        <Link
                            :href="route('logs.index')"
                            class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
                            :class="route().current('logs.index') ? 'bg-[#3B4252] text-[#ECEFF4] border border-[#4C566A]/60 shadow-xs' : 'text-[#D8DEE9] hover:text-[#ECEFF4] hover:bg-[#3B4252]/60'"
                        >
                            <svg class="w-4 h-4 text-[#88C0D0]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Log Aktivitas Sistem
                        </Link>
                    </nav>

                    <!-- Desktop User Menu -->
                    <div class="hidden sm:flex items-center gap-3">
                        <div class="relative">
                            <Dropdown align="right" width="48" content-classes="py-1 bg-[#242933] border border-[#3B4252]">
                                <template #trigger>
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2.5 px-3 py-1.5 rounded-lg border border-[#4C566A]/60 bg-[#3B4252] text-sm font-medium text-[#ECEFF4] hover:bg-[#434C5E] focus:outline-none transition-colors"
                                    >
                                        <div class="h-6 w-6 rounded-full bg-[#5E81AC]/20 text-[#88C0D0] flex items-center justify-center font-bold text-xs uppercase border border-[#88C0D0]/30">
                                            {{ $page.props.auth.user.name.charAt(0) }}
                                        </div>
                                        <span>{{ $page.props.auth.user.name }}</span>
                                        <svg class="h-4 w-4 text-[#A5B1C2]" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </template>

                                <template #content>
                                    <div class="px-4 py-2 border-b border-[#3B4252] text-xs text-[#D8DEE9]">
                                        Role: <span class="text-[#88C0D0] font-semibold">Warehouse Supervisor</span>
                                    </div>
                                    <DropdownLink :href="route('profile.edit')" class="!text-[#ECEFF4] hover:!bg-[#3B4252]">
                                        Profile Settings
                                    </DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button" class="!text-[#ECEFF4] hover:!bg-[#3B4252]">
                                        Log Out
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <!-- Mobile Top Quick Avatar (Opens Account Sheet) -->
                    <div class="flex items-center sm:hidden">
                        <button
                            type="button"
                            @click="showingAccountSheet = !showingAccountSheet"
                            class="flex items-center gap-2 p-1.5 rounded-lg bg-[#3B4252] border border-[#4C566A]/60 text-[#ECEFF4]"
                            aria-label="Menu Akun Petugas"
                        >
                            <div class="h-7 w-7 rounded-full bg-[#5E81AC]/20 text-[#88C0D0] flex items-center justify-center font-bold text-xs uppercase border border-[#88C0D0]/30">
                                {{ $page.props.auth.user.name.charAt(0) }}
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Optional Header Slot (Topbar Nordic: bg-[#2E3440]/95 border-b border-[#434C5E]) -->
        <header v-if="$slots.header" class="border-b border-[#434C5E] bg-[#2E3440]/95 backdrop-blur-md">
            <div class="max-w-7xl mx-auto py-3.5 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Main Content Area with Bottom Padding for Mobile Nav (bg-[#2E3440]) -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-28 sm:pb-6 page-content-enter">
            <slot />
        </main>

        <!-- System Footer (Nordic Dim) -->
        <footer class="border-t border-[#3B4252] py-4 bg-[#242933] text-center text-xs text-[#A5B1C2] mb-16 sm:mb-0">
            <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-x-2 gap-y-1">
                    <span>&copy; {{ new Date().getFullYear() }} <strong class="text-[#ECEFF4]">Hols</strong> &times; <strong class="text-[#88C0D0]">WAREFIVE</strong>. All rights reserved.</span>
                    <span class="text-[#616E88]">&bull;</span>
                    <span>Made by <span class="text-[#D8DEE9] font-medium">Creactivities</span></span>
                </div>
                <div class="flex items-center gap-4">
                    <span>Database: Connected</span>
                    <span>Scanner: Ready (HID/USB)</span>
                    <span class="text-[#88C0D0] font-mono font-medium">NODE-01</span>
                </div>
            </div>
        </footer>

        <!-- MOBILE NATIVE BOTTOM NAVIGATION BAR (Nordic Dark Floating Bar: bg-[#242933]/95 border-[#3B4252]) -->
        <nav
            class="sm:hidden fixed bottom-3 inset-x-4 z-30 bg-[#242933]/95 border border-[#3B4252] rounded-2xl backdrop-blur-xl px-3 py-2 flex items-center justify-around shadow-2xl shadow-black/50"
            role="navigation"
            aria-label="Navigasi Aplikasi Mobile"
        >
            <!-- 1. Dashboard -->
            <Link
                :href="route('dashboard')"
                class="flex flex-col items-center justify-center min-w-[56px] min-h-[44px] py-1 px-1.5 rounded-xl transition-colors"
                :class="route().current('dashboard') ? 'text-[#88C0D0] font-semibold' : 'text-[#A5B1C2] hover:text-[#ECEFF4]'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <span class="text-[10px]">Dashboard</span>
            </Link>

            <!-- 2. Barcodes -->
            <Link
                :href="route('barcodes.index')"
                class="flex flex-col items-center justify-center min-w-[56px] min-h-[44px] py-1 px-1.5 rounded-xl transition-colors"
                :class="route().current('barcodes.index') ? 'text-[#88C0D0] font-semibold' : 'text-[#A5B1C2] hover:text-[#ECEFF4]'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span class="text-[10px]">Scan Rak</span>
            </Link>

            <!-- 3. Logs -->
            <Link
                :href="route('logs.index')"
                class="flex flex-col items-center justify-center min-w-[56px] min-h-[44px] py-1 px-1.5 rounded-xl transition-colors"
                :class="route().current('logs.index') ? 'text-[#88C0D0] font-semibold' : 'text-[#A5B1C2] hover:text-[#ECEFF4]'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="text-[10px]">Aktivitas</span>
            </Link>
        </nav>

        <!-- MOBILE ACCOUNT SHEET (Muncul saat avatar kanan atas diklik di mode mobile) -->
        <div
            v-if="showingAccountSheet"
            class="sm:hidden fixed inset-0 z-[80] flex items-end justify-center bg-[#242933]/75 backdrop-blur-xs"
            @click.self="showingAccountSheet = false"
        >
            <div class="w-full max-w-md bg-[#2E3440] border-t border-[#4C566A]/60 rounded-t-2xl p-5 pb-[max(1.5rem,env(safe-area-inset-bottom))] shadow-2xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#4C566A]/60">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-[#5E81AC]/25 text-[#88C0D0] flex items-center justify-center font-bold text-sm uppercase border border-[#88C0D0]/40">
                            {{ $page.props.auth.user.name.charAt(0) }}
                        </div>
                        <div>
                            <div class="text-sm font-bold text-[#ECEFF4]">{{ $page.props.auth.user.name }}</div>
                            <div class="text-xs text-[#A5B1C2]">{{ $page.props.auth.user.email }}</div>
                            <div class="text-[11px] text-[#88C0D0] font-medium mt-0.5">Role: Warehouse Supervisor</div>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="showingAccountSheet = false"
                        class="p-2 rounded-lg text-[#A5B1C2] hover:text-[#ECEFF4] hover:bg-[#3B4252]"
                        aria-label="Tutup Menu Akun"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-2">
                    <Link
                        :href="route('profile.edit')"
                        @click="showingAccountSheet = false"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl bg-[#3B4252] border border-[#4C566A]/60 text-sm font-medium text-[#ECEFF4] hover:bg-[#434C5E] transition-colors"
                    >
                        <svg class="w-4 h-4 text-[#88C0D0]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profile Settings
                    </Link>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        @click="showingAccountSheet = false"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl bg-[#BF616A]/15 border border-[#BF616A]/40 text-sm font-semibold text-[#ECEFF4] hover:bg-[#BF616A]/25 transition-colors text-left"
                    >
                        <svg class="w-4 h-4 text-[#BF616A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Log Out
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes pageContentEnter {
    0% {
        opacity: 0;
        transform: translateY(18px) scale(0.975);
        filter: blur(6px);
    }
    60% {
        opacity: 0.92;
        transform: translateY(-2px) scale(1.003);
        filter: blur(1px);
    }
    100% {
        opacity: 1;
        transform: none;
        filter: none;
    }
}

.page-content-enter {
    animation: pageContentEnter 0.48s cubic-bezier(0.22, 1, 0.36, 1);
}

@media (prefers-reduced-motion: reduce) {
    .page-content-enter {
        animation: none;
    }
}
</style>
