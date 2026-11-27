<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

// Modular Professional Warehouse Components
import WarehouseBackground from '@/Components/Warehouse/WarehouseBackground.vue';
import WarehouseHeaderBar from '@/Components/Warehouse/WarehouseHeaderBar.vue';
import WarehouseCategoryGrid from '@/Components/Warehouse/WarehouseCategoryGrid.vue';
import WarehouseInteractiveConsole from '@/Components/Warehouse/WarehouseInteractiveConsole.vue';

defineProps({
    canLogin: { type: Boolean, default: false },
    canRegister: { type: Boolean, default: false },
    laravelVersion: { type: String, required: true },
    phpVersion: { type: String, required: true },
});

const featuresRef = ref(null);
const featuresVisible = ref(true);

onMounted(() => {
    if (typeof IntersectionObserver !== 'undefined' && featuresRef.value) {
        const obs = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    featuresVisible.value = true;
                    obs.disconnect();
                }
            },
            { threshold: 0.1 }
        );
        obs.observe(featuresRef.value);
    }
});
</script>

<template>
    <Head title="WAREFIVE - Sistem Manajemen Gudang & Inventaris Terintegrasi" />

    <div class="wh-root">
        <a href="#main-content" class="skip-link">Loncat ke konten utama</a>

        <!-- 1. Ambient Background (Nordic Dim) -->
        <WarehouseBackground />

        <!-- 2. Header Navigation Bar (Nordic Dim) -->
        <WarehouseHeaderBar
            :can-login="canLogin"
            :can-register="canRegister"
        />

        <!-- Main Content -->
        <main id="main-content" class="wh-main welcome-page-enter">
            <!-- 3. Hero Section -->
            <section class="wh-hero" aria-labelledby="hero-heading">
                <div class="wh-hero-content">
                    <!-- Facility Badge Pill -->
                    <div class="wh-hero-pill">
                        <span class="w-2 h-2 rounded-full bg-[#88C0D0]"></span>
                        <span>HUB LOGISTIK WH-KRWNG BARAT &bull; ENTERPRISE WMS</span>
                    </div>

                    <!-- Main Headline -->
                    <h1 id="hero-heading" class="wh-hero-title">
                        Pengelolaan Inventaris & Tata Letak Rak Gudang Terintegrasi.
                    </h1>

                    <!-- Hero Subtitle -->
                    <p class="wh-hero-desc">
                        Platform Warehouse Management System modern untuk pemantauan kapasitas rak bertingkat secara real-time, pencatatan mutasi stok akurat, dan pemindaian barcode presisi tinggi.
                    </p>

                    <!-- Hero Call to Actions -->
                    <div class="wh-hero-actions">
                        <template v-if="canLogin">
                            <Link
                                v-if="$page.props.auth?.user"
                                :href="route('dashboard')"
                                class="wh-btn-hero-primary"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                Buka Dashboard Utama
                            </Link>

                            <template v-else>
                                <Link
                                    :href="route('login')"
                                    class="wh-btn-hero-primary"
                                >
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                    </svg>
                                    Masuk ke Sistem WMS
                                </Link>

                                <Link
                                    v-if="canRegister"
                                    :href="route('register')"
                                    class="wh-btn-hero-secondary"
                                >
                                    Daftar Personil Baru
                                </Link>
                            </template>
                        </template>
                    </div>

                    <!-- Technical Specifications Strip -->
                    <div class="wh-specs-strip">
                        <div class="wh-spec-chip">
                            <span class="wh-chip-label">Struktur Lokasi</span>
                            <span class="wh-chip-val">Aisle &bull; Bay &bull; Level</span>
                        </div>
                        <div class="wh-spec-chip">
                            <span class="wh-chip-label">Standar Barcode</span>
                            <span class="wh-chip-val">Code 128 & QR Code</span>
                        </div>
                        <div class="wh-spec-chip">
                            <span class="wh-chip-label">Pemindaian</span>
                            <span class="wh-chip-val">Kamera & USB Scanner</span>
                        </div>
                        <div class="wh-spec-chip">
                            <span class="wh-chip-label">Audit Keamanan</span>
                            <span class="wh-chip-val">Log Mutasi Real-Time</span>
                        </div>
                    </div>
                </div>

                <!-- Live Interactive Operations Console Showcase -->
                <div class="wh-hero-stage">
                    <WarehouseInteractiveConsole />
                </div>
            </section>

            <!-- 4. Feature Pillars Section -->
            <div ref="featuresRef">
                <WarehouseCategoryGrid :visible="featuresVisible" />
            </div>

            <!-- 5. Operational Workflow Section -->
            <section id="operations" class="wh-workflow-section" aria-labelledby="workflow-heading">
                <div class="wh-workflow-container">
                    <div class="text-center max-w-2xl mx-auto mb-14">
                        <span class="wh-section-badge">STANDAR OPERASIONAL PROSEDUR</span>
                        <h2 id="workflow-heading" class="wh-section-title">Alur Distribusi & Penyimpanan Barang</h2>
                        <p class="wh-section-subtitle">
                            Proses terstandarisasi untuk memastikan setiap barang yang masuk tercatat, ditempatkan pada slot yang sesuai, dan terpantau hingga waktu pengiriman.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Step 1 -->
                        <div class="wh-step-card">
                            <div class="wh-step-number">01</div>
                            <h3 class="wh-step-title">Penerimaan & Verifikasi (Inbound)</h3>
                            <p class="wh-step-desc">
                                Petugas memeriksa surat jalan barang masuk, memastikan kecocokan fisik, lalu mencetak label barcode identitas untuk barang dan pallet.
                            </p>
                        </div>

                        <!-- Step 2 -->
                        <div class="wh-step-card">
                            <div class="wh-step-number">02</div>
                            <h3 class="wh-step-title">Penempatan Rak (Putaway & Slotting)</h3>
                            <p class="wh-step-desc">
                                Operator memindai barcode rak tujuan (Aisle-Bay-Level) dan barcode barang, memastikan alokasi kapasitas rak tidak melebihi batas muat.
                            </p>
                        </div>

                        <!-- Step 3 -->
                        <div class="wh-step-card">
                            <div class="wh-step-number">03</div>
                            <h3 class="wh-step-title">Monitoring Stok (Stock Opname)</h3>
                            <p class="wh-step-desc">
                                Sistem memperbarui persentase keterisian rak secara otomatis. Pengawasan visual mempermudah audit fisik dan relokasi internal kapan saja.
                            </p>
                        </div>

                        <!-- Step 4 -->
                        <div class="wh-step-card">
                            <div class="wh-step-number">04</div>
                            <h3 class="wh-step-title">Pengeluaran & Dispatch (Outbound)</h3>
                            <p class="wh-step-desc">
                                Pengambilan barang sesuai order dengan validasi barcode keluar. Data kuantitas terpotong instan dan tercatat di riwayat mutasi.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 6. Rack Engineering & Technology Specs -->
            <section id="racks-tech" class="wh-specs-section" aria-labelledby="specs-heading">
                <div class="wh-specs-container">
                    <div class="text-center max-w-2xl mx-auto mb-14">
                        <span class="wh-section-badge">TEKNOLOGI & INFRASTRUKTUR</span>
                        <h2 id="specs-heading" class="wh-section-title">Spesifikasi Arsitektur Sistem Gudang</h2>
                        <p class="wh-section-subtitle">
                            Fondasi teknologi yang tangguh untuk menjamin keandalan data pergudangan pada operasional volume tinggi.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-7">
                        <div class="wh-spec-card">
                            <div class="wh-spec-icon">
                                <svg class="w-6 h-6 text-[#88C0D0]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <h3 class="wh-spec-card-title">Struktur Koordinat Rak Fisik</h3>
                            <p class="wh-spec-card-desc">
                                Format penomoran konsisten memudahkan penemuan barang oleh picker dan operator forklift. Meminimalisir waktu pencarian dan kekeliruan pengambilan barang di lorong gudang.
                            </p>
                            <div class="mt-4 pt-4 border-t border-[#4C566A]/60 text-xs font-mono text-[#A5B1C2]">
                                Dukungan: Selective Racks, Drive-in, Mezzanine
                            </div>
                        </div>

                        <div class="wh-spec-card">
                            <div class="wh-spec-icon">
                                <svg class="w-6 h-6 text-[#A3BE8C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                            </div>
                            <h3 class="wh-spec-card-title">Kompatibilitas Printer Thermal</h3>
                            <p class="wh-spec-card-desc">
                                Desain cetak stiker disesuaikan dengan dimensi label barcode industri 100x50 mm dan 50x30 mm. Kompatibel dengan printer label thermal ternama melalui ekspor PDF dan ZPL langsung.
                            </p>
                            <div class="mt-4 pt-4 border-t border-[#4C566A]/60 text-xs font-mono text-[#A5B1C2]">
                                Standar Format: Code 128, ISO/IEC 18004
                            </div>
                        </div>

                        <div class="wh-spec-card">
                            <div class="wh-spec-icon">
                                <svg class="w-6 h-6 text-[#81A1C1]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <h3 class="wh-spec-card-title">Integritas Data & Audit Trail</h3>
                            <p class="wh-spec-card-desc">
                                Setiap perpindahan stok, perubahan status rak, maupun kuantitas barang dicatat secara kronologis. Memberikan transparansi menyeluruh untuk pelaporan manajerial dan audit kepatuhan.
                            </p>
                            <div class="mt-4 pt-4 border-t border-[#4C566A]/60 text-xs font-mono text-[#A5B1C2]">
                                Keamanan: Role-Based Clearance & Timestamping
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 7. Call To Action Access Panel -->
            <section class="wh-cta-section" aria-label="Akses Sistem WMS">
                <div class="wh-cta-box">
                    <div class="max-w-2xl mx-auto text-center">
                        <span class="wh-section-badge">AKSES TERMINAL GUDANG</span>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-[#ECEFF4] mb-4">
                            Siap Mengoptimalkan Manajemen Gudang Anda?
                        </h2>
                        <p class="text-[#D8DEE9] text-sm sm:text-base mb-8 leading-relaxed">
                            Masuk ke dashboard WAREFIVE sekarang untuk mulai memantau kapasitas rak, memindai barcode, dan mengelola inventaris secara presisi.
                        </p>
                        <div class="flex flex-wrap items-center justify-center gap-4">
                            <Link
                                v-if="canLogin && $page.props.auth?.user"
                                :href="route('dashboard')"
                                class="wh-btn-hero-primary"
                            >
                                Buka Dashboard Utama
                            </Link>
                            <template v-else-if="canLogin">
                                <Link
                                    :href="route('login')"
                                    class="wh-btn-hero-primary"
                                >
                                    Masuk ke Sistem WMS
                                </Link>
                                <Link
                                    v-if="canRegister"
                                    :href="route('register')"
                                    class="wh-btn-hero-secondary"
                                >
                                    Daftar Personil Baru
                                </Link>
                            </template>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- 8. Footer (Nordic Dim) -->
        <footer class="wh-footer" role="contentinfo">
            <div class="wh-footer-inner">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-3 text-center sm:text-left">
                        <span class="wh-footer-logo">WAREFIVE<span class="text-[#88C0D0]">.</span></span>
                        <span class="text-xs text-[#A5B1C2]">
                            &copy; {{ new Date().getFullYear() }} <strong class="text-[#ECEFF4]">Hols</strong> &times; <strong class="text-[#88C0D0]">WAREFIVE</strong>. All rights reserved.
                        </span>
                    </div>

                    <div class="text-xs text-[#A5B1C2]">
                        Made by <span class="text-[#ECEFF4] font-medium">Creactivities</span> &bull; Fasilitas WH-KRWNG Barat
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap');

:root {
    --wh-bg: #2E3440;
    --wh-surface: #3B4252;
    --wh-surface-border: rgba(76, 86, 106, 0.6);
    --wh-text-heading: #ECEFF4;
    --wh-text-body: #A5B1C2;
    --wh-accent-primary: #5E81AC;
    --wh-accent-frost: #88C0D0;
}

*, *::before, *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

.wh-root {
    position: relative;
    min-height: 100vh;
    background: #2E3440;
    color: #ECEFF4;
    font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
    display: flex;
    flex-direction: column;
    overflow-x: hidden;
}

.skip-link {
    position: absolute;
    top: -60px;
    left: 1rem;
    z-index: 200;
    background: #5E81AC;
    color: #ECEFF4;
    padding: 0.5rem 1rem;
    font-weight: 700;
    border-radius: 8px;
    transition: top 0.15s ease;
    text-decoration: none;
}
.skip-link:focus {
    top: 1rem;
}

.wh-main {
    flex: 1;
    position: relative;
    z-index: 10;
}

/* Hero Section */
.wh-hero {
    max-width: 1240px;
    margin: 0 auto;
    padding: 4rem 1.5rem 5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.wh-hero-content {
    max-width: 840px;
    margin: 0 auto 3rem;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.wh-hero-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 1rem;
    background: rgba(136, 192, 208, 0.12);
    color: #88C0D0;
    border: 1px solid rgba(136, 192, 208, 0.25);
    border-radius: 9999px;
    font-size: 0.75rem;
    font-family: 'IBM Plex Mono', monospace;
    font-weight: 700;
    letter-spacing: 0.06em;
    margin-bottom: 1.75rem;
}

.wh-hero-title {
    font-size: 3.15rem;
    line-height: 1.2;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: #ECEFF4;
    margin-bottom: 1.5rem;
}

.wh-hero-desc {
    font-size: 1.15rem;
    line-height: 1.75;
    color: #D8DEE9;
    max-width: 720px;
    margin-bottom: 2.25rem;
}

.wh-hero-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 2.75rem;
}

.wh-btn-hero-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.85rem 1.75rem;
    background: #5E81AC;
    color: #ECEFF4;
    font-size: 0.95rem;
    font-weight: 700;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.15s ease;
    min-height: 48px;
    box-shadow: 0 4px 14px rgba(94, 129, 172, 0.25);
}

