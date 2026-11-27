<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    canLogin: { type: Boolean, default: false },
    canRegister: { type: Boolean, default: false },
});
</script>

<template>
    <header class="wh-header" role="banner">
        <div class="wh-header-inner">
            <!-- Brand Logo -->
            <a href="/" class="wh-brand" aria-label="Beranda WAREFIVE WMS">
                <span class="wh-brand-icon" aria-hidden="true">
                    <svg class="w-5 h-5 text-[#88C0D0]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 7.5L12 2.5L3 7.5V16.5L12 21.5L21 16.5V7.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 22V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 12L20.5 7.2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 12L3.5 7.2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M7.5 4.5L16.5 9.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div class="flex flex-col">
                    <span class="wh-brand-name">WAREFIVE<span class="text-[#88C0D0]">.</span></span>
                    <span class="wh-brand-sub">ENTERPRISE WMS</span>
                </div>
            </a>

            <!-- Facility Status Pill (Nordic Dim) -->
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#2E3440] border border-[#4C566A]/60 text-xs text-[#D8DEE9] shadow-xs">
                <span class="w-2 h-2 rounded-full bg-[#A3BE8C] animate-pulse"></span>
                <span>Fasilitas: <strong class="text-[#ECEFF4]">WH-KRWNG</strong> <span class="hidden sm:inline">Barat</span></span>
            </div>

            <!-- Desktop Active Actions -->
            <nav v-if="canLogin" class="hidden md:flex items-center gap-3" aria-label="Navigasi Desktop">
                <Link
                    v-if="$page.props.auth?.user"
                    :href="route('dashboard')"
                    class="wh-btn-primary"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    Buka Dashboard
                </Link>

                <template v-else>
                    <Link :href="route('login')" class="wh-nav-link">
                        Masuk Petugas
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="wh-btn-primary"
                    >
                        Daftar Akun
                    </Link>
                </template>
            </nav>
        </div>

        <!-- MOBILE FLOATING BOTTOM ACTION BAR (Nordic Dim) -->
        <nav
            v-if="canLogin"
            class="md:hidden fixed bottom-3 inset-x-4 z-50 bg-[#242933]/95 border border-[#3B4252] rounded-2xl backdrop-blur-xl p-2.5 flex items-center gap-2.5 shadow-2xl shadow-black/50"
            role="navigation"
            aria-label="Navigasi Aksi Mobile"
        >
            <Link
                v-if="$page.props.auth?.user"
                :href="route('dashboard')"
                class="flex-1 flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-[#5E81AC] hover:bg-[#81A1C1] text-[#ECEFF4] font-bold text-sm shadow-md active:scale-98 transition-transform"
            >
                <svg class="w-5 h-5 text-[#ECEFF4]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                Buka Dashboard
            </Link>

            <template v-else>
                <Link
                    :href="route('login')"
                    class="flex-1 flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-[#5E81AC] hover:bg-[#81A1C1] text-[#ECEFF4] font-bold text-sm shadow-md active:scale-98 transition-transform"
                >
                    <svg class="w-4 h-4 text-[#ECEFF4]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Masuk Petugas
                </Link>

                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="flex-1 flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-[#3B4252] border border-[#4C566A] text-[#ECEFF4] font-semibold text-sm active:scale-98 transition-transform"
                >
                    Daftar Akun
                </Link>
            </template>
        </nav>
    </header>
</template>

<style scoped>
.wh-header {
    position: sticky;
    top: 0;
    z-index: 50;
    background: rgba(36, 41, 51, 0.95);
    border-bottom: 1px solid #3B4252;
    backdrop-filter: blur(14px);
    transition: background-color 0.2s ease;
}

.wh-header-inner {
    max-width: 1240px;
    margin: 0 auto;
    padding: 0 1.5rem;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
}

.wh-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    color: #ECEFF4;
}

.wh-brand-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(136, 192, 208, 0.12);
    border: 1px solid rgba(136, 192, 208, 0.25);
}

.wh-brand-name {
    font-size: 1.25rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.1;
    color: #ECEFF4;
}

.wh-brand-sub {
    font-size: 0.68rem;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-weight: 700;
    color: #A5B1C2;
    letter-spacing: 0.08em;
}

.wh-nav-link {
    padding: 0.55rem 1.1rem;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 500;
    color: #D8DEE9;
    text-decoration: none;
    transition: color 0.15s ease, background-color 0.15s ease;
    min-height: 44px;
    display: inline-flex;
    align-items: center;
}

.wh-nav-link:hover {
    color: #ECEFF4;
    background: rgba(67, 76, 94, 0.6);
}

.wh-nav-link:focus-visible {
    outline: 2px solid #88C0D0;
    outline-offset: 2px;
}

.wh-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.55rem 1.25rem;
    background: #5E81AC;
    color: #ECEFF4;
    font-size: 0.9rem;
    font-weight: 700;
    border-radius: 8px;
    text-decoration: none;
    transition: background-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease;
    min-height: 44px;
}

.wh-btn-primary:hover {
    background: #81A1C1;
    box-shadow: 0 4px 16px rgba(94, 129, 172, 0.3);
    transform: translateY(-1px);
}

.wh-btn-primary:focus-visible {
    outline: 2px solid #88C0D0;
    outline-offset: 2px;
}
</style>