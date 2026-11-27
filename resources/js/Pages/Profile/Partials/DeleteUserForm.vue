<script setup>
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-5">
        <header class="pb-4 border-b border-[#BF616A]/30">
            <h2 class="text-base sm:text-lg font-bold text-[#BF616A]">
                Hapus Akun
            </h2>

            <p class="mt-1 text-xs sm:text-sm text-[#A5B1C2]">
                Setelah akun dihapus, seluruh akses dan data profil akun Anda akan dihapus secara permanen. Pastikan Anda telah menyimpan informasi penting sebelum melanjutkan.
            </p>
        </header>

        <button
            type="button"
            @click="confirmUserDeletion"
            class="px-5 py-2.5 rounded-lg bg-[#BF616A]/20 hover:bg-[#BF616A] text-[#BF616A] hover:text-[#ECEFF4] border border-[#BF616A]/40 font-semibold text-sm transition-all active:scale-95"
        >
            Hapus Akun Permanen
        </button>

        <!-- Modal Konfirmasi Hapus Akun (Nordic Dim, z-[70]) -->
        <div
            v-if="confirmingUserDeletion"
            class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-[#242933]/80 backdrop-blur-xs"
            @click.self="closeModal"
        >
            <div class="bg-[#3B4252] border border-[#BF616A]/40 rounded-2xl max-w-md w-full p-6 shadow-2xl text-[#ECEFF4]">
                <h2 class="text-lg font-bold text-[#ECEFF4]">
                    Yakin ingin menghapus akun Anda?
                </h2>

                <p class="mt-2 text-xs sm:text-sm text-[#D8DEE9] leading-relaxed">
                    Tindakan ini bersifat permanen dan tidak dapat dibatalkan. Masukkan kata sandi Anda untuk mengonfirmasi penghapusan akun.
                </p>

                <div class="mt-5">
                    <label for="delete-account-password" class="sr-only">Kata Sandi</label>

                    <input
                        id="delete-account-password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3.5 py-2.5 text-sm text-[#ECEFF4] placeholder-[#A5B1C2] focus:outline-none focus:border-[#BF616A]"
                        placeholder="Masukkan kata sandi Anda..."
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-4 py-2 rounded-lg bg-[#434C5E] hover:bg-[#4C566A] text-sm font-medium text-[#D8DEE9] transition-colors"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        :disabled="form.processing"
                        @click="deleteUser"
                        class="px-4 py-2 rounded-lg bg-[#BF616A] hover:bg-[#D08770] text-sm font-semibold text-[#ECEFF4] shadow-xs transition-all disabled:opacity-50"
                    >
                        {{ form.processing ? 'Menghapus...' : 'Ya, Hapus Akun' }}
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>