.wh-btn-hero-primary:hover {
    background: #81A1C1;
    box-shadow: 0 6px 20px rgba(94, 129, 172, 0.35);
    transform: translateY(-1px);
}

.wh-btn-hero-primary:focus-visible {
    outline: 2px solid #88C0D0;
    outline-offset: 2px;
}

.wh-btn-hero-secondary {
    display: inline-flex;
    align-items: center;
    padding: 0.85rem 1.75rem;
    border: 1px solid #4C566A;
    color: #ECEFF4;
    font-size: 0.95rem;
    font-weight: 600;
    border-radius: 10px;
    text-decoration: none;
    background: #3B4252;
    transition: all 0.15s ease;
    min-height: 48px;
}

.wh-btn-hero-secondary:hover {
    color: #ECEFF4;
    background: #434C5E;
    border-color: rgba(136, 192, 208, 0.4);
}

.wh-btn-hero-secondary:focus-visible {
    outline: 2px solid #88C0D0;
    outline-offset: 2px;
}

/* Technical Specifications Strip */
.wh-specs-strip {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 0.85rem;
    width: 100%;
}

.wh-spec-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    background: #3B4252;
    border: 1px solid rgba(76, 86, 106, 0.6);
    box-shadow: 0 2px 8px rgba(36, 41, 51, 0.3);
}

