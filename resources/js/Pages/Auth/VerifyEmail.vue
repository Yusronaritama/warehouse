<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Verifikasi Email Akun Petugas" />

        <div class="mb-5">
            <div class="flex items-center gap-2 mb-2">
                <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </span>
                <h2 class="text-lg font-bold text-white tracking-tight">Verifikasi Email Petugas</h2>
            </div>
            <p class="text-xs text-slate-400 leading-relaxed">
                Terima kasih telah mendaftar di Terminal Warehouse. Sebelum memulai aktivitas logistik, silakan verifikasi alamat email Anda melalui tautan yang baru saja kami kirimkan ke kotak masuk Anda.
            </p>
        </div>

        <div
            class="mb-4 text-xs font-medium text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 p-3 rounded-lg flex items-start gap-2"
            v-if="verificationLinkSent"
        >
            <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Tautan verifikasi email baru telah berhasil dikirimkan ke alamat email Anda.</span>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <PrimaryButton
                    class="w-full justify-center !bg-amber-500 hover:!bg-amber-400 !text-slate-950 !font-bold !py-2.5 !rounded-lg !border-0 shadow-lg shadow-amber-500/20 text-xs"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Kirim Ulang Tautan Verifikasi Email
                </PrimaryButton>
            </div>

            <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                <Link
                    :href="route('password.request')"
                    class="hover:text-amber-400 transition-colors"
                >
                    Lupa Password?
                </Link>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-rose-400 hover:text-rose-300 transition-colors font-medium hover:underline"
                >
                    Keluar / Logout
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
