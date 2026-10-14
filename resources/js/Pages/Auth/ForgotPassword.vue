<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Lupa Kata Sandi & Verifikasi Email" />

        <div class="mb-5">
            <div class="flex items-center gap-2 mb-2">
                <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </span>
                <h2 class="text-lg font-bold text-white tracking-tight">Lupa Kata Sandi Akun</h2>
            </div>
            <p class="text-xs text-slate-400 leading-relaxed">
                Ketikkan email petugas yang terdaftar di sistem. Kami akan mengirimkan tautan verifikasi email beserta kode token untuk mengatur ulang kata sandi Anda.
            </p>
        </div>

        <div
            v-if="status"
            class="mb-4 text-xs font-medium text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 p-3 rounded-lg flex items-start gap-2"
        >
            <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ status }}</span>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Kolom Verifikasi Alamat Email -->
            <div>
                <InputLabel
                    for="email"
                    value="Kolom Verifikasi Email Petugas"
                    class="!text-slate-300 !font-semibold text-xs"
                />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1.5 block w-full !bg-slate-950 !border-slate-700 !text-white focus:!border-amber-500 focus:!ring-amber-500 font-mono text-sm"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="cth: petugas@warehouse.com"
                />

                <InputError class="mt-1.5" :message="form.errors.email" />
                <span class="text-[11px] text-slate-500 mt-1 block">
                    Pastikan email sesuai dengan data user gudang Anda.
                </span>
            </div>

            <div class="pt-1">
                <PrimaryButton
                    class="w-full justify-center !bg-amber-500 hover:!bg-amber-400 !text-slate-950 !font-bold !py-2.5 !rounded-lg !border-0 shadow-lg shadow-amber-500/20 text-xs"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Kirim Link Verifikasi & Reset Password
                </PrimaryButton>
            </div>
        </form>

        <!-- Bagian Navigasi ke Halaman Reset Password Langsung -->
        <div class="mt-5 pt-4 border-t border-slate-800/80 space-y-3">
            <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-lg">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-semibold text-slate-300">Sudah Punya Token Reset?</span>
                    <span class="text-[10px] bg-amber-500/10 text-amber-400 border border-amber-500/20 px-1.5 py-0.5 rounded font-mono">Langkah 2</span>
                </div>
                <p class="text-[11px] text-slate-400 mb-2.5">
                    Jika Anda sudah menerima kode / token reset di inbox email, lanjutkan langsung untuk memasukkan password baru.
                </p>
                <Link
                    :href="route('password.reset', { token: 'token', email: form.email || undefined })"
                    class="w-full inline-flex items-center justify-center px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-amber-300 text-xs font-semibold border border-slate-700 hover:border-amber-500/40 transition-colors gap-1.5"
                >
                    <span>Masuk ke Halaman Reset Password</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </Link>
            </div>

            <!-- Opsi Verifikasi Email Akun & Kembali ke Login -->
            <div class="flex items-center justify-between text-xs pt-1 text-slate-400">
                <Link
                    :href="route('login')"
                    class="hover:text-amber-400 transition-colors flex items-center gap-1"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Login
                </Link>

                <Link
                    :href="route('verification.notice')"
                    class="text-slate-400 hover:text-amber-400 transition-colors underline"
                >
                    Verifikasi Akun Email
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>
