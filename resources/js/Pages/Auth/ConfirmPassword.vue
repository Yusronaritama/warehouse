<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Konfirmasi Password Keamanan" />

        <div class="mb-5">
            <h2 class="text-lg font-bold text-[#ECEFF4] tracking-tight">Konfirmasi Akses Aman</h2>
            <p class="text-xs text-[#A5B1C2] mt-1 leading-relaxed">
                Ini adalah area sensitif sistem pergudangan. Silakan konfirmasi kata sandi Anda sebelum melanjutkan.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="password" value="Kata Sandi" class="!text-[#D8DEE9] !font-semibold text-xs" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1.5 block w-full !bg-[#2E3440] !border-[#4C566A] !text-[#ECEFF4] placeholder-[#8C96A9] focus:!border-[#88C0D0] focus:!ring-[#88C0D0]"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                    placeholder="Masukkan password Anda"
                />
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <div class="pt-2 flex justify-end">
                <PrimaryButton
                    class="!bg-[#5E81AC] hover:!bg-[#81A1C1] !text-[#ECEFF4] !font-bold !py-2 !px-5 !rounded-lg !border-0 shadow-lg shadow-[#5E81AC]/25"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Konfirmasi Password
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>