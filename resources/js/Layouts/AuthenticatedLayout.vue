<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link } from '@inertiajs/vue3';

const showingMobileMenu = ref(false);
const activeTab = ref('dashboard');
</script>

<template>
    <div class="min-h-screen bg-slate-900 text-slate-100 flex flex-col font-sans selection:bg-amber-500 selection:text-white">
        <!-- Top Warehouse Navigation Bar -->
        <header class="border-b border-slate-800 bg-slate-950/80 sticky top-0 z-40 backdrop-blur-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Brand & Facility Selector -->
                    <div class="flex items-center gap-6">
                        <Link :href="route('dashboard')" class="flex items-center gap-2">
                            <ApplicationLogo />
                        </Link>

                        <div class="hidden md:flex items-center gap-2 pl-4 border-l border-slate-800 text-xs">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-medium">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                {{ $page.props.facility?.name || 'WH-KRWNG Barat' }} ({{ $page.props.facility?.status || 'Online' }})
                            </span>
                            <span class="text-slate-400">
                                Rack Capacity:
                                <span class="font-mono font-semibold" :class="($page.props.facility?.actualRackCapacity || 0) > 0 ? 'text-amber-400' : 'text-slate-300'">
                                    {{ $page.props.facility?.actualRackCapacity ?? 0 }}%
                                </span>
                            </span>
                        </div>
                    </div>

                    <!-- Main Navigation Links -->
                    <nav class="hidden md:flex items-center gap-1">
                        <Link
                            :href="route('dashboard')"
                            class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
                            :class="route().current('dashboard') ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            Inventory Overview
                        </Link>

                        <a
                            href="#inventory-table"
                            class="px-3.5 py-2 rounded-lg text-sm font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition-colors flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            Item & Asset Master
                        </a>

                        <a
                            href="#stock-movement"
                            class="px-3.5 py-2 rounded-lg text-sm font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition-colors flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                            Stock In / Out
                        </a>

                        <a
                            href="#rack-locations"
                            class="px-3.5 py-2 rounded-lg text-sm font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition-colors flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                            Rack Zones
                        </a>
                        <Link
                            :href="route('logs.index')"
                            class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
                            :class="route().current('logs.index') ? 'bg-blue-500/10 text-blue-400 border border-blue-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Log Aktivitas Sistem
                        </Link>
                    </nav>

                    <!-- User and Actions -->
                    <div class="hidden sm:flex items-center gap-3">
                        <div class="relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2.5 px-3 py-1.5 rounded-lg border border-slate-700 bg-slate-800/80 text-sm font-medium text-slate-200 hover:bg-slate-700/80 focus:outline-none transition-colors"
                                    >
                                        <div class="h-6 w-6 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-xs uppercase border border-amber-500/30">
                                            {{ $page.props.auth.user.name.charAt(0) }}
                                        </div>
                                        <span>{{ $page.props.auth.user.name }}</span>
                                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </template>

                                <template #content>
                                    <div class="bg-slate-900 border border-slate-800 rounded-md py-1 shadow-xl">
                                        <div class="px-4 py-2 border-b border-slate-800 text-xs text-slate-400">
                                            Role: <span class="text-amber-400 font-semibold">Warehouse Supervisor</span>
                                        </div>
                                        <DropdownLink :href="route('profile.edit')" class="text-slate-300 hover:bg-slate-800">
                                            Profile Settings
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button" class="text-rose-400 hover:bg-slate-800">
                                            Log Out
                                        </DropdownLink>
                                    </div>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <!-- Mobile Hamburger -->
                    <div class="flex items-center sm:hidden">
                        <button
                            @click="showingMobileMenu = !showingMobileMenu"
                            class="p-2 rounded-md text-slate-400 hover:text-slate-200 hover:bg-slate-800 focus:outline-none"
                        >
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path v-if="!showingMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Dropdown Menu -->
            <div v-show="showingMobileMenu" class="sm:hidden border-t border-slate-800 bg-slate-950 px-4 pt-3 pb-4 space-y-2">
                <Link
                    :href="route('dashboard')"
                    class="block px-3 py-2 rounded-md text-base font-medium text-slate-200 hover:bg-slate-800"
                    @click="showingMobileMenu = false"
                >
                    Dashboard
                </Link>
                <a
                    href="#inventory-table"
                    class="block px-3 py-2 rounded-md text-base font-medium text-slate-400 hover:bg-slate-800"
                    @click="showingMobileMenu = false"
                >
                    Item & Asset Master
                </a>
                <Link
                    :href="route('logs.index')"
                    class="block px-3 py-2 rounded-md text-base font-medium text-slate-400 hover:bg-slate-800"
                    @click="showingMobileMenu = false"
                >
                    Log Aktivitas Sistem
                </Link>
                <div class="pt-3 border-t border-slate-800">
                    <div class="text-sm font-medium text-slate-300 px-3">{{ $page.props.auth.user.name }}</div>
                    <div class="text-xs text-slate-500 px-3">{{ $page.props.auth.user.email }}</div>
                    <div class="mt-2 space-y-1">
                        <Link :href="route('profile.edit')" class="block px-3 py-1.5 text-sm text-slate-400 hover:bg-slate-800 rounded">Profile</Link>
                        <Link :href="route('logout')" method="post" as="button" class="block w-full text-left px-3 py-1.5 text-sm text-rose-400 hover:bg-slate-800 rounded">Log Out</Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Optional Header Slot -->
        <header v-if="$slots.header" class="border-b border-slate-800/80 bg-slate-900/60">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <slot />
        </main>

        <!-- System Footer -->
        <footer class="border-t border-slate-800/60 py-4 bg-slate-950/40 text-center text-xs text-slate-500">
            <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
                <div>WAREREX Asset & Warehouse Operations Management &bull; v2.4.0</div>
                <div class="flex items-center gap-4">
                    <span>Database: Connected</span>
                    <span>Scanner: Ready (HID/USB)</span>
                    <span class="text-amber-500 font-mono">NODE-01</span>
                </div>
            </div>
        </footer>
    </div>
</template>
