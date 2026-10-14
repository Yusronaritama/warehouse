<script setup>
import { ref } from 'vue';

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
        case 'Heavy Equipment':
            return 'bg-amber-500/10 text-amber-400 border-amber-500/30';
        case 'Material Handlers':
            return 'bg-blue-500/10 text-blue-400 border-blue-500/30';
        case 'Safety Gear':
            return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30';
        case 'Electronics':
            return 'bg-purple-500/10 text-purple-400 border-purple-500/30';
        case 'Spare Parts':
            return 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30';
        default:
            return 'bg-slate-800 text-slate-300 border-slate-700';
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
    <div class="bg-slate-950/80 border border-slate-800 rounded-xl p-5 shadow-lg">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <div class="flex items-center gap-2">
                <span class="p-1 bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 rounded">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01"/>
                    </svg>
                </span>
                <h3 class="text-sm font-bold text-white">Simulator Scanner Cepat Rak</h3>
            </div>
            <span class="text-[11px] text-cyan-400 font-mono">Input Barcode / USB Scanner</span>
        </div>

        <form @submit.prevent="handleScan" class="mt-3 flex gap-2">
            <input
                ref="scanInputRef"
                v-model="scanInput"
                type="text"
                placeholder="Ketik atau scan barcode (e.g. BC-A01-1024 atau RAK A-01)..."
                class="flex-1 bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white font-mono uppercase focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500"
            />
            <button
                type="submit"
                :disabled="isScanning"
                class="px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white rounded-lg text-xs font-bold transition-all disabled:opacity-50 flex items-center gap-1.5 shadow-md shadow-cyan-600/20 active:scale-95"
            >
                <svg v-if="isScanning" class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>{{ isScanning ? 'Memindai...' : 'Scan' }}</span>
            </button>
        </form>

        <!-- Scan Feedback Result -->
        <div v-if="scanFeedback" class="mt-3 p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-lg text-xs text-emerald-300">
            <div class="font-bold flex items-center justify-between pb-1 border-b border-emerald-500/20">
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    <span>Scan Sukses: Rak [{{ scanFeedback.barcode?.rack_code }}]</span>
                </div>
                <button
                    type="button"
                    @click="emit('openMutation', scanFeedback.barcode, scanFeedback.items)"
                    class="font-mono text-[11px] bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-2 py-0.5 rounded shadow-sm transition-all"
                >
                    Buka Popup Mutasi Stok &rarr;
                </button>
            </div>
            <div class="text-slate-300 mt-1.5 flex items-center justify-between">
                <span class="truncate max-w-[280px]">{{ scanFeedback.barcode?.label }}</span>
                <span class="text-emerald-400 font-mono font-bold">{{ scanFeedback.items?.length || 0 }} SKU Tersimpan</span>
            </div>

            <!-- Structured List of Items inside scanned rack -->
            <div v-if="scanFeedback.items && scanFeedback.items.length > 0" class="mt-2 space-y-1.5">
                <div
                    v-for="item in scanFeedback.items"
                    :key="item.id"
                    class="p-2 rounded bg-slate-900/80 border border-emerald-500/20 flex items-center justify-between text-xs"
                >
                    <div class="flex flex-col">
                        <span class="font-medium text-slate-100">{{ item.name }}</span>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="font-mono text-[10px] text-amber-300">{{ item.code }}</span>
                            <span class="text-slate-600">&bull;</span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] border" :class="getCategoryBadge(item.category)">
                                {{ item.category }}
                            </span>
                        </div>
                    </div>
                    <div class="text-right font-mono">
                        <span class="text-sm font-bold text-white">{{ item.stock }}</span>
                        <span class="text-[10px] text-slate-400 ml-1">{{ item.unit || 'Unit' }}</span>
                    </div>
                </div>
            </div>
            <div v-else class="text-[11px] text-slate-400 italic mt-2">
                Rak saat ini kosong (belum ada item yang dicantumkan ke rak ini).
            </div>
        </div>

        <!-- Scan Error -->
        <div v-if="scanError" class="mt-3 p-2.5 bg-rose-500/10 border border-rose-500/30 rounded-lg text-xs text-rose-400 flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ scanError }}</span>
        </div>
    </div>
</template>