.wh-chip-label {
    font-size: 0.72rem;
    font-family: 'IBM Plex Mono', monospace;
    color: #A5B1C2;
    text-transform: uppercase;
}

.wh-chip-val {
    font-size: 0.82rem;
    font-weight: 600;
    color: #ECEFF4;
}

.wh-hero-stage {
    width: 100%;
}

/* Workflow Section */
.wh-workflow-section {
    padding: 5.5rem 1.5rem;
    background: #2E3440;
    border-top: 1px solid rgba(76, 86, 106, 0.6);
    border-bottom: 1px solid rgba(76, 86, 106, 0.6);
}

.wh-workflow-container {
    max-width: 1240px;
    margin: 0 auto;
}

.wh-section-badge {
    display: inline-block;
    padding: 0.35rem 0.95rem;
    background: rgba(136, 192, 208, 0.12);
    color: #88C0D0;
    border: 1px solid rgba(136, 192, 208, 0.25);
    border-radius: 8px;
    font-size: 0.72rem;
    font-family: 'IBM Plex Mono', monospace;
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

.wh-step-card {
    background: #3B4252;
    border: 1px solid rgba(76, 86, 106, 0.6);
    border-radius: 16px;
    padding: 1.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 4px 16px -2px rgba(36, 41, 51, 0.4);
}

.wh-step-card:hover {
    border-color: rgba(136, 192, 208, 0.4);
    transform: translateY(-2px);
    box-shadow: 0 12px 24px -6px rgba(36, 41, 51, 0.5);
}

.wh-step-number {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 1.4rem;
    font-weight: 800;
    color: #88C0D0;
}

.wh-step-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #ECEFF4;
    line-height: 1.35;
}

