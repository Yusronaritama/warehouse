<script setup>
defineProps({
    categories: {
        type: Array,
        required: true,
    },
    visible: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <!-- Military Arsenal & Inventory Categories -->
    <section class="wms-resources"
             :class="{ 'resources-in': visible }"
             aria-label="Katalog Aset Militer & Alutsista">

        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="wms-section-title !mb-1">Klasifikasi Suku Cadang & Alutsista Militer</h2>
                <p class="text-xs sm:text-sm text-neutral-400">Pengawasan inventaris pertahanan dan suku cadang alutsista di Depot Logistik Karawang Barat.</p>
            </div>
        </div>

        <div class="wms-resource-grid">
            <div
                v-for="(cat, idx) in categories"
                :key="cat.title"
                class="wms-res res-item"
                :style="{ '--d': `${idx * 80}ms` }"
            >
                <div class="flex items-center justify-between mb-3">
                    <div class="wms-res-icon" aria-hidden="true">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="cat.icon"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-white/10 text-white border border-white/20">
                        {{ cat.badge }}
                    </span>
                </div>
                <div class="wms-res-body">
                    <h3 class="wms-res-title">{{ cat.title }}</h3>
                    <p class="wms-res-desc">{{ cat.desc }}</p>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.wms-resources {
    max-width: 1200px;
    margin: 0 auto;
    padding: 3rem 1.5rem 5rem;
}
.wms-section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 2rem;
    letter-spacing: -.02em;
}

.wms-resource-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1.25rem;
}

.wms-res {
    background: #0d0d0d;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 10px;
    padding: 1.5rem;
    transition: transform .2s, border-color .2s;
    opacity: 0;
    transform: translateY(16px);
}
.resources-in .wms-res {
    opacity: 1;
    transform: translateY(0);
    transition: opacity .4s var(--d), transform .4s var(--d);
}
.wms-res:hover {
    border-color: rgba(255, 255, 255, 0.45);
    transform: translateY(-2px);
}

.wms-res-icon {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.08);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
}
.wms-res-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: .45rem;
}
.wms-res-desc {
    font-size: .85rem;
    line-height: 1.55;
    color: #a3a3a3;
}
</style>
