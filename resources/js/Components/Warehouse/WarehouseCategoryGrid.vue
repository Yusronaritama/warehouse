<script setup>
defineProps({
    visible: {
        type: Boolean,
        default: true,
    },
});

const features = [
    {
        title: 'Manajemen Rak & Multi-Tier Slotting',
        desc: 'Penataan tata letak gudang berbasis koordinat Aisle, Bay, dan Level. Pantau kapasitas aktual setiap slot rak secara visual untuk mencegah over-capacity dan mempercepat proses putaway.',
        badge: 'Multi-Tier Racking',
        icon: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
    },
    {
        title: 'Generator Barcode & Cetak Label',
        desc: 'Sistem pembuatan barcode Code-128 dan QR Code otomatis untuk setiap rak serta SKU barang. Siap dicetak langsung ke printer label thermal dengan standar ukuran industri.',
        badge: 'Thermal & Code-128',
        icon: 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z',
    },
    {
        title: 'Pemindaian Kamera & Handheld Scanner',
        desc: 'Dukungan pemindaian fleksibel menggunakan kamera perangkat smartphone, tablet, maupun barcode scanner USB/Bluetooth industri untuk operasional picking dan receiving cepat di lapangan.',
        badge: 'Live Scanner Ready',
        icon: 'M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z M15 13a3 3 0 11-6 0 3 3 0 016 0z',
    },
    {
        title: 'Mutasi Stok & Riwayat Audit Terintegrasi',
        desc: 'Pencatatan real-time untuk setiap pergerakan barang (Inbound, Outbound, Relokasi antar rak, dan Stock Opname). Setiap transaksi tercatat dalam audit log lengkap dengan identitas petugas.',
        badge: 'Audit Trail Real-Time',
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
    },
];
</script>

<template>
    <section id="features" class="wh-features-section" :class="{ 'wh-features-in': visible }" aria-label="Fitur Utama Sistem Gudang WAREFIVE">
        <div class="wh-features-header">
            <span class="wh-section-badge">KEMAMPUAN UTAMA PLATFORM</span>
            <h2 class="wh-section-title">Solusi Menyeluruh Pergudangan Modern</h2>
            <p class="wh-section-subtitle">
                Dirancang khusus untuk mengoptimalkan operasional gudang, memastikan akurasi data stok, dan mempercepat alur distribusi logistik secara terstruktur.
            </p>
        </div>

        <div class="wh-features-grid">
            <div
                v-for="(f, idx) in features"
                :key="f.title"
                class="wh-card"
                :style="{ '--delay': `${idx * 75}ms` }"
            >
                <div class="wh-card-top">
                    <div class="wh-card-icon" aria-hidden="true">
                        <svg class="w-5 h-5 text-[#88C0D0]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" :d="f.icon"/>
                        </svg>
                    </div>
                    <span class="wh-card-badge">{{ f.badge }}</span>
                </div>
                <h3 class="wh-card-title">{{ f.title }}</h3>
                <p class="wh-card-desc">{{ f.desc }}</p>
            </div>
        </div>
    </section>
</template>

<style scoped>
.wh-features-section {
    max-width: 1240px;
    margin: 0 auto;
    padding: 5rem 1.5rem;
}

.wh-features-header {
    text-align: center;
    max-width: 760px;
    margin: 0 auto 3.5rem;
}

.wh-section-badge {
    display: inline-block;
    padding: 0.35rem 0.95rem;
    background: rgba(136, 192, 208, 0.12);
    color: #88C0D0;
    border: 1px solid rgba(136, 192, 208, 0.25);
    border-radius: 8px;
    font-size: 0.72rem;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-weight: 700;
    letter-spacing: 0.08em;
    margin-bottom: 1rem;
}

.wh-section-title {
    font-size: 2.15rem;
    font-weight: 800;
    letter-spacing: -0.025em;
    color: #ECEFF4;
    margin-bottom: 1rem;
    line-height: 1.25;
}

.wh-section-subtitle {
    font-size: 1.05rem;
    line-height: 1.7;
    color: #A5B1C2;
}

.wh-features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.75rem;
}

.wh-card {
    background: #3B4252;
    border: 1px solid rgba(76, 86, 106, 0.6);
    border-radius: 16px;
    padding: 2rem;
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 4px 20px -4px rgba(36, 41, 51, 0.5);
}

.wh-card:hover {
    border-color: rgba(136, 192, 208, 0.5);
    transform: translateY(-2px);
    box-shadow: 0 16px 30px -8px rgba(36, 41, 51, 0.7);
}

.wh-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.25rem;
}

.wh-card-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #434C5E;
    border: 1px solid #4C566A;
    display: flex;
    align-items: center;
    justify-content: center;
}

.wh-card-badge {
    font-size: 0.7rem;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-weight: 700;
    color: #88C0D0;
    background: #2E3440;
    border: 1px solid rgba(76, 86, 106, 0.6);
    padding: 0.3rem 0.65rem;
    border-radius: 6px;
}

.wh-card-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #ECEFF4;
    margin-bottom: 0.75rem;
    line-height: 1.35;
}

.wh-card-desc {
    font-size: 0.92rem;
    line-height: 1.65;
    color: #A5B1C2;
}

@media (max-width: 768px) {
    .wh-features-section {
        padding: 3.5rem 1.25rem;
    }
    .wh-section-title {
        font-size: 1.8rem;
    }
    .wh-card {
        padding: 1.5rem;
    }
}
</style>