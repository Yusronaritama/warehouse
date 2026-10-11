<script setup>
import { onMounted } from 'vue';

const props = defineProps({
    active: { type: Boolean, default: true },
    fading: { type: Boolean, default: false },
    progress: { type: Number, default: 0 },
    stepIndex: { type: Number, default: 0 },
    statusText: { type: String, default: '' },
});

const emit = defineEmits(['skip']);

function handleSkip() {
    emit('skip');
}
</script>

<template>
    <!-- Ultra-Cinematic Tactical Opening Boot Overlay -->
    <div
        v-if="active"
        class="tactical-intro-overlay"
        :class="{ 'intro-fading': fading }"
        aria-live="polite"
    >
        <!-- Cinematic Letterbox Scope (Top & Bottom Bars) -->
        <div class="cine-letterbox cine-letterbox-top">
            <div class="cine-bar-content font-mono">
                <span>REC ● MIL-OPS RECORDING</span>
                <span>SEC-CODE: 994-ALPHA-01</span>
                <span>LOC: 06°18'S 107°17'E (KARAWANG)</span>
            </div>
        </div>
        <div class="cine-letterbox cine-letterbox-bottom">
            <div class="cine-bar-content font-mono">
                <span>WAREREX ARSENAL DEPOT COMMAND SYSTEM</span>
                <span>FPS: 60.0 // RAW DATA LINK</span>
                <span>STATUS: STANDBY</span>
            </div>
        </div>

        <!-- Cinema Optical Vignette & Radar Grid Background -->
        <div class="intro-grid-bg"></div>
        <div class="intro-vignette"></div>
        <div class="intro-radar-sweep"></div>
        <div class="intro-scanline"></div>

        <!-- HUD Corner Brackets & Target Crosshairs -->
        <div class="hud-frame">
            <div class="hud-corner hud-corner--tl"></div>
            <div class="hud-corner hud-corner--tr"></div>
            <div class="hud-corner hud-corner--bl"></div>
            <div class="hud-corner hud-corner--br"></div>

            <div class="hud-telemetry hud-telemetry--left font-mono">
                <div class="telemetry-row"><span class="text-neutral-500">SYS.VOLT:</span> <span>24.8V STABLE</span></div>
                <div class="telemetry-row"><span class="text-neutral-500">CORE.TEMP:</span> <span>34°C OPTIMAL</span></div>
                <div class="telemetry-row"><span class="text-neutral-500">BANDWIDTH:</span> <span>10.4 GBPS GIGABIT</span></div>
                <div class="telemetry-row"><span class="text-neutral-500">FIREWALL:</span> <span class="text-emerald-400">ACTIVE AES-512</span></div>
            </div>

            <div class="hud-telemetry hud-telemetry--right font-mono">
                <div class="telemetry-row"><span class="text-neutral-500">ARMOR-1:</span> <span>LEOPARD 2A6 [READY]</span></div>
                <div class="telemetry-row"><span class="text-neutral-500">ARMOR-2:</span> <span>CHALLENGER 2 [READY]</span></div>
                <div class="telemetry-row"><span class="text-neutral-500">AMMO CLS:</span> <span>120MM APFSDS/HE</span></div>
                <div class="telemetry-row"><span class="text-neutral-500">DEPOT ID:</span> <span>WH-KRWNG-01</span></div>
            </div>
        </div>

        <div class="intro-content">
            <!-- Military Crest / Icon with Rotating Reticle Ring -->
            <div class="intro-crest-wrap">
                <div class="crest-reticle-ring"></div>
                <div class="intro-crest">
                    <svg width="46" height="46" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2L3 7v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-9-5z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 8v8m-4-4h8" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>

            <!-- Terminal Classification Badge -->
            <div class="intro-badge font-mono">
                <span class="live-pulse-dot"></span>
                DEFENSE ARSENAL LOGISTICS &bull; LEVEL-4 CLEARANCE
            </div>

            <h2 class="intro-title font-mono tracking-widest">
                WAREREX ARSENAL
            </h2>

            <div class="intro-facility font-mono text-neutral-400">
                TACTICAL HEAVY ARMOR DEPOT &bull; FACILITY WH-KRWNG BARAT
            </div>

            <!-- Terminal Log Typing Display (High Tech Command Prompt) -->
            <div class="intro-terminal-box">
                <div class="terminal-header font-mono">
                    <span class="text-neutral-500">TERMINAL_EXEC // RUN_BOOT_LOG</span>
                    <span class="text-neutral-400">STEP [0{{ stepIndex + 1 }}/05]</span>
                </div>
                <div class="terminal-body font-mono">
                    <span class="prompt-symbol font-bold">&gt;&gt;</span>
                    <span class="typing-text">{{ statusText }}</span>
                    <span class="cursor-blink">_</span>
                </div>
            </div>

            <!-- Precision Progress Bar -->
            <div class="intro-progress-wrap">
                <div class="intro-progress-bar" :style="{ width: `${progress}%` }">
                    <div class="progress-glow-head"></div>
                </div>
            </div>

            <div class="intro-footer-row font-mono text-xs">
                <span class="text-neutral-400">
                    CALIBRATING 3D MODEL TELEMETRY... 
                    <span class="text-white font-bold">{{ progress }}%</span>
                </span>
                <button @click="handleSkip" class="intro-skip-btn">
                    [LEWATI OPENING &rarr;]
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Ultra-Cinematic Tactical Opening Boot Overlay Styles */
.tactical-intro-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: #000000;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    transition: opacity 0.65s cubic-bezier(0.16, 1, 0.3, 1), transform 0.65s cubic-bezier(0.16, 1, 0.3, 1), filter 0.65s ease;
}
.tactical-intro-overlay.intro-fading {
    opacity: 0;
    filter: blur(8px) brightness(1.8);
    transform: scale(1.06);
    pointer-events: none;
}

