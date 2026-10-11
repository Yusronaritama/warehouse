<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-slate-950 p-6 text-slate-100 selection:bg-amber-500 selection:text-white"
    >
        <div class="mb-4">
            <Link href="/" class="flex flex-col items-center gap-1">
                <ApplicationLogo />
                <span class="text-xs text-slate-500 tracking-wider uppercase font-semibold">Authorized Warehouse Terminal</span>
            </Link>
        </div>

        <div
            class="w-full overflow-hidden rounded-xl border border-slate-800 bg-slate-900/90 p-6 shadow-2xl backdrop-blur-md sm:max-w-md"
        >
            <slot />
        </div>

        <div class="mt-6 text-center text-xs text-slate-500">
            Sistem Keamanan Logistik &bull; Terhubung ke Local Host Laragon
        </div>
    </div>
</template>
