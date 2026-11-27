<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Pengaturan Profil Akun" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="p-1.5 bg-[#434C5E] border border-[#4C566A] rounded-lg text-[#88C0D0]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </span>
                    <div>
                        <h1 class="text-lg sm:text-xl font-bold tracking-tight text-[#ECEFF4]">
                            Pengaturan Profil &amp; Keamanan Akun
                        </h1>
                        <p class="text-xs text-[#A5B1C2]">Kelola informasi operator, kata sandi, dan akses terminal WAREFIVE.</p>
                    </div>
                </div>

                <Link
                    :href="route('dashboard')"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-[#3B4252] border border-[#4C566A]/60 text-xs font-medium text-[#ECEFF4] hover:bg-[#434C5E] transition-colors self-start sm:self-auto"
                >
                    <svg class="w-4 h-4 text-[#88C0D0]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Dashboard
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Profile Avatar Hero Card (Mobile App Style) -->
            <div class="bg-[#3B4252] border border-[#4C566A]/60 rounded-2xl p-6 sm:p-8 shadow-xs">
                <div class="flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left">
                    <!-- Inisial Avatar (Huruf Pertama Username) -->
                    <div class="relative shrink-0">
                        <div class="h-20 w-20 sm:h-24 sm:w-24 rounded-full bg-gradient-to-br from-[#5E81AC]/35 to-[#88C0D0]/20 border-2 border-[#88C0D0]/60 flex items-center justify-center shadow-lg shadow-[#88C0D0]/10">
                            <span class="text-3xl sm:text-4xl font-extrabold text-[#ECEFF4] uppercase tracking-wider">
                                {{ ($page.props.auth?.user?.name || 'U').charAt(0) }}
                            </span>
                        </div>
                        <span
                            class="absolute bottom-1 right-1 h-4 w-4 rounded-full bg-[#A3BE8C] border-2 border-[#3B4252]"
                            title="Status Aktif"
                        ></span>
                    </div>

                    <!-- Identitas & Role -->
                    <div class="space-y-1.5 min-w-0 flex-1">
                        <h2 class="text-xl sm:text-2xl font-bold text-[#ECEFF4] truncate">
                            {{ $page.props.auth?.user?.name }}
                        </h2>
                        <p class="text-xs sm:text-sm text-[#A5B1C2] truncate">
                            {{ $page.props.auth?.user?.email }}
                        </p>
                        <div class="pt-1.5 flex flex-wrap items-center justify-center sm:justify-start gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#2E3440] border border-[#4C566A]/60 text-xs font-medium text-[#88C0D0]">
                                Warehouse Supervisor
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#2E3440] border border-[#4C566A]/60 text-xs font-medium text-[#A3BE8C]">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#A3BE8C]"></span>
                                {{ $page.props.facility?.name || 'WH-KRWNG Barat' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Profil -->
            <div class="bg-[#3B4252] border border-[#4C566A]/60 rounded-xl p-5 sm:p-8 shadow-xs">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <!-- Ubah Kata Sandi -->
            <div class="bg-[#3B4252] border border-[#4C566A]/60 rounded-xl p-5 sm:p-8 shadow-xs">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <!-- Hapus Akun -->
            <div class="bg-[#3B4252] border border-[#BF616A]/30 rounded-xl p-5 sm:p-8 shadow-xs">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
