<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Registrasi Petugas Gudang" />

        <div class="mb-5">
            <h2 class="text-lg font-bold text-[#ECEFF4] tracking-tight">Daftar Akun Operator</h2>
            <p class="text-xs text-[#A5B1C2] mt-1">Buat akun untuk mengakses kontrol inventaris dan aset.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Username -->
            <div>
                <InputLabel for="name" value="Username Petugas" class="!text-[#D8DEE9] !font-semibold text-xs" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1.5 block w-full !bg-[#2E3440] !border-[#4C566A] !text-[#ECEFF4] placeholder-[#8C96A9] focus:!border-[#88C0D0] focus:!ring-[#88C0D0]"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Masukkan username"
                />

                <InputError class="mt-1.5" :message="form.errors.name" />
            </div>

            <!-- Password -->
            <div>
                <InputLabel for="password" value="Password" class="!text-[#D8DEE9] !font-semibold text-xs" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1.5 block w-full !bg-[#2E3440] !border-[#4C566A] !text-[#ECEFF4] placeholder-[#8C96A9] focus:!border-[#88C0D0] focus:!ring-[#88C0D0]"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                    placeholder="Minimal 8 karakter"
                />

                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <!-- Confirm Password -->
            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Konfirmasi Password"
                    class="!text-[#D8DEE9] !font-semibold text-xs"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1.5 block w-full !bg-[#2E3440] !border-[#4C566A] !text-[#ECEFF4] placeholder-[#8C96A9] focus:!border-[#88C0D0] focus:!ring-[#88C0D0]"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Ulangi password"
                />

                <InputError
                    class="mt-1.5"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <!-- Submit & Links -->
            <div class="pt-2 flex items-center justify-between">
                <Link
                    :href="route('login')"
                    class="text-xs text-[#A5B1C2] hover:text-[#88C0D0] transition-colors"
                >
                    Sudah punya akun? Masuk
                </Link>

                <PrimaryButton
                    class="!bg-[#5E81AC] hover:!bg-[#81A1C1] !text-[#ECEFF4] !font-bold !py-2 !px-5 !rounded-lg !border-0 shadow-lg shadow-[#5E81AC]/25"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Daftar Akun
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>