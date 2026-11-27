<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    userName: { type: String, default: 'Operator' },
    facilityName: { type: String, default: 'WH-KRWNG Barat' },
});

const emit = defineEmits(['done']);

const progress = ref(0);
const launching = ref(false);
const fading = ref(false);
const statusText = ref('Memverifikasi sesi pengguna...');

let timers = [];

function clearTimers() {
    timers.forEach((t) => clearTimeout(t));
    timers = [];
}

function skipOpening() {
    if (fading.value) return;
    clearTimers();
    progress.value = 100;
    fading.value = true;

    timers.push(setTimeout(() => {
        emit('done');
    }, 350));
}

function startFlySequence() {
    if (launching.value || fading.value) return;
    launching.value = true;

    // Setelah logo hover lalu mulai melesat ke atas, fade-out backdrop
    timers.push(setTimeout(() => {
        fading.value = true;
    }, 620));

    timers.push(setTimeout(() => {
        emit('done');
    }, 1100));
}

onMounted(() => {
    if (!props.show) return;

    timers.push(setTimeout(() => {
        progress.value = 35;
        statusText.value = 'Menghubungkan database inventaris & rak...';
    }, 150));

    timers.push(setTimeout(() => {
        progress.value = 78;
        statusText.value = 'Menyiapkan ruang kerja gudang...';
    }, 750));

    timers.push(setTimeout(() => {
        progress.value = 100;
        statusText.value = 'Ruang kerja siap digunakan';
    }, 1300));

    timers.push(setTimeout(() => {
        startFlySequence();
    }, 1550));
});

onBeforeUnmount(() => {
    clearTimers();
});
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-[9999] bg-[#242933] flex flex-col items-center justify-center px-6 select-none transition-opacity duration-500 ease-out"
        :class="fading ? 'opacity-0 pointer-events-none' : 'opacity-100'"
        role="status"
        aria-live="polite"
    >
        <!-- Subtle Ambient Glow -->
        <div
            class="absolute w-72 h-72 sm:w-96 sm:h-96 rounded-full bg-[#88C0D0]/12 blur-3xl pointer-events-none transition-all duration-700"
            :class="launching ? 'scale-150 opacity-0' : 'scale-100 opacity-100'"
        ></div>

        <!-- Center Content -->
        <div class="relative z-10 w-full max-w-sm flex flex-col items-center text-center">
            <!-- Brand Logo (Hover idle -> Hover naik -> Terbang menghilang) -->
            <div
                class="mb-5 will-change-transform"
                :class="launching ? 'logo-hover-fly' : 'logo-idle-hover'"
            >
                <ApplicationLogo />
            </div>

            <!-- Konten teks & progress bar (memudar saat logo bersiap terbang) -->
            <div
                class="w-full flex flex-col items-center transition-all duration-300 ease-out"
                :class="launching ? 'opacity-0 translate-y-2 pointer-events-none' : 'opacity-100 translate-y-0'"
            >
                <!-- Facility Pill -->
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#2E3440] border border-[#4C566A]/60 text-xs font-medium text-[#88C0D0] mb-4">
                    <span class="h-2 w-2 rounded-full bg-[#A3BE8C] animate-pulse"></span>
                    <span>{{ facilityName }}</span>
                </div>

                <!-- Welcome Greeting -->
                <h1 class="text-xl sm:text-2xl font-bold text-[#ECEFF4] tracking-tight mb-1.5">
                    Selamat datang, {{ userName }}
                </h1>
                <p class="text-xs sm:text-sm text-[#A5B1C2] mb-7">
                    {{ statusText }}
                </p>

                <!-- Progress Bar -->
                <div class="w-full h-1.5 bg-[#2E3440] rounded-full overflow-hidden border border-[#3B4252]">
                    <div
                        class="h-full bg-gradient-to-r from-[#5E81AC] via-[#88C0D0] to-[#A3BE8C] rounded-full transition-all duration-500 ease-out"
                        :style="{ width: `${progress}%` }"
                    ></div>
                </div>

                <!-- Progress Percentage -->
                <div class="w-full flex items-center justify-between mt-2.5 text-[11px] font-mono text-[#A5B1C2]">
                    <span>WAREFIVE SYSTEM</span>
                    <span class="text-[#88C0D0] font-semibold">{{ progress }}%</span>
                </div>
            </div>
        </div>

        <!-- Skip Button -->
        <button
            type="button"
            @click="skipOpening"
            class="mt-10 px-4 py-1.5 rounded-lg text-xs font-medium text-[#A5B1C2] hover:text-[#ECEFF4] hover:bg-[#3B4252]/70 border border-transparent hover:border-[#4C566A]/60 transition-all duration-300"
            :class="launching ? 'opacity-0 pointer-events-none' : 'opacity-100'"
        >
            Lewati &rarr;
        </button>
    </div>
</template>

<style scoped>
/* Hover lembut saat loading */
.logo-idle-hover {
    transform: scale(1.1);
    animation: logoFloat 2s ease-in-out infinite;
    filter: drop-shadow(0 0 12px rgba(136, 192, 208, 0.25));
}

/* Fase Hover naik -> Antisipasi -> Terbang ke atas dan menghilang */
.logo-hover-fly {
    animation: logoHoverAndFly 1s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

@keyframes logoFloat {
    0%, 100% {
        transform: translateY(0) scale(1.1);
    }
    50% {
        transform: translateY(-6px) scale(1.14);
    }
}

@keyframes logoHoverAndFly {
    0% {
        transform: translateY(0) scale(1.1);
        opacity: 1;
        filter: drop-shadow(0 0 12px rgba(136, 192, 208, 0.3));
    }
    35% {
        transform: translateY(-12px) scale(1.26);
        opacity: 1;
        filter: drop-shadow(0 0 26px rgba(136, 192, 208, 0.65));
    }
    50% {
        transform: translateY(-4px) scale(1.18);
        opacity: 1;
        filter: drop-shadow(0 0 22px rgba(136, 192, 208, 0.55));
    }
    100% {
        transform: translateY(-180px) scale(0.6);
        opacity: 0;
        filter: drop-shadow(0 0 36px rgba(136, 192, 208, 0.8)) blur(5px);
    }
}
</style>
