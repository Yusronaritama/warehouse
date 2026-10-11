<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    models: {
        type: Array,
        default: () => [
            {
                name: 'Leopard 2A6 MBT',
                code: 'LEOPARD-2A6',
                origin: 'Germany / Krauss-Maffei Wegmann',
                badge: 'MIL-SPEC 3D ASSET • LEOPARD 2A6 MBT',
                caliber: '120mm Rheinmetall L/55',
                src: '/models/leopard_2_a6.glb',
                sizeNote: '78.6 MB',
            },
            {
                name: 'Challenger 2 MBT',
                code: 'CHALLENGER-2',
                origin: 'United Kingdom / Vickers Defence',
                badge: 'MIL-SPEC 3D ASSET • CHALLENGER 2 MBT',
                caliber: '120mm L30A1 Rifled Gun',
                src: '/models/challenger_ii.glb',
                sizeNote: '82.2 MB',
            },
        ],
    },
    initialIndex: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits(['change']);

const currentIndex = ref(props.initialIndex);
const modelViewerRef = ref(null);
const autoRotateActive = ref(true);

const currentModel = computed(() => {
    return props.models[currentIndex.value] || props.models[0];
});

function nextModel() {
    if (currentIndex.value < props.models.length - 1) {
        currentIndex.value++;
    } else {
        currentIndex.value = 0;
    }
    emit('change', currentIndex.value, currentModel.value);
}

function prevModel() {
    if (currentIndex.value > 0) {
        currentIndex.value--;
    } else {
        currentIndex.value = props.models.length - 1;
    }
    emit('change', currentIndex.value, currentModel.value);
}

function selectModel(index) {
    currentIndex.value = index;
    emit('change', currentIndex.value, currentModel.value);
}

function toggleAutoRotate() {
    autoRotateActive.value = !autoRotateActive.value;
    if (modelViewerRef.value) {
        modelViewerRef.value.autoRotate = autoRotateActive.value;
    }
}

function resetCamera() {
    if (modelViewerRef.value) {
        modelViewerRef.value.cameraOrbit = '45deg 70deg auto';
        modelViewerRef.value.resetTurntableRotation();
    }
}
</script>

<template>
    <div class="tank-viewer-container">
        <!-- Tactical Header Controls Strip -->
        <div class="tactical-toolbar">
            <div class="tactical-badge">
                <span class="live-dot"></span>
                <span class="font-mono">{{ currentModel.badge }}</span>
                <span class="model-counter font-mono">[{{ currentIndex + 1 }}/{{ models.length }}]</span>
            </div>

            <div class="toolbar-actions">
                <button
                    @click="toggleAutoRotate"
                    class="btn-tactical"
                    :class="{ 'btn-tactical--active': autoRotateActive }"
                    title="Toggle Putar Otomatis"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>{{ autoRotateActive ? 'Auto Rotate' : 'Paused' }}</span>
                </button>
                <button
                    @click="resetCamera"
                    class="btn-tactical"
                    title="Kembalikan Sudut Kamera Awal"
                >
                    Reset Angle
                </button>
            </div>
        </div>

        <!-- Huge Stage for 3D Tank with Carousel Nav (Left & Right Buttons) -->
        <div class="tank-massive-stage">
            <!-- Tactical Ground Radar / Grid Disc -->
            <div class="tactical-pedestal">
                <div class="pedestal-glow"></div>
                <div class="pedestal-shadow"></div>
                <div class="pedestal-grid-outer"></div>
                <div class="pedestal-grid-inner"></div>
            </div>

            <!-- Carousel Button: Previous (<) -->
            <button
                @click="prevModel"
                class="carousel-nav-btn carousel-nav-prev"
                aria-label="Model Sebelumnya"
                title="Model Sebelumnya"
            >
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <!-- Carousel Button: Next (>) -->
            <button
                @click="nextModel"
                class="carousel-nav-btn carousel-nav-next"
                aria-label="Model Selanjutnya"
                title="Model Selanjutnya"
            >
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <!-- 3D Model Canvas (Enlarged Scale with field-of-view & orbit) -->
            <model-viewer
                ref="modelViewerRef"
                :key="currentModel.src"
                :src="currentModel.src"
                :alt="currentModel.name"
                auto-rotate
                camera-controls
                touch-action="pan-y"
                shadow-intensity="2.4"
                shadow-softness="0.75"
                exposure="1.05"
                environment-image="neutral"
                camera-orbit="45deg 70deg auto"
                field-of-view="24deg"
                min-field-of-view="12deg"
                max-field-of-view="45deg"
                min-camera-orbit="auto auto 5%"
                max-camera-orbit="auto auto 200%"
                interaction-prompt="none"
                class="w-full h-full"
            >
                <template #poster>
                    <div class="loading-state">
                        <div class="radar-spinner"></div>
                        <span class="text-xs font-mono text-white">
                            MEMUAT ASET MILITER: {{ currentModel.name.toUpperCase() }} ({{ currentModel.sizeNote }})...
                        </span>
                    </div>
                </template>
            </model-viewer>

            <!-- Bottom Tactical HUD: Model Specs & Carousel Dots -->
            <div class="tactical-hud-panel">
                <div class="hud-model-info">
                    <div class="font-mono text-xs font-bold text-white uppercase tracking-wider">
                        {{ currentModel.name }}
                    </div>
                    <div class="text-[11px] text-slate-300">
                        {{ currentModel.origin }} • Gun: {{ currentModel.caliber }}
                    </div>
                </div>

                <!-- Carousel Dot Pagination -->
                <div class="carousel-dots">
                    <button
                        v-for="(m, idx) in models"
                        :key="m.src"
                        @click="selectModel(idx)"
                        class="carousel-dot"
                        :class="{ 'carousel-dot--active': currentIndex === idx }"
                        :title="m.name"
                        :aria-label="`Pilih ${m.name}`"
                    >
                        <span class="sr-only">{{ m.name }}</span>
                    </button>
                </div>
            </div>

            <!-- Floating Tactical Hint -->
            <div class="tactical-hint">
                <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                </svg>
                <span class="font-mono text-xs text-white">Klik & geser kursor untuk inspeksi bodi 360° | Tombol &lt; / &gt; untuk ganti alutsista</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.tank-viewer-container {
    width: 100%;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

/* Tactical Controls Strip */
.tactical-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    max-width: 800px;
    margin-bottom: 0.5rem;
    z-index: 10;
}

