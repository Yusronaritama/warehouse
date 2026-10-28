<script setup>
import { ref, watch, nextTick } from 'vue';
import JsBarcode from 'jsbarcode';
import QRCode from 'qrcode';
import axios from 'axios';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    barcode: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue', 'printed']);

const printSvgRef = ref(null);
const printCanvasRef = ref(null);

watch(
    () => props.modelValue,
    async (isOpen) => {
        if (isOpen && props.barcode) {
            await nextTick();
            renderPrintCode();
            logPrintAction();
        }
    }
);

async function renderPrintCode() {
    if (!props.barcode) return;
    await nextTick();

    if (props.barcode.barcode_type === 'CODE128' && printSvgRef.value) {
        try {
            JsBarcode(printSvgRef.value, props.barcode.barcode_code, {
                format: 'CODE128',
                lineColor: '#000000',
                width: 2.4,
                height: 64,
                displayValue: true,
                fontSize: 16,
                textMargin: 6,
                font: 'monospace',
                background: '#ffffff',
                margin: 10,
            });
        } catch (e) {
            console.error('Print modal barcode error:', e);
        }
    } else if (props.barcode.barcode_type === 'QR_CODE' && printCanvasRef.value) {
        try {
            // Encode direct rack URL so smartphone camera immediately opens the page in browser
            const directUrl = typeof window !== 'undefined'
                ? `${window.location.origin}/racks/${encodeURIComponent(props.barcode.rack_code)}`
                : props.barcode.barcode_code;

            await QRCode.toCanvas(printCanvasRef.value, directUrl, {
                width: 170,
                margin: 2,
                color: {
                    dark: '#000000',
                    light: '#ffffff',
                },
            });
        } catch (e) {
            console.error('Print modal QR error:', e);
        }
    }
}

async function logPrintAction() {
    if (!props.barcode?.id) return;
    try {
        await axios.post(route('barcodes.printLog', props.barcode.id));
    } catch (e) {
        // silent catch
    }
}

function executePrint() {
    emit('printed', props.barcode);
    window.print();
}

function closeModal() {
    emit('update:modelValue', false);
}

function formatDate(isoString) {
    if (!isoString) return '-';
    const date = new Date(isoString);
    return date.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <div
        v-if="modelValue && barcode"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
    >
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-md w-full p-6 shadow-2xl relative text-slate-100">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div>
                    <h3 class="text-base font-bold text-white">Cetak Stiker Rak Gudang</h3>
                    <p class="text-xs text-slate-400">Siap dicetak untuk penempelan fisik pada rak.</p>
                </div>
                <button
                    type="button"
                    @click="closeModal"
                    class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Printable Sticker Box -->
            <div id="printable-sticker-area" class="my-5 p-5 bg-white text-slate-900 rounded-xl border-4 border-slate-900 shadow-md flex flex-col items-center">
                <div class="w-full flex items-center justify-between pb-2 border-b-2 border-slate-900 font-bold text-xs">
                    <div class="flex items-center gap-1.5">
                        <span class="bg-slate-900 text-white px-2 py-0.5 rounded text-[10px] font-mono">WAREREX</span>
                        <span>KRWNG LOGISTICS FACILITY</span>
                    </div>
                    <span class="px-2 py-0.5 bg-slate-100 rounded border border-slate-300 font-mono text-xs">
                        {{ barcode.zone || 'ZONE A' }}
                    </span>
                </div>

                <div class="w-full text-center py-3">
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">LOKASI RAK RESMI</div>
                    <div class="text-4xl font-black font-mono tracking-tight text-slate-950 mt-0.5">
                        RAK {{ barcode.rack_code }}
                    </div>
                    <div class="text-sm font-semibold text-slate-800 mt-1">
                        {{ barcode.label }}
                    </div>
                </div>

                <!-- Rendered Barcode Graphic -->
                <div class="w-full flex justify-center items-center py-2 bg-white">
                    <svg v-show="barcode.barcode_type === 'CODE128'" ref="printSvgRef" class="max-w-full"></svg>
                    <canvas v-show="barcode.barcode_type === 'QR_CODE'" ref="printCanvasRef" class="max-w-full"></canvas>
                </div>

                <div class="w-full pt-2 border-t-2 border-slate-900 flex items-center justify-between text-[11px] font-mono text-slate-600">
                    <span>KODE: {{ barcode.barcode_code }}</span>
                    <span>DIKELUARKAN: {{ formatDate(barcode.created_at) }}</span>
                </div>
            </div>

            <!-- Actions inside modal -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button
                    type="button"
                    @click="closeModal"
                    class="px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-800 rounded-lg transition-colors"
                >
                    Tutup
                </button>
                <button
                    type="button"
                    @click="executePrint"
                    class="px-5 py-2.5 text-xs font-bold text-slate-950 bg-amber-400 hover:bg-amber-300 rounded-lg shadow-md flex items-center gap-2 transition-all"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak Stiker (Print Dialog)
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media print {
    body * {
        visibility: hidden;
    }
    #printable-sticker-area, #printable-sticker-area * {
        visibility: visible;
    }
    #printable-sticker-area {
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        border: 2px solid #000;
    }
}
</style>
