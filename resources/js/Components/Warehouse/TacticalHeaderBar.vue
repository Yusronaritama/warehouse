<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    canLogin: { type: Boolean },
    canRegister: { type: Boolean },
    menuOpen: { type: Boolean, default: false },
});

const emit = defineEmits(['toggle-menu', 'close-menu']);
</script>

<template>
    <header class="wms-header wms-header--slide" role="banner">
        <div class="wms-header-inner">
            <a href="/" class="wms-brand" aria-label="Warerex Military Depot Home">
                <span class="wms-brand-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2L3 7v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-9-5z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 8v8m-4-4h8" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="wms-brand-name">WAREREX<span class="wms-brand-dot">.</span></span>
                <span class="wms-brand-tag">MILITARY DEPOT & LOGISTICS</span>
            </a>

            <nav class="wms-nav" aria-label="Primary navigation">
                <Link v-if="canLogin && $page.props.auth?.user"
                      :href="route('dashboard')" class="wms-btn-primary">
                    Buka Terminal Komando &rarr;
                </Link>
                <template v-if="canLogin && !$page.props.auth?.user">
                    <Link :href="route('login')" class="wms-nav-link">Autentikasi Petugas</Link>
                    <Link v-if="canRegister" :href="route('register')" class="wms-btn-primary">
                        Registrasi Personil
                    </Link>
                </template>
            </nav>

            <button v-if="canLogin" class="wms-hamburger"
                    :aria-expanded="menuOpen" aria-controls="mobile-menu"
                    aria-label="Toggle navigation menu" @click="emit('toggle-menu')">
                <span class="wms-bar" :class="{ 'bar-open-1': menuOpen }"></span>
                <span class="wms-bar" :class="{ 'bar-open-2': menuOpen }"></span>
                <span class="wms-bar" :class="{ 'bar-open-3': menuOpen }"></span>
            </button>
        </div>

        <div id="mobile-menu" class="wms-mobile-menu"
             :class="{ 'wms-mobile-menu--open': menuOpen }"
             role="dialog" aria-modal="true" aria-label="Navigation">
            <Link v-if="$page.props.auth?.user" :href="route('dashboard')"
                  class="wms-mobile-link wms-mobile-link--primary" @click="emit('close-menu')">
                Buka Terminal Komando
            </Link>
            <template v-if="!$page.props.auth?.user">
                <Link :href="route('login')" class="wms-mobile-link" @click="emit('close-menu')">Autentikasi Petugas</Link>
                <Link v-if="canRegister" :href="route('register')"
                      class="wms-mobile-link wms-mobile-link--primary" @click="emit('close-menu')">
                    Registrasi Personil
                </Link>
            </template>
        </div>
    </header>
</template>

<style scoped>
.wms-header {
    position: sticky;
    top: 0;
    z-index: 50;
    background: rgba(0, 0, 0, 0.92);
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(12px);
}
.wms-header--slide {
    animation: headerSlide .4s cubic-bezier(.22,.68,0,1.15) both;
}
@keyframes headerSlide {
    from { transform: translateY(-100%); opacity: 0; }
    to   { transform: translateY(0);     opacity: 1; }
}

.wms-header-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
}

.wms-brand {
    display: flex;
    align-items: center;
    gap: .75rem;
    text-decoration: none;
    color: #fff;
    flex-shrink: 0;
}
.wms-brand-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.2);
}
.wms-brand-name {
    font-size: 1.25rem;
    font-weight: 800;
    letter-spacing: -.03em;
    line-height: 1;
}
.wms-brand-dot { color: #ffffff; }
.wms-brand-tag {
    font-size: 10px;
    font-family: monospace;
    font-weight: 700;
    color: #000000;
    background: #ffffff;
    padding: 2px 7px;
    border-radius: 4px;
    letter-spacing: .06em;
}

.wms-nav { display: flex; align-items: center; gap: 1rem; }
.wms-nav-link {
    padding: .45rem .85rem;
    border-radius: 6px;
    font-size: .88rem;
    font-weight: 500;
    color: #a3a3a3;
    text-decoration: none;
    transition: color .15s, background .15s;
}
.wms-nav-link:hover { color: #fff; background: rgba(255,255,255,.08); }

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

.wms-hamburger {
    display: none;
    flex-direction: column;
    gap: 5px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 6px;
}
.wms-bar {
    width: 22px;
    height: 2px;
    background: #fff;
    transition: transform .2s, opacity .2s;
}
.bar-open-1 { transform: translateY(7px) rotate(45deg); }
.bar-open-2 { opacity: 0; }
.bar-open-3 { transform: translateY(-7px) rotate(-45deg); }

.wms-mobile-menu {
    display: none;
    padding: 1rem;
    background: #0d0d0d;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    flex-direction: column;
    gap: .75rem;
}
.wms-mobile-menu--open { display: flex; }
.wms-mobile-link {
    color: #a3a3a3;
    text-decoration: none;
    padding: .5rem;
    font-weight: 500;
}
.wms-mobile-link--primary {
    color: #ffffff;
    font-weight: 700;
}

@media (max-width: 900px) {
    .wms-nav { display: none; }
    .wms-hamburger { display: flex; }
}
</style>
