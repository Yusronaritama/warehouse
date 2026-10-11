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
                <InputLabel for="password" value="Password" class="!text-slate-300 !font-semibold text-xs" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1.5 block w-full !bg-slate-950 !border-slate-700 !text-white focus:!border-amber-500 focus:!ring-amber-500"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="Masukkan password"
                />

                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <!-- Remember me -->
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
        </form>
    </GuestLayout>
</template>
