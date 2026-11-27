<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

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

                <div class="relative mt-1.5">
                    <TextInput
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        class="block w-full pr-10 !bg-[#2E3440] !border-[#4C566A] !text-[#ECEFF4] placeholder-[#8C96A9] focus:!border-[#88C0D0] focus:!ring-[#88C0D0]"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-[#A5B1C2] hover:text-[#ECEFF4] transition-colors focus:outline-none"
                        :aria-label="showPassword ? 'Sembunyikan password' : 'Lihat password'"
                    >
                        <svg v-if="showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>

                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <!-- Confirm Password -->
            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Konfirmasi Password"
                    class="!text-[#D8DEE9] !font-semibold text-xs"
                />

                <div class="relative mt-1.5">
                    <TextInput
                        id="password_confirmation"
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        class="block w-full pr-10 !bg-[#2E3440] !border-[#4C566A] !text-[#ECEFF4] placeholder-[#8C96A9] focus:!border-[#88C0D0] focus:!ring-[#88C0D0]"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Ulangi password"
                    />
                    <button
                        type="button"
                        @click="showPasswordConfirmation = !showPasswordConfirmation"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-[#A5B1C2] hover:text-[#ECEFF4] transition-colors focus:outline-none"
                        :aria-label="showPasswordConfirmation ? 'Sembunyikan konfirmasi password' : 'Lihat konfirmasi password'"
                    >
                        <svg v-if="showPasswordConfirmation" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>

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