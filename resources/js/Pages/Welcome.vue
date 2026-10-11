<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, nextTick } from 'vue';

// Modular Warehouse Components
import Car3DViewer from '@/Components/Warehouse/Car3DViewer.vue';
import CinematicIntroOverlay from '@/Components/Warehouse/CinematicIntroOverlay.vue';
import TacticalBackground from '@/Components/Warehouse/TacticalBackground.vue';
import TacticalHeaderBar from '@/Components/Warehouse/TacticalHeaderBar.vue';
import MilitaryCategoryGrid from '@/Components/Warehouse/MilitaryCategoryGrid.vue';

defineProps({
    canLogin:       { type: Boolean },
    canRegister:    { type: Boolean },
    laravelVersion: { type: String, required: true },
    phpVersion:     { type: String, required: true },
});

const menuOpen = ref(false);
const heroStep = ref(0);
const featuresRef = ref(null);
const featuresVisible = ref(false);

// Military Logistics & Defense Asset Categories
const militaryAssetCategories = [
    {
        title: 'Main Battle Tanks & Armored Vehicles',
        desc: 'Inventarisasi unit tempur berat (Leopard 2A6, Panser Anoa, IFV Marder), sparepart rantai roda (tracks), serta modul turet meriam 120mm.',
        badge: 'Heavy Armor (Tier-1)',
        icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
    },
    {
        title: 'Amunisi & Ballistic Storage',
        desc: 'Penyimpanan terkontrol suhu untuk proyektil kaliber berat, propelan artileri, dan material sensitif dalam zona bunker berstandar militer.',
        badge: 'Bunker Hazmat Class-A',
        icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    },
    {
        title: 'Komunikasi Taktis & Radar Avionik',
        desc: 'Radio VHF/UHF anti-jamming, modul navigasi satelit terenkripsi, sensor termal optik malam (FLIR), dan display kontrol komando.',
        badge: 'Encrypted Avionics',
        icon: 'M13 10V3L4 14h7v7l9-11h-7z',
    },
    {
        title: 'Sparepart Mesin Diesel & Hidrolik Berat',
        desc: 'Mesin turbodiesel MTU 1,500 HP, transmisi otomatis hidrolik Renk HSWL, silinder peredam kejut torsi tinggi, dan pelumas militer.',
        badge: 'Powerpack & Trans',
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
    },
];

// Opening Tactical Boot Sequence State
const introActive = ref(true);
const introFading = ref(false);
const introProgress = ref(0);
const introStepIndex = ref(0);
const introStatusText = ref('INITIALIZING DEFENSE NETWORK SATELLITE UPLINK...');

const bootLogs = [
    { code: 'SAT-01', text: 'CONNECTING QUANTUM-ENCRYPTED DEFENSE UPLINK [SATCOM-4]...', delay: 600, progress: 20 },
    { code: 'BIO-SEC', text: 'BIOMETRIC HANDSHAKE & CLEARANCE LEVEL-4 AUTHORIZED...', delay: 1500, progress: 45 },
    { code: 'DEPOT-DB', text: 'MOUNTING ARSENAL INVENTORY DATABASE [SECTOR WH-KRWNG]...', delay: 2400, progress: 68 },
    { code: 'RADAR-3D', text: 'TELEMETRY CALIBRATION: MAIN BATTLE TANK ASSETS ONLINE...', delay: 3400, progress: 88 },
    { code: 'ACCESS', text: 'CLEARANCE GRANTED. DEPLOYING COMMAND TERMINAL DISPLAY.', delay: 4300, progress: 100 },
];

// Web Audio API Tactical Sound Effects (Reusing single context to avoid GC pauses)
let sharedAudioCtx = null;
function playTacticalBeep(freq = 880, duration = 0.05, type = 'sine') {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        if (!sharedAudioCtx) {
            sharedAudioCtx = new AudioCtx();
        }
        if (sharedAudioCtx.state === 'suspended') {
            sharedAudioCtx.resume();
        }
        const osc = sharedAudioCtx.createOscillator();
        const gain = sharedAudioCtx.createGain();
        osc.type = type;
        osc.frequency.setValueAtTime(freq, sharedAudioCtx.currentTime);
        gain.gain.setValueAtTime(0.035, sharedAudioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.0001, sharedAudioCtx.currentTime + duration);
        osc.connect(gain);
        gain.connect(sharedAudioCtx.destination);
        osc.start();
        osc.stop(sharedAudioCtx.currentTime + duration);
    } catch (_) {}
}

function runHeroAnimations() {
    setTimeout(() => { heroStep.value = 1; }, 80);
    setTimeout(() => { heroStep.value = 2; }, 200);
    setTimeout(() => { heroStep.value = 3; }, 350);
    setTimeout(() => { heroStep.value = 4; }, 500);
    setTimeout(() => { heroStep.value = 5; }, 650);
}

