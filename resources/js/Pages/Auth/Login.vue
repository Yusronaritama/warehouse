<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

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
            <h2 class="text-lg font-bold text-white tracking-tight">Login Petugas Gudang</h2>
            <p class="text-xs text-slate-400 mt-1">Masukkan username dan kata sandi Anda.</p>
        </div>

        <div v-if="status" class="mb-4 text-xs font-medium text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 p-2.5 rounded-lg">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Username/Login -->
            <div>
                <InputLabel for="login" value="Username Petugas" class="!text-slate-300 !font-semibold text-xs" />

                <TextInput
                    id="login"
                    type="text"
                    class="mt-1.5 block w-full !bg-slate-950 !border-slate-700 !text-white focus:!border-amber-500 focus:!ring-amber-500"
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
                    <InputLabel for="password" value="Password" class="!text-slate-300 !font-semibold text-xs" />
                    <Link
                        v-if="canResetPassword !== false"
                        :href="route('password.request')"
                        class="text-xs text-amber-400 hover:text-amber-300 font-medium transition-colors hover:underline"
                    >
                        Lupa Password?
                    </Link>
                </div>

                <TextInput
                    id="password"
                    type="password"
                    class="block w-full !bg-slate-950 !border-slate-700 !text-white focus:!border-amber-500 focus:!ring-amber-500"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="Masukkan password"
                />

                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <!-- Remember me & Register link -->
            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center cursor-pointer">
                    <Checkbox name="remember" v-model:checked="form.remember" class="!bg-slate-950 !border-slate-700 !text-amber-500 focus:!ring-amber-500" />
                    <span class="ms-2 text-slate-400">Ingat sesi saya</span>
                </label>

                <Link
                    :href="route('register')"
                    class="text-slate-400 hover:text-amber-400 transition-colors"
                >
                    Belum punya akun? Daftar
                </Link>
            </div>

            <!-- Submit -->
            <div class="pt-2">
                <PrimaryButton
                    class="w-full justify-center !bg-amber-500 hover:!bg-amber-400 !text-slate-950 !font-bold !py-2.5 !rounded-lg !border-0 shadow-lg shadow-amber-500/20"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Masuk ke Terminal Warehouse
                </PrimaryButton>
            </div>

            <!-- Bantuan / Quick Links -->
            <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                <span>Kendala akses akun?</span>
                <Link
                    :href="route('password.request')"
                    class="text-amber-400 hover:text-amber-300 font-medium transition-colors hover:underline flex items-center gap-1"
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
