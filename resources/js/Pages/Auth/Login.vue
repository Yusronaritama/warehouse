<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const showPassword = ref(false);

const form = useForm({
    login: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk Sistem Warehouse" />

        <div class="mb-5">
            <h2 class="text-lg font-bold text-[#ECEFF4] tracking-tight">Login Petugas Gudang</h2>
            <p class="text-xs text-[#A5B1C2] mt-1">Masukkan username dan kata sandi Anda.</p>
        </div>

        <div v-if="status" class="mb-4 text-xs font-medium text-[#A3BE8C] bg-[#A3BE8C]/15 border border-[#A3BE8C]/30 p-2.5 rounded-lg">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Username/Login -->
            <div>
                <InputLabel for="login" value="Username Petugas" class="!text-[#D8DEE9] !font-semibold text-xs" />

                <TextInput
                    id="login"
                    type="text"
                    class="mt-1.5 block w-full !bg-[#2E3440] !border-[#4C566A] !text-[#ECEFF4] placeholder-[#8C96A9] focus:!border-[#88C0D0] focus:!ring-[#88C0D0]"
                    v-model="form.login"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Masukkan username"
                />

                <InputError class="mt-1.5" :message="form.errors.login" />
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <InputLabel for="password" value="Password" class="!text-[#D8DEE9] !font-semibold text-xs" />
                    <Link
                        v-if="canResetPassword !== false"
                        :href="route('password.request')"
                        class="text-xs text-[#88C0D0] hover:text-[#81A1C1] font-medium transition-colors hover:underline"
                    >
                        Lupa Password?
                    </Link>
                </div>

                <div class="relative">
                    <TextInput
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        class="block w-full pr-10 !bg-[#2E3440] !border-[#4C566A] !text-[#ECEFF4] placeholder-[#8C96A9] focus:!border-[#88C0D0] focus:!ring-[#88C0D0]"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="Masukkan password"
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

            <!-- Remember me & Register link -->
            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center cursor-pointer">
                    <Checkbox name="remember" v-model:checked="form.remember" class="!bg-[#2E3440] !border-[#4C566A] !text-[#5E81AC] focus:!ring-[#88C0D0]" />
                    <span class="ms-2 text-[#A5B1C2]">Ingat sesi saya</span>
                </label>

                <Link
                    :href="route('register')"
                    class="text-[#A5B1C2] hover:text-[#88C0D0] transition-colors"
                >
                    Belum punya akun? Daftar
                </Link>
            </div>

            <!-- Submit -->
            <div class="pt-2">
                <PrimaryButton
                    class="w-full justify-center !bg-[#5E81AC] hover:!bg-[#81A1C1] !text-[#ECEFF4] !font-bold !py-2.5 !rounded-lg !border-0 shadow-lg shadow-[#5E81AC]/25"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Masuk ke Terminal Warehouse
                </PrimaryButton>
            </div>

            <!-- Bantuan / Quick Links -->
            <div class="pt-3 border-t border-[#4C566A]/60 flex items-center justify-between text-xs text-[#A5B1C2]">
                <span>Kendala akses akun?</span>
                <Link
                    :href="route('password.request')"
                    class="text-[#88C0D0] hover:text-[#81A1C1] font-medium transition-colors hover:underline flex items-center gap-1"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Bantuan Reset Password
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>