<script setup>
import { ref, watch, nextTick, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Html5Qrcode } from 'html5-qrcode';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'scanned']);

const isStarting = ref(false);
const isScanning = ref(false);
const cameraError = ref(null);
const html5QrCodeRef = ref(null);
const scannerElementId = 'live-camera-scanner-viewfinder';

watch(
    () => props.modelValue,
    async (isOpen) => {
        if (isOpen) {
            cameraError.value = null;
            await nextTick();
            startCamera();
        } else {
            stopCamera();
        }
    }
);

async function startCamera() {
    isStarting.value = true;
    cameraError.value = null;

    try {
        if (html5QrCodeRef.value) {
            await stopCamera();
        }

        const scanner = new Html5Qrcode(scannerElementId);
        html5QrCodeRef.value = scanner;

        const config = {
            fps: 15,
            qrbox: (viewfinderWidth, viewfinderHeight) => {
                const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                const qrboxSize = Math.floor(minEdge * 0.75);
                return {
                    width: qrboxSize,
                    height: qrboxSize,
                };
            },
            aspectRatio: 1.0,
        };

        await scanner.start(
            { facingMode: 'environment' },
            config,
            (decodedText) => {
                handleScannedCode(decodedText);
            },
            () => {
                // Ignore frame parse misses
            }
        );

        isScanning.value = true;
    } catch (err) {
        console.error('Camera start error:', err);
        cameraError.value =
            err?.message ||
            'Gagal mengakses kamera HP. Pastikan Anda mengizinkan akses kamera pada browser.';
    } finally {
        isStarting.value = false;
    }
}

async function stopCamera() {
    if (html5QrCodeRef.value) {
        try {
            if (html5QrCodeRef.value.isScanning) {
                await html5QrCodeRef.value.stop();
            }
            await html5QrCodeRef.value.clear();
        } catch (err) {
            console.warn('Camera stop warning:', err);
        } finally {
            html5QrCodeRef.value = null;
            isScanning.value = false;
        }
    }
}

function handleScannedCode(rawCode) {
    if (!rawCode || !rawCode.trim()) return;

    // Haptic feedback if supported on phone
    if (typeof navigator !== 'undefined' && navigator.vibrate) {
        navigator.vibrate(100);
    }

    emit('scanned', rawCode.trim());
    stopCamera();
    emit('update:modelValue', false);

    const text = rawCode.trim();

    // 1. If scanned text is already a full URL pointing to /racks/
    if (text.includes('/racks/')) {
        const parts = text.split('/racks/');
        if (parts[1]) {
            const rackPart = parts[1].split('?')[0].split('#')[0];
            router.visit(route('racks.show', decodeURIComponent(rackPart)));
            return;
        }
    }

    // 2. If it is any web URL with ?code= or ?scan=
    if (text.startsWith('http://') || text.startsWith('https://')) {
        try {
            const url = new URL(text);
            const scanParam = url.searchParams.get('scan') || url.searchParams.get('code');
            if (scanParam) {
                router.visit(route('racks.show', scanParam));
                return;
            }
            // If it's a URL within the same origin or ngrok
            if (url.origin === window.location.origin) {
                window.location.href = text;
                return;
            }
        } catch (e) {
            // fallback
        }
    }

    // 3. Raw barcode code or rack code (e.g. BC-A01-1024 or A-01)
    router.visit(route('racks.show', text));
}

function close() {
    stopCamera();
    emit('update:modelValue', false);
}

onUnmounted(() => {
    stopCamera();
});
</script>

<template>
    <div
        v-if="modelValue"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md"
    >
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-5 shadow-2xl space-y-4 text-slate-100 relative">
            <!-- Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 rounded-lg">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-white">Live Scanner Kamera HP</h3>
                        <p class="text-[11px] text-slate-400">Arahkan kamera ke Barcode atau QR Code Rak</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="close"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Error Banner -->
            <div
                v-if="cameraError"
                class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs space-y-2"
            >
                <div class="flex items-center gap-2 font-bold">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>Izin Kamera Diperlukan</span>
                </div>
                <p class="text-[11px] text-slate-400 leading-relaxed">{{ cameraError }}</p>
                <button
                    type="button"
                    @click="startCamera"
                    class="px-3 py-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-xs font-semibold"
                >
                    Coba Lagi
                </button>
            </div>

            <!-- Viewfinder Area -->
            <div class="relative bg-black rounded-xl overflow-hidden min-h-[280px] flex items-center justify-center border border-slate-800">
                <div id="live-camera-scanner-viewfinder" class="w-full"></div>

                <!-- Loading overlay -->
                <div
                    v-if="isStarting"
                    class="absolute inset-0 bg-slate-950/90 flex flex-col items-center justify-center gap-2 text-xs text-slate-400"
                >
                    <svg class="animate-spin h-6 w-6 text-cyan-400" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Mengaktifkan kamera HP...</span>
                </div>

                <!-- Laser Target Animation Overlay -->
                <div
                    v-if="isScanning"
                    class="absolute inset-0 pointer-events-none flex flex-col items-center justify-center"
                >
                    <div class="w-56 h-56 border-2 border-cyan-400/60 rounded-xl relative shadow-lg shadow-cyan-500/20">
                        <!-- Corner Markers -->
                        <span class="absolute -top-1 -left-1 w-4 h-4 border-t-2 border-l-2 border-cyan-400"></span>
                        <span class="absolute -top-1 -right-1 w-4 h-4 border-t-2 border-r-2 border-cyan-400"></span>
                        <span class="absolute -bottom-1 -left-1 w-4 h-4 border-b-2 border-l-2 border-cyan-400"></span>
                        <span class="absolute -bottom-1 -right-1 w-4 h-4 border-b-2 border-r-2 border-cyan-400"></span>

                        <!-- Animated Laser Scanner Line -->
                        <div class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-transparent via-cyan-400 to-transparent animate-laser-scan shadow-sm shadow-cyan-400"></div>
                    </div>
                </div>
            </div>

            <div class="text-center text-[11px] text-slate-400">
                Setelah barcode atau QR code terdeteksi, Anda akan otomatis dialihkan ke halaman edit rak.
            </div>

            <div class="flex justify-end pt-2 border-t border-slate-800">
                <button
                    type="button"
                    @click="close"
                    class="w-full py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-xs font-semibold transition-colors"
                >
                    Tutup Kamera
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes laserScan {
    0% {
        top: 0%;
        opacity: 0.8;
    }
    50% {
        top: 100%;
        opacity: 1;
    }
    100% {
        top: 0%;
        opacity: 0.8;
    }
}

.animate-laser-scan {
    animation: laserScan 2s ease-in-out infinite;
}

#live-camera-scanner-viewfinder video {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    border-radius: 0.75rem;
}
</style>
