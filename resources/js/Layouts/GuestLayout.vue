<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-[#2E3440] p-6 pb-24 sm:pb-6 text-[#ECEFF4] selection:bg-[#5E81AC] selection:text-[#ECEFF4]"
    >
        <div class="mb-5 guest-logo-enter">
            <Link href="/" class="flex flex-col items-center gap-1.5">
                <ApplicationLogo />
                <span class="text-xs text-[#A5B1C2] tracking-wider uppercase font-semibold">Authorized Warehouse Terminal</span>
            </Link>
        </div>

        <div
            class="w-full overflow-hidden rounded-2xl border border-[#4C566A]/60 bg-[#3B4252] p-7 shadow-2xl backdrop-blur-md sm:max-w-md guest-card-enter"
        >
            <slot />
        </div>

        <div class="mt-6 text-center text-xs text-[#A5B1C2] mb-6 sm:mb-0 space-y-1">
            <div>Sistem Manajemen Pergudangan WAREFIVE &bull; Fasilitas WH-KRWNG Barat</div>
            <div>
                &copy; {{ new Date().getFullYear() }} <strong class="text-[#ECEFF4]">Hols</strong> &times; <strong class="text-[#88C0D0]">WAREFIVE</strong>
                &bull; Made by <span class="text-[#D8DEE9] font-medium">Creactivities</span>
            </div>
        </div>

        <!-- Mobile Bottom Nav for Guest Pages (Nordic Dim) -->
        <nav
            class="sm:hidden fixed bottom-3 inset-x-4 z-30 bg-[#242933]/95 border border-[#3B4252] rounded-2xl backdrop-blur-xl px-4 py-3 flex items-center justify-between shadow-2xl shadow-black/50"
            aria-label="Navigasi Bawah"
        >
            <Link
                href="/"
                class="inline-flex items-center gap-2 text-xs font-semibold text-[#ECEFF4] hover:text-white py-2 px-3.5 rounded-xl bg-[#3B4252] border border-[#4C566A] hover:bg-[#434C5E] transition-colors"
            >
                <svg class="w-4 h-4 text-[#88C0D0]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Beranda
            </Link>

            <span class="text-[11px] text-[#A5B1C2] font-mono">
                WH-KRWNG Auth
            </span>
        </nav>
    </div>
</template>

<style scoped>
@keyframes guestLogoEnter {
    0% {
        opacity: 0;
        transform: translateY(-12px) scale(0.96);
    }
    100% {
        opacity: 1;
        transform: none;
    }
}

@keyframes guestCardEnter {
    0% {
        opacity: 0;
        transform: translateY(20px) scale(0.975);
        filter: blur(6px);
    }
    65% {
        opacity: 0.94;
        transform: translateY(-2px) scale(1.003);
        filter: blur(1px);
    }
    100% {
        opacity: 1;
        transform: none;
        filter: none;
    }
}

.guest-logo-enter {
    animation: guestLogoEnter 0.42s cubic-bezier(0.22, 1, 0.36, 1);
}

.guest-card-enter {
    animation: guestCardEnter 0.48s cubic-bezier(0.22, 1, 0.36, 1);
}

@media (prefers-reduced-motion: reduce) {
    .guest-logo-enter,
    .guest-card-enter {
        animation: none;
    }
}
</style>