function skipIntro() {
    playTacticalBeep(1200, 0.08, 'triangle');
    introFading.value = true;
    setTimeout(() => {
        introActive.value = false;
        runHeroAnimations();
    }, 600);
}

onMounted(async () => {
    await nextTick();

    // Run opening sequence across 5 seconds with cinematic audio cues & telemetry
    bootLogs.forEach((item, idx) => {
        setTimeout(() => {
            if (!introActive.value) return;
            introStatusText.value = item.text;
            introProgress.value = item.progress;
            introStepIndex.value = idx;
            playTacticalBeep(700 + idx * 120, 0.04, 'sine');
        }, item.delay);
    });

    // Final entry chime
    setTimeout(() => {
        if (introActive.value) {
            playTacticalBeep(1400, 0.12, 'sine');
        }
    }, 4500);

    // Auto complete intro at 5.0 seconds (5000ms)
    setTimeout(() => {
        if (introActive.value) {
            skipIntro();
        }
    }, 5000);

    const obs = new IntersectionObserver(
        ([entry]) => { if (entry.isIntersecting) { featuresVisible.value = true; obs.disconnect(); } },
        { threshold: 0.1 }
    );
    if (featuresRef.value) obs.observe(featuresRef.value);
});

function toggleMenu() { menuOpen.value = !menuOpen.value; }
function closeMenu()  { menuOpen.value = false; }
</script>

<template>
    <Head title="WAREREX - Military Logistics & Defense Asset Command" />

    <!-- 1. Cinematic Opening Overlay -->
    <CinematicIntroOverlay
        :active="introActive"
        :fading="introFading"
        :progress="introProgress"
        :step-index="introStepIndex"
        :status-text="introStatusText"
        @skip="skipIntro"
    />

    <div class="wms-root">
        <a href="#main-content" class="skip-link">Skip to main content</a>

        <!-- 2. Technical Blueprint Schematic Background -->
        <TacticalBackground />

        <!-- 3. Header Navigation Bar -->
        <TacticalHeaderBar
            :can-login="canLogin"
            :can-register="canRegister"
            :menu-open="menuOpen"
            @toggle-menu="toggleMenu"
            @close-menu="closeMenu"
        />

        <!-- Main Content -->
        <main id="main-content" class="wms-main">
            <!-- 4. Hero Section with 3D Tank Showcase -->
            <section class="wms-hero-tactical">
                <div class="wms-hero-tank-stage" :class="{ 'in': heroStep >= 1 }">
                    <Car3DViewer v-if="!introActive || heroStep >= 1" />
                </div>

                <div class="wms-hero-header">
                    <div class="wms-hero-label" :class="{ 'in': heroStep >= 2 }">
                        DEFENSE LOGISTICS & ARSENAL INVENTORY DEPOT
                    </div>
                    <h1 class="wms-hero-heading" :class="{ 'in': heroStep >= 3 }">
                        Gudang Logistik Militer & Alutsista Berat.<br>
                        <span class="wms-hero-accent">Pemantauan Taktis 360° Real-Time.</span>
                    </h1>
                    <p class="wms-hero-desc" :class="{ 'in': heroStep >= 4 }">
                        Sistem komando terpadu untuk pencatatan suku cadang tempur, kendaraan lapis baja, amunisi, dan rotasi pemeliharaan alutsista. Putar dan periksa model 3D Main Battle Tank (Leopard 2A6 & Challenger 2) di atas secara bebas, atau klik tombol panah untuk mengganti alutsista.
                    </p>
                    <div class="wms-hero-actions" :class="{ 'in': heroStep >= 5 }">
                        <Link v-if="canLogin && !$page.props.auth?.user"
                              :href="route('login')" class="wms-btn-primary wms-btn-lg">
                            Buka Sistem Gudang Militer
                        </Link>
                        <Link v-if="canLogin && $page.props.auth?.user"
                              :href="route('dashboard')" class="wms-btn-primary wms-btn-lg">
                            Masuk ke Dashboard
                        </Link>
                        <a v-if="canLogin && !$page.props.auth?.user && canRegister"
                           :href="route('register')" class="wms-btn-ghost wms-btn-lg">
                            Daftar Personil Baru
                        </a>
                    </div>
                </div>
            </section>

            <!-- 5. Military Asset Categories Grid -->
            <div ref="featuresRef">
                <MilitaryCategoryGrid
                    :categories="militaryAssetCategories"
                    :visible="featuresVisible"
                />
            </div>
        </main>

        <!-- 6. Footer -->
        <footer class="wms-footer" role="contentinfo">
            <span>WAREREX Military Logistics & Heavy Defense Arsenal Command</span>
            <span class="wms-footer-sep" aria-hidden="true">&bull;</span>
            <span>Running on Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})</span>
        </footer>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=IBM+Plex+Mono:wght@400;600;700&display=swap');

