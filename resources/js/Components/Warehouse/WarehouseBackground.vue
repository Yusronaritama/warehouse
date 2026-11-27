<script setup>
// Warehouse Nordic Dim Architectural Ambient Background
</script>

<template>
    <div class="wh-bg" aria-hidden="true">
        <!-- Soft ambient illumination in cool Nordic Dim tones -->
        <div class="wh-ambient-glow-top"></div>
        <div class="wh-ambient-glow-center"></div>

        <!-- Very subtle structural grid (low contrast, easy on the eyes) -->
        <div class="wh-bg-grid"></div>
    </div>
</template>

<style scoped>
.wh-bg {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 0;
    background: #2E3440; /* Nordic Dim base: cool, dark polar night */
    overflow: hidden;
}

/* Gentle top glow (Frost Sky #88C0D0) */
.wh-ambient-glow-top {
    position: absolute;
    top: -160px;
    left: 50%;
    transform: translateX(-50%);
    width: 900px;
    height: 480px;
    background: radial-gradient(circle, rgba(136, 192, 208, 0.08) 0%, rgba(67, 76, 94, 0.05) 50%, transparent 80%);
    border-radius: 50%;
    filter: blur(90px);
}

/* Subtle accent ambient light (#5E81AC) */
.wh-ambient-glow-center {
    position: absolute;
    top: 30%;
    right: -100px;
    width: 600px;
    height: 500px;
    background: radial-gradient(circle, rgba(94, 129, 172, 0.06) 0%, transparent 70%);
    border-radius: 50%;
    filter: blur(100px);
}

/* Subtle grid line to provide spatial depth without visual noise */
.wh-bg-grid {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(to right, rgba(216, 222, 233, 0.03) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(216, 222, 233, 0.03) 1px, transparent 1px);
    background-size: 40px 40px;
    background-position: center top;
    mask-image: radial-gradient(ellipse 90% 70% at 50% 30%, #000 30%, transparent 85%);
    -webkit-mask-image: radial-gradient(ellipse 90% 70% at 50% 30%, #000 30%, transparent 85%);
}
</style>