.tactical-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 5px 14px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 0.06em;
}

.live-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow: 0 0 8px #ffffff;
    animation: pulse 1.8s infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.8); }
}

.toolbar-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-tactical {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 600;
    padding: 6px 14px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.05);
    color: #cbd5e1;
    border: 1px solid rgba(255, 255, 255, 0.15);
    cursor: pointer;
    transition: all 0.15s;
    backdrop-filter: blur(8px);
}
.btn-tactical:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(255, 255, 255, 0.3);
}
.btn-tactical--active {
    color: #000000;
    border-color: #ffffff;
    background: #ffffff;
}

/* Massive Stage for Big Tank Model (Increased Scale) */
.tank-massive-stage {
    width: 100%;
    height: 680px; /* Expanded vertical canvas for larger tank scale */
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

@media (max-width: 768px) {
    .tank-massive-stage {
        height: 480px;
    }
}

/* Ground radar & pedestal disc (Monochrome Silver/White) */
.tactical-pedestal {
    position: absolute;
    bottom: 25px;
    left: 50%;
    transform: translateX(-50%);
    width: 820px;
    height: 320px;
    pointer-events: none;
    z-index: 1;
}

.pedestal-glow {
    position: absolute;
    inset: -30px;
    border-radius: 50%;
    background: radial-gradient(ellipse at center, rgba(255, 255, 255, 0.12) 0%, transparent 70%);
    filter: blur(40px);
}

.pedestal-shadow {
    position: absolute;
    inset: 40px;
    border-radius: 50%;
    background: radial-gradient(ellipse at center, rgba(0, 0, 0, 0.98) 0%, rgba(0, 0, 0, 0.7) 45%, transparent 70%);
}

.pedestal-grid-outer {
    position: absolute;
    inset: 15px;
    border-radius: 50%;
    border: 1px dashed rgba(255, 255, 255, 0.25);
    transform: rotateX(65deg);
}

.pedestal-grid-inner {
    position: absolute;
    inset: 60px;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, 0.15);
    transform: rotateX(65deg);
}

/* model-viewer canvas directly integrated */
model-viewer {
    width: 100%;
    height: 100%;
    --poster-color: transparent;
    cursor: grab;
    z-index: 2;
}
model-viewer:active {
    cursor: grabbing;
}

.loading-state {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 16px;
    background: rgba(0, 0, 0, 0.75);
}

.radar-spinner {
    width: 44px;
    height: 44px;
    border: 3px solid rgba(255, 255, 255, 0.2);
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: spin 0.9s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Floating Hint */
.tactical-hint {
    position: absolute;
    bottom: 12px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(10, 10, 10, 0.88);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #ffffff;
    padding: 6px 18px;
    border-radius: 999px;
    display: flex;
    align-items: center;
    gap: 8px;
    pointer-events: none;
    z-index: 5;
    backdrop-filter: blur(12px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.7);
}

/* Carousel Navigation Arrows (< and >) */
.carousel-nav-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: rgba(15, 15, 15, 0.85);
    border: 1.5px solid rgba(255, 255, 255, 0.4);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    backdrop-filter: blur(14px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.8), 0 0 16px rgba(255, 255, 255, 0.15);
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.carousel-nav-btn:hover {
    background: #ffffff;
    border-color: #ffffff;
    color: #000000;
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.9), 0 0 24px rgba(255, 255, 255, 0.5);
}
.carousel-nav-btn:active {
    transform: translateY(-50%) scale(0.96);
}
.carousel-nav-prev {
    left: 20px;
}
.carousel-nav-next {
    right: 20px;
}

@media (max-width: 768px) {
    .carousel-nav-btn {
        width: 42px;
        height: 42px;
    }
    .carousel-nav-prev {
        left: 8px;
    }
    .carousel-nav-next {
        right: 8px;
    }
}

/* Tactical HUD Panel (Bottom Specs & Dots) */
.tactical-hud-panel {
    position: absolute;
    bottom: 52px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 24px;
    padding: 8px 20px;
    background: rgba(10, 10, 10, 0.92);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 12px;
    z-index: 6;
    backdrop-filter: blur(14px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.8);
}
.hud-model-info {
    text-align: left;
}
.carousel-dots {
    display: flex;
    align-items: center;
    gap: 8px;
}
.carousel-dot {
    width: 24px;
    height: 6px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}
.carousel-dot:hover {
    background: rgba(255, 255, 255, 0.5);
}
.carousel-dot--active {
    background: #ffffff;
    width: 38px;
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
}
</style>
