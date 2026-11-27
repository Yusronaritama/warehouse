<script setup>
import InputError from '@/Components/InputError.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header class="pb-4 border-b border-[#4C566A]/60">
            <h2 class="text-base sm:text-lg font-bold text-[#ECEFF4]">
                Informasi Profil
            </h2>

            <p class="mt-1 text-xs sm:text-sm text-[#A5B1C2]">
                Perbarui nama operator dan alamat email akun Anda.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-5"
        >
            <div>
                <label for="name" class="block text-xs font-semibold text-[#D8DEE9] mb-1.5">
                    Nama Lengkap
                </label>

                <input
                    id="name"
                    type="text"
                    class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3.5 py-2.5 text-sm text-[#ECEFF4] focus:outline-none focus:border-[#88C0D0] transition-colors"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold text-[#D8DEE9] mb-1.5">
                    Alamat Email
                </label>

                <input
                    id="email"
                    type="email"
                    class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3.5 py-2.5 text-sm text-[#ECEFF4] focus:outline-none focus:border-[#88C0D0] transition-colors"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="p-3 rounded-lg bg-[#EBCB8B]/10 border border-[#EBCB8B]/30">
                <p class="text-xs sm:text-sm text-[#EBCB8B]">
                    Alamat email Anda belum terverifikasi.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="underline font-semibold text-[#ECEFF4] hover:text-[#88C0D0] ml-1"
                    >
                        Klik di sini untuk mengirim ulang email verifikasi.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-xs font-medium text-[#A3BE8C]"
                >
                    Tautan verifikasi baru telah dikirim ke alamat email Anda.
                </div>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-5 py-2.5 rounded-lg bg-[#5E81AC] hover:bg-[#81A1C1] text-[#ECEFF4] font-semibold text-sm shadow-xs active:scale-95 transition-all disabled:opacity-50"
                >
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                </button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-xs sm:text-sm text-[#A3BE8C] font-medium"
                    >
                        Tersimpan.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