.wh-step-desc {
    font-size: 0.9rem;
    line-height: 1.65;
    color: #A5B1C2;
}

/* Specs Section */
.wh-specs-section {
    padding: 5.5rem 1.5rem;
    max-width: 1240px;
    margin: 0 auto;
}

.wh-specs-container {
    width: 100%;
}

.wh-spec-card {
    background: #3B4252;
    border: 1px solid rgba(76, 86, 106, 0.6);
    border-radius: 16px;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 4px 16px -2px rgba(36, 41, 51, 0.4);
}

.wh-spec-card:hover {
    border-color: rgba(136, 192, 208, 0.4);
    transform: translateY(-2px);
    box-shadow: 0 12px 24px -6px rgba(36, 41, 51, 0.5);
}

.wh-spec-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: #434C5E;
    border: 1px solid #4C566A;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
}

.wh-spec-card-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #ECEFF4;
    margin-bottom: 0.75rem;
}

.wh-spec-card-desc {
    font-size: 0.92rem;
    line-height: 1.65;
    color: #A5B1C2;
    flex: 1;
}

/* CTA Section */
.wh-cta-section {
    padding: 2rem 1.5rem 6rem;
    max-width: 1240px;
    margin: 0 auto;
    width: 100%;
}

.wh-cta-box {
    background: linear-gradient(145deg, #3B4252 0%, #2E3440 100%);
    border: 1px solid rgba(76, 86, 106, 0.8);
    border-radius: 20px;
    padding: 4rem 2rem;
    box-shadow: 0 20px 40px -15px rgba(36, 41, 51, 0.7);
}

/* Footer */
.wh-footer {
    position: relative;
    z-index: 10;
    border-top: 1px solid #3B4252;
    background: #242933;
    padding: 2.25rem 1.5rem;
    margin-top: auto;
}

.wh-footer-inner {
    max-width: 1240px;
    margin: 0 auto;
}

.wh-footer-logo {
    font-size: 1.15rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #ECEFF4;
}

@media (max-width: 900px) {
    .wh-hero {
        padding: 2.5rem 1rem 3.5rem;
    }
    .wh-hero-title {
        font-size: 2.25rem;
    }
    .wh-hero-desc {
        font-size: 1rem;
    }
    .wh-footer {
        margin-bottom: 5.5rem; /* Safe breathing room for mobile floating bottom bar */
    }
}

@keyframes welcomeEnter {
    0% {
        opacity: 0;
        transform: translateY(16px) scale(0.985);
        filter: blur(5px);
    }
    100% {
        opacity: 1;
        transform: none;
        filter: none;
    }
}

.welcome-page-enter {
    animation: welcomeEnter 0.48s cubic-bezier(0.22, 1, 0.36, 1);
}

@media (prefers-reduced-motion: reduce) {
    .welcome-page-enter {
        animation: none;
    }
}
</style>