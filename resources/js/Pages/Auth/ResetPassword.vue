<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        default: '',
    },
    token: {
        type: String,
        default: '',
    },
});

const form = useForm({
    token: props.token || '',
    email: props.email || '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Atur Ulang Kata Sandi & Verifikasi Email" />

        <div class="mb-5">
            <div class="flex items-center gap-2 mb-2">
                <span class="p-1.5 rounded-lg bg-[#88C0D0]/15 text-[#88C0D0] border border-[#88C0D0]/30">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </span>
                <h2 class="text-lg font-bold text-[#ECEFF4] tracking-tight">Atur Ulang Kata Sandi</h2>
            </div>
            <p class="text-xs text-[#A5B1C2] leading-relaxed">
                Verifikasi alamat email Anda dan buat kata sandi baru untuk mengamankan akses terminal gudang.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Kolom Verifikasi Email -->
            <div>
                <InputLabel
                    for="email"
                    value="Kolom Verifikasi Email Petugas"
                    class="!text-[#D8DEE9] !font-semibold text-xs"
                />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1.5 block w-full !bg-[#2E3440] !border-[#4C566A] !text-[#ECEFF4] placeholder-[#8C96A9] focus:!border-[#88C0D0] focus:!ring-[#88C0D0] font-mono text-sm"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="nama@warehouse.com"
                />

                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <!-- Kolom Token / Kode Verifikasi -->
            <div>
                <InputLabel
                    for="token"
                    value="Token / Kode Verifikasi Reset"
                    class="!text-[#D8DEE9] !font-semibold text-xs"
                />

                <TextInput
                    id="token"
                    type="text"
                    class="mt-1.5 block w-full !bg-[#2E3440] !border-[#4C566A] !text-[#ECEFF4] placeholder-[#8C96A9] focus:!border-[#88C0D0] focus:!ring-[#88C0D0] font-mono text-sm"
                    v-model="form.token"
                    required
                    placeholder="Masukkan token verifikasi dari email"
                />

                <InputError class="mt-1.5" :message="form.errors.token" />
                <span class="text-[11px] text-[#A5B1C2] mt-1 block">
                    Diisi otomatis dari link email atau diketik secara manual.
                </span>
            </div>

            <!-- Password Baru -->
            <div>
                <InputLabel
                    for="password"
                    value="Kata Sandi Baru"
                    class="!text-[#D8DEE9] !font-semibold text-xs"
                />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1.5 block w-full !bg-[#2E3440] !border-[#4C566A] !text-[#ECEFF4] placeholder-[#8C96A9] focus:!border-[#88C0D0] focus:!ring-[#88C0D0] text-sm"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                    placeholder="Minimal 8 karakter kombinasi aman"
                />

                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <!-- Konfirmasi Password Baru -->
            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Konfirmasi Kata Sandi Baru"
                    class="!text-[#D8DEE9] !font-semibold text-xs"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1.5 block w-full !bg-[#2E3440] !border-[#4C566A] !text-[#ECEFF4] placeholder-[#8C96A9] focus:!border-[#88C0D0] focus:!ring-[#88C0D0] text-sm"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Ketik ulang kata sandi baru"
                />

                <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <PrimaryButton
                    class="w-full justify-center !bg-[#5E81AC] hover:!bg-[#81A1C1] !text-[#ECEFF4] !font-bold !py-2.5 !rounded-lg !border-0 shadow-lg shadow-[#5E81AC]/25 text-xs"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Simpan Kata Sandi Baru
                </PrimaryButton>
            </div>
        </form>

        <!-- Footer Navigasi -->
        <div class="mt-5 pt-4 border-t border-[#4C566A]/60 flex items-center justify-between text-xs text-[#A5B1C2]">
            <Link
                :href="route('password.request')"
                class="hover:text-[#88C0D0] transition-colors flex items-center gap-1"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Minta Token Baru
            </Link>

            <Link
                :href="route('login')"
                class="hover:text-[#88C0D0] transition-colors"
            >
                Masuk ke Login &rarr;
            </Link>
        </div>
    </GuestLayout>
</template>