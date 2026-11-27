<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import LiveCameraScanner from '@/Components/Warehouse/LiveCameraScanner.vue';

const props = defineProps({
    isScanning: {
        type: Boolean,
        default: false,
    },
    scanFeedback: {
        type: Object,
        default: null,
    },
    scanError: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['scan', 'openMutation']);

const scanInput = ref('');
const scanInputRef = ref(null);
const showCameraScanner = ref(false);

function handleCameraScanned(code) {
    scanInput.value = code;
    emit('scan', code);
}

function handleScan() {
    if (!scanInput.value.trim()) return;
    emit('scan', scanInput.value.trim());
}

function quickFill(code) {
    scanInput.value = code;
    emit('scan', code);
}

function focus() {
    scanInputRef.value?.focus();
}

function clear() {
    scanInput.value = '';
}

function getCategoryBadge(category) {
    switch (category) {
        case 'Bahan Baku':
            return 'bg-[#81A1C1]/15 text-[#81A1C1] border-[#81A1C1]/30';
        case 'Painting':
            return 'bg-[#B48EAD]/15 text-[#B48EAD] border-[#B48EAD]/30';
        case 'Masking':
            return 'bg-[#EBCB8B]/15 text-[#EBCB8B] border-[#EBCB8B]/30';
        case 'Buffing':
            return 'bg-[#A3BE8C]/15 text-[#A3BE8C] border-[#A3BE8C]/30';
        case 'Packaging':
            return 'bg-[#88C0D0]/15 text-[#88C0D0] border-[#88C0D0]/30';
        case 'Heavy Equipment':
            return 'bg-[#EBCB8B]/15 text-[#EBCB8B] border-[#EBCB8B]/30';
        case 'Material Handlers':
            return 'bg-[#81A1C1]/15 text-[#81A1C1] border-[#81A1C1]/30';
        case 'Safety Gear':
            return 'bg-[#A3BE8C]/15 text-[#A3BE8C] border-[#A3BE8C]/30';
        case 'Electronics':
            return 'bg-[#B48EAD]/15 text-[#B48EAD] border-[#B48EAD]/30';
        case 'Spare Parts':
            return 'bg-[#88C0D0]/15 text-[#88C0D0] border-[#88C0D0]/30';
        default:
            return 'bg-[#434C5E]/60 text-[#D8DEE9] border-[#4C566A]/60';
    }
}

defineExpose({
    focus,
    clear,
    setInput: (val) => {
        scanInput.value = val;
    },
});
</script>

<template>
    <div class="bg-[#3B4252] border border-[#4C566A]/60 rounded-xl p-5 shadow-lg">
        <div class="flex items-center justify-between pb-3 border-b border-[#4C566A]/60">
            <div class="flex items-center gap-2">
                <span class="p-1 bg-[#434C5E] text-[#88C0D0] border border-[#4C566A] rounded">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01"/>
                    </svg>
                </span>
                <h3 class="text-sm font-bold text-[#ECEFF4]">Simulator Scanner Cepat Rak</h3>
            </div>
            <span class="text-[11px] text-[#88C0D0] font-mono">Input Barcode / USB Scanner</span>
        </div>

        <form @submit.prevent="handleScan" class="mt-3 flex gap-2">
            <input
                ref="scanInputRef"
                v-model="scanInput"
                type="text"
                placeholder="Ketik atau scan barcode (e.g. BC-A01-1024 atau RAK A-01)..."
                class="flex-1 bg-[#2E3440] border border-[#4C566A] rounded-lg px-3 py-2 text-xs text-[#ECEFF4] placeholder-[#8C96A9] font-mono uppercase focus:border-[#88C0D0] focus:ring-1 focus:ring-[#88C0D0]"
            />
            <button
                type="button"
                @click="showCameraScanner = true"
                class="px-3 py-2 bg-[#434C5E] hover:bg-[#4C566A] text-[#88C0D0] border border-[#4C566A] rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm active:scale-95"
                title="Pindai menggunakan kamera HP"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="hidden sm:inline">Kamera HP</span>
            </button>
            <button
                type="submit"
                :disabled="isScanning"
                class="px-4 py-2 bg-[#5E81AC] hover:bg-[#81A1C1] text-[#ECEFF4] rounded-lg text-xs font-bold transition-all disabled:opacity-50 flex items-center gap-1.5 shadow-sm active:scale-95"
            >
                <svg v-if="isScanning" class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>{{ isScanning ? 'Memindai...' : 'Scan' }}</span>
            </button>
        </form>

        <!-- Scan Feedback Result -->
        <div v-if="scanFeedback" class="mt-3 p-3 bg-[#2E3440] border border-[#A3BE8C]/40 rounded-lg text-xs text-[#ECEFF4]">
            <div class="font-bold flex items-center justify-between pb-1 border-b border-[#4C566A]/60">
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-[#A3BE8C]"></span>
                    <span>Scan Sukses: Rak [{{ scanFeedback.barcode?.rack_code }}]</span>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('racks.show', scanFeedback.barcode ? scanFeedback.barcode.rack_code : '')"
                        class="font-mono text-[11px] bg-[#5E81AC] hover:bg-[#81A1C1] text-[#ECEFF4] font-bold px-2.5 py-0.5 rounded shadow-xs transition-all flex items-center gap-1"
                    >
                        <span>Kelola Rak & CRUD &rarr;</span>
                    </Link>
                    <button
                        type="button"
                        @click="emit('openMutation', scanFeedback.barcode, scanFeedback.items)"
                        class="font-mono text-[11px] bg-[#434C5E] hover:bg-[#4C566A] text-[#ECEFF4] border border-[#4C566A] font-bold px-2 py-0.5 rounded shadow-xs transition-all"
                    >
                        Popup Mutasi &rarr;
                    </button>
                </div>
            </div>
            <div class="text-[#A5B1C2] mt-1.5 flex items-center justify-between">
                <span class="truncate max-w-[280px]">{{ scanFeedback.barcode?.label }}</span>
                <span class="text-[#A3BE8C] font-mono font-bold">{{ scanFeedback.items?.length || 0 }} SKU Tersimpan</span>
            </div>

            <!-- Structured List of Items inside scanned rack -->
            <div v-if="scanFeedback.items && scanFeedback.items.length > 0" class="mt-2 space-y-1.5">
                <div
                    v-for="item in scanFeedback.items"
                    :key="item.id"
                    class="p-2 rounded bg-[#3B4252] border border-[#4C566A]/60 flex items-center justify-between text-xs"
                >
                    <div class="flex flex-col">
                        <span class="font-medium text-[#ECEFF4]">{{ item.name }}</span>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="font-mono text-[10px] text-[#88C0D0]">{{ item.code }}</span>
                            <span class="text-[#4C566A]">&bull;</span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] border" :class="getCategoryBadge(item.category)">
                                {{ item.category }}
                            </span>
                        </div>
                    </div>
                    <div class="text-right font-mono">
                        <span class="text-sm font-bold text-[#ECEFF4]">{{ item.stock }}</span>
                        <span class="text-[10px] text-[#A5B1C2] ml-1">{{ item.unit || 'Unit' }}</span>
                    </div>
                </div>
            </div>
            <div v-else class="text-[11px] text-[#A5B1C2] italic mt-2">
                Rak saat ini kosong (belum ada item yang dicantumkan ke rak ini).
            </div>
        </div>

        <!-- Scan Error -->
        <div v-if="scanError" class="mt-3 p-2.5 bg-[#BF616A]/15 border border-[#BF616A]/30 rounded-lg text-xs text-[#BF616A] flex items-center gap-2">
            <svg class="w-4 h-4 text-[#BF616A] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ scanError }}</span>
        </div>

        <!-- Live Camera Scanner for Mobile / Laptop -->
        <LiveCameraScanner
            v-model="showCameraScanner"
            @scanned="handleCameraScanned"
        />
    </div>
</template>