/* Cinematic Anamorphic Letterbox Scope */
.cine-letterbox {
    position: absolute;
    left: 0;
    right: 0;
    height: 44px;
    background: #000000;
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
    z-index: 10;
    display: flex;
    align-items: center;
    padding: 0 2rem;
}
.cine-letterbox-top {
    top: 0;
}
.cine-letterbox-bottom {
    bottom: 0;
    border-bottom: none;
    border-top: 1px solid rgba(255, 255, 255, 0.15);
}
.cine-bar-content {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11px;
    color: rgba(255, 255, 255, 0.45);
    letter-spacing: 0.12em;
}

/* Background Atmosphere: Vignette, Grid & Radar Sweep */
.intro-vignette {
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at center, transparent 35%, rgba(0, 0, 0, 0.95) 90%);
    pointer-events: none;
    z-index: 1;
}

.intro-grid-bg {
    position: absolute;
    inset: 0;
    background-image: 
        linear-gradient(rgba(255, 255, 255, 0.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.035) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
}

.intro-radar-sweep {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 700px;
    height: 700px;
    margin-top: -350px;
    margin-left: -350px;
    border-radius: 50%;
    background: conic-gradient(from 0deg, rgba(255, 255, 255, 0.06) 0deg, transparent 55deg, transparent 360deg);
    animation: radarSpin 6s linear infinite;
    pointer-events: none;
    will-change: transform;
    transform: translateZ(0);
}
@keyframes radarSpin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.intro-scanline {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 12px;
    background: linear-gradient(180deg, transparent, rgba(255, 255, 255, 0.35), transparent);
    animation: scanline 3.2s linear infinite;
    pointer-events: none;
    z-index: 2;
}
@keyframes scanline {
    0% { transform: translateY(-100px); }
    100% { transform: translateY(105vh); }
}

/* HUD Telemetry Frame & Corner Brackets */
.hud-frame {
    position: absolute;
    inset: 52px 24px;
    pointer-events: none;
    z-index: 3;
}
.hud-corner {
    position: absolute;
    width: 24px;
    height: 24px;
    border-color: rgba(255, 255, 255, 0.7);
}
.hud-corner--tl { top: 0; left: 0; border-top: 2px solid; border-left: 2px solid; }
.hud-corner--tr { top: 0; right: 0; border-top: 2px solid; border-right: 2px solid; }
.hud-corner--bl { bottom: 0; left: 0; border-bottom: 2px solid; border-left: 2px solid; }
.hud-corner--br { bottom: 0; right: 0; border-bottom: 2px solid; border-right: 2px solid; }

.hud-telemetry {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    font-size: 11px;
    line-height: 1.8;
    color: rgba(255, 255, 255, 0.7);
    letter-spacing: 0.08em;
}
.hud-telemetry--left {
    left: 12px;
    text-align: left;
}
.hud-telemetry--right {
    right: 12px;
    text-align: right;
}
.telemetry-row {
    display: flex;
    gap: 8px;
    align-items: center;
}
@media (max-width: 900px) {
    .hud-telemetry { display: none; }
}

/* Center Content & Rotating Crest Reticle */
.intro-content {
    position: relative;
    z-index: 4;
    max-width: 640px;
    width: 90%;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1rem 0;
}

.intro-crest-wrap {
    position: relative;
    width: 104px;
    height: 104px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.25rem;
}
.crest-reticle-ring {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 1.5px dashed rgba(255, 255, 255, 0.4);
    animation: reticleSpin 8s linear infinite;
}
@keyframes reticleSpin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.intro-crest {
    width: 76px;
    height: 76px;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.08);
    border: 1.5px solid rgba(255, 255, 255, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 35px rgba(255, 255, 255, 0.2);
    animation: crestPulse 2.2s infinite ease-in-out;
}
@keyframes crestPulse {
    0%, 100% { transform: scale(1); box-shadow: 0 0 25px rgba(255, 255, 255, 0.2); }
    50% { transform: scale(1.05); box-shadow: 0 0 50px rgba(255, 255, 255, 0.4); }
}

.intro-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    letter-spacing: 0.14em;
    font-weight: 700;
    color: #ffffff;
    padding: 5px 16px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 999px;
    margin-bottom: 0.85rem;
    backdrop-filter: blur(8px);
}
.live-pulse-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow: 0 0 8px #ffffff;
    animation: blink 1.2s infinite ease-in-out;
}
@keyframes blink {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.3; transform: scale(0.8); }
}

