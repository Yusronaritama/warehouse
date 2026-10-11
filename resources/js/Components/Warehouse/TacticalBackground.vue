<script setup>
// Tactical Military Schematic & Technical Blueprint Background
</script>

<template>
    <div class="wms-bg" aria-hidden="true">
        <div class="bg-schematic-grid"></div>
        <div class="bg-schematic-radar"></div>
        <div class="bg-schematic-crosshairs"></div>
        <div class="bg-glow bg-glow--tactical"></div>
        <div class="bg-noise"></div>
    </div>
</template>

<style scoped>
.wms-bg {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 0;
    background: #000000;
}

/* Schematic Blueprint Technical Grid */
.bg-schematic-grid {
    position: absolute;
    inset: 0;
    background-image: 
        /* Minor 20px grid */
        linear-gradient(to right, rgba(255, 255, 255, 0.035) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(255, 255, 255, 0.035) 1px, transparent 1px),
        /* Major 100px grid lines */
        linear-gradient(to right, rgba(255, 255, 255, 0.08) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(255, 255, 255, 0.08) 1px, transparent 1px);
    background-size: 20px 20px, 20px 20px, 100px 100px, 100px 100px;
    background-position: center center;
    mask-image: radial-gradient(circle at 50% 35%, black 40%, rgba(0, 0, 0, 0.5) 80%, transparent 100%);
    -webkit-mask-image: radial-gradient(circle at 50% 35%, black 40%, rgba(0, 0, 0, 0.5) 80%, transparent 100%);
}

/* Schematic Radial Blueprint Rings & Calibration Circles */
.bg-schematic-radar {
    position: absolute;
    top: 300px;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 900px;
    height: 900px;
    border-radius: 50%;
    border: 1px dashed rgba(255, 255, 255, 0.07);
    pointer-events: none;
}
.bg-schematic-radar::before {
    content: '';
    position: absolute;
    inset: 160px;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, 0.05);
}
.bg-schematic-radar::after {
    content: '';
    position: absolute;
    inset: 320px;
    border-radius: 50%;
    border: 1px dashed rgba(255, 255, 255, 0.08);
}

/* Schematic Technical Crosshairs & Corner Ticks */
.bg-schematic-crosshairs {
    position: absolute;
    inset: 0;
    background-image: 
        radial-gradient(circle at 20% 25%, rgba(255, 255, 255, 0.25) 1px, transparent 2px),
        radial-gradient(circle at 80% 25%, rgba(255, 255, 255, 0.25) 1px, transparent 2px),
        radial-gradient(circle at 50% 70%, rgba(255, 255, 255, 0.2) 1px, transparent 2px);
    background-size: 100% 100%;
}

.bg-glow--tactical {
    position: absolute;
    width: 900px;
    height: 600px;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    background: radial-gradient(circle, rgba(255, 255, 255, 0.07) 0%, transparent 70%);
    border-radius: 50%;
    filter: blur(120px);
}

.bg-noise {
    position: absolute;
    inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.035'/%3E%3C/svg%3E");
    opacity: .25;
    mix-blend-mode: overlay;
}
</style>
