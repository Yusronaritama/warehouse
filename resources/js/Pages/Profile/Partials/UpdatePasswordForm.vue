<script setup>
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header class="pb-4 border-b border-[#4C566A]/60">
            <h2 class="text-base sm:text-lg font-bold text-[#ECEFF4]">
                Ubah Kata Sandi
            </h2>

            <p class="mt-1 text-xs sm:text-sm text-[#A5B1C2]">
                Gunakan kata sandi yang panjang dan acak untuk menjaga keamanan akun Anda.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-5">
            <div>
                <label for="current_password" class="block text-xs font-semibold text-[#D8DEE9] mb-1.5">
                    Kata Sandi Saat Ini
                </label>

                <input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3.5 py-2.5 text-sm text-[#ECEFF4] focus:outline-none focus:border-[#88C0D0] transition-colors"
                    autocomplete="current-password"
                />

                <InputError
                    :message="form.errors.current_password"
                    class="mt-2"
                />
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-[#D8DEE9] mb-1.5">
                    Kata Sandi Baru
                </label>

                <input
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3.5 py-2.5 text-sm text-[#ECEFF4] focus:outline-none focus:border-[#88C0D0] transition-colors"
                    autocomplete="new-password"
                />

                <InputError :message="form.errors.password" class="mt-2" />
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-[#D8DEE9] mb-1.5">
                    Konfirmasi Kata Sandi Baru
                </label>

                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3.5 py-2.5 text-sm text-[#ECEFF4] focus:outline-none focus:border-[#88C0D0] transition-colors"
                    autocomplete="new-password"
                />

                <InputError
                    :message="form.errors.password_confirmation"
                    class="mt-2"
                />
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-5 py-2.5 rounded-lg bg-[#5E81AC] hover:bg-[#81A1C1] text-[#ECEFF4] font-semibold text-sm shadow-xs active:scale-95 transition-all disabled:opacity-50"
                >
                    {{ form.processing ? 'Menyimpan...' : 'Perbarui Kata Sandi' }}
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
                        Kata sandi diperbarui.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