.intro-title {
    font-size: 2.2rem;
    font-weight: 900;
    color: #ffffff;
    letter-spacing: 0.15em;
    margin-bottom: 0.4rem;
    text-shadow: 0 0 20px rgba(255, 255, 255, 0.4);
}

.intro-facility {
    font-size: 11px;
    letter-spacing: 0.1em;
    margin-bottom: 1.75rem;
    color: #a3a3a3;
}

/* Terminal Console Box */
.intro-terminal-box {
    width: 100%;
    background: rgba(8, 8, 8, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 10px;
    padding: 12px 18px;
    text-align: left;
    margin-bottom: 1.25rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8), inset 0 0 15px rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(12px);
}
.terminal-header {
    display: flex;
    justify-content: space-between;
    font-size: 10px;
    letter-spacing: 0.08em;
    padding-bottom: 8px;
    margin-bottom: 8px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.terminal-body {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: #ffffff;
}
.prompt-symbol {
    color: #ffffff;
    letter-spacing: -0.05em;
}
.typing-text {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.cursor-blink {
    color: #ffffff;
    font-weight: bold;
    animation: cursorBlink 0.7s infinite;
}
@keyframes cursorBlink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0; }
}

/* Precision Progress Bar with Glowing Head */
.intro-progress-wrap {
    width: 100%;
    height: 7px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 999px;
    overflow: hidden;
    margin-bottom: 0.85rem;
    border: 1px solid rgba(255, 255, 255, 0.15);
}
.intro-progress-bar {
    position: relative;
    height: 100%;
    background: #ffffff;
    box-shadow: 0 0 14px rgba(255, 255, 255, 0.9);
    transition: width 0.45s cubic-bezier(0.16, 1, 0.3, 1);
}
.progress-glow-head {
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    width: 10px;
    background: #ffffff;
    box-shadow: 0 0 12px 3px rgba(255, 255, 255, 0.9);
}

/* Footer Row & Skip Button */
.intro-footer-row {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 4px;
}
.intro-skip-btn {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #ffffff;
    padding: 4px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-weight: 600;
    letter-spacing: 0.06em;
}
.intro-skip-btn:hover {
    background: #ffffff;
    color: #000000;
    box-shadow: 0 0 15px rgba(255, 255, 255, 0.4);
}
</style>