:root {
    --bg:           #000000;
    --border:       rgba(255,255,255,.12);
    --border-light: rgba(255,255,255,.24);
    --text:         #ffffff;
    --text-muted:   #a3a3a3;
    --text-dim:     #737373;
    --font:         'IBM Plex Sans', system-ui, sans-serif;
    --font-mono:    'IBM Plex Mono', monospace;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.wms-root {
    position: relative;
    min-height: 100vh;
    background: #000000;
    color: #ffffff;
    font-family: var(--font);
    display: flex;
    flex-direction: column;
    overflow-x: hidden;
}

.skip-link {
    position: absolute;
    top: -60px;
    left: 1rem;
    z-index: 200;
    background: #ffffff;
    color: #000000;
    padding: .5rem 1rem;
    font-weight: 600;
    border-radius: 4px;
    transition: top .15s;
}
.skip-link:focus { top: 1rem; }

/* ── Hero Tactical Center Layout ────────────────────────── */
.wms-main { flex: 1; }
.wms-hero-tactical {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1.5rem 1.5rem 2.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.wms-hero-header {
    max-width: 820px;
    margin: 1.5rem auto 0;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.wms-hero-label {
    display: inline-block;
    padding: .35rem .9rem;
    background: rgba(255,255,255,.08);
    color: #ffffff;
    border: 1px solid rgba(255,255,255,.25);
    border-radius: 999px;
    font-size: .75rem;
    font-family: monospace;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin-bottom: 1.25rem;
    opacity: 0;
    transform: translateY(8px);
    transition: opacity .3s, transform .3s;
}
.wms-hero-label.in { opacity: 1; transform: translateY(0); }

.wms-hero-heading {
    font-size: 3.1rem;
    line-height: 1.15;
    font-weight: 800;
    letter-spacing: -.03em;
    color: #fff;
    margin-bottom: 1.25rem;
    opacity: 0;
    transform: translateY(12px);
    transition: opacity .35s, transform .35s;
}
.wms-hero-heading.in { opacity: 1; transform: translateY(0); }
.wms-hero-accent {
    color: #ffffff;
    text-decoration: underline;
    text-decoration-color: rgba(255,255,255,.3);
    text-underline-offset: 6px;
}

.wms-hero-desc {
    font-size: 1.05rem;
    line-height: 1.65;
    color: #a3a3a3;
    margin-bottom: 2rem;
    max-width: 660px;
    opacity: 0;
    transform: translateY(12px);
    transition: opacity .35s, transform .35s;
}
.wms-hero-desc.in { opacity: 1; transform: translateY(0); }

.wms-hero-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    opacity: 0;
    transform: translateY(12px);
    transition: opacity .35s, transform .35s;
}
.wms-hero-actions.in { opacity: 1; transform: translateY(0); }

.wms-btn-primary {
    display: inline-flex;
    align-items: center;
    padding: .5rem 1.15rem;
    background: #ffffff;
    color: #000000;
    font-size: .88rem;
    font-weight: 700;
    border-radius: 6px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all .15s;
}
.wms-btn-primary:hover {
    background: #e5e5e5;
    box-shadow: 0 0 20px rgba(255,255,255,.4);
}

.wms-btn-ghost {
    display: inline-flex;
    align-items: center;
    padding: .5rem 1.15rem;
    border: 1px solid rgba(255,255,255,.2);
    color: #ffffff;
    font-size: .88rem;
    font-weight: 600;
    border-radius: 6px;
    text-decoration: none;
    background: transparent;
    transition: color .15s, border-color .15s, background .15s;
}
.wms-btn-ghost:hover {
    color: #000000;
    background: #ffffff;
    border-color: #ffffff;
}

.wms-btn-lg {
    padding: .75rem 1.4rem;
    font-size: .95rem;
}

/* Center Stage for Massive 3D Tank */
.wms-hero-tank-stage {
    width: 100%;
    max-width: 1240px;
    margin: 0 auto;
    opacity: 0;
    transform: scale(.95) translateY(12px);
    transition: opacity .45s cubic-bezier(.22,.68,0,1.15), transform .45s cubic-bezier(.22,.68,0,1.15);
}
.wms-hero-tank-stage.in {
    opacity: 1;
    transform: scale(1) translateY(0);
}

/* Footer */
.wms-footer {
    border-top: 1px solid rgba(255,255,255,.12);
    padding: 1.5rem;
    text-align: center;
    font-size: .8rem;
    color: #737373;
    display: flex;
    justify-content: center;
    gap: .75rem;
}

@media (max-width: 900px) {
    .wms-hero-heading { font-size: 2.25rem; }
}
</style>
