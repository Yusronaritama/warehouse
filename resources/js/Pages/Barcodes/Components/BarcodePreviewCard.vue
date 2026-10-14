<script setup>
import { ref, watch, nextTick, onMounted } from 'vue';
import JsBarcode from 'jsbarcode';
import QRCode from 'qrcode';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    sampleCode: {
        type: String,
        required: true,
    },
});

const previewSvgRef = ref(null);
const previewCanvasRef = ref(null);

async function renderLivePreview() {
    await nextTick();
    const code = props.sampleCode;

    if (props.form.barcode_type === 'CODE128') {
        if (previewSvgRef.value) {
            try {
                JsBarcode(previewSvgRef.value, code, {
                    format: 'CODE128',
                    lineColor: '#0f172a',
                    width: 2.2,
                    height: 58,
                    displayValue: true,
                    fontSize: 14,
                    textMargin: 6,
                    font: 'monospace',
                    background: '#ffffff',
                    margin: 8,
                });
            } catch (err) {
                console.warn('Barcode render preview warning:', err);
            }
        }
    } else if (props.form.barcode_type === 'QR_CODE') {
        if (previewCanvasRef.value) {
            try {
                await QRCode.toCanvas(previewCanvasRef.value, code, {
                    width: 130,
                    margin: 1,
                    color: {
                        dark: '#0f172a',
                        light: '#ffffff',
                    },
                });
            } catch (err) {
                console.warn('QR Code preview render error:', err);
            }
        }
    }
}

watch(
    () => [props.form.barcode_type, props.form.rack_code, props.form.custom_code, props.form.label, props.form.zone, props.sampleCode],
    () => {
        renderLivePreview();
    },
    { deep: true }
);

onMounted(() => {
    renderLivePreview();
});
</script>

<template>
    <!-- Physical Warehouse Sticker Card Preview -->
    <div class="bg-slate-950/80 border border-slate-800 rounded-xl p-5 shadow-lg flex flex-col">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800 text-xs">
            <span class="font-bold text-slate-200 uppercase tracking-wider flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                Live Preview Stiker Rak Gudang
            </span>
            <span class="text-slate-400 font-mono text-[11px]">Label Ukuran Standar</span>
        </div>

        <!-- Industrial Label Mockup (High Contrast White Card) -->
        <div class="mt-4 p-4 bg-white text-slate-900 rounded-lg border-2 border-dashed border-amber-500/60 shadow-xl flex flex-col items-center select-none">
            <!-- Top Strip: Facility and Zone -->
            <div class="w-full flex items-center justify-between pb-2 border-b-2 border-slate-900 font-bold text-xs uppercase tracking-wider">
                <div class="flex items-center gap-1.5 text-slate-800">
                    <span class="bg-slate-900 text-white px-1.5 py-0.5 rounded text-[10px]">WAREREX</span>
                    <span>KRWNG LOGISTICS</span>
                </div>
                <span class="px-2 py-0.5 bg-slate-100 text-slate-900 rounded border border-slate-300 font-mono text-[11px]">
                    {{ form.zone || 'ZONE A' }}
                </span>
            </div>

            <!-- Target Rack Big Headline -->
            <div class="w-full text-center py-2">
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">LOKASI RAK GUDANG</div>
                <div class="text-3xl font-black tracking-tight text-slate-900 font-mono mt-0.5">
                    RAK {{ form.rack_code || 'A-01' }}
                </div>
                <div class="text-xs font-semibold text-slate-700 truncate max-w-[280px] mx-auto mt-0.5">
                    {{ form.label || 'LABEL RAK PENYIMPANAN' }}
                </div>
            </div>

            <!-- Rendered Barcode Area -->
            <div class="w-full flex justify-center items-center py-1 bg-white min-h-[90px]">
                <svg v-show="form.barcode_type === 'CODE128'" ref="previewSvgRef" class="max-w-full"></svg>
                <canvas v-show="form.barcode_type === 'QR_CODE'" ref="previewCanvasRef" class="max-w-full"></canvas>
            </div>

            <!-- Bottom Spec Strip -->
            <div class="w-full pt-2 border-t border-slate-300 flex items-center justify-between text-[10px] text-slate-500 font-mono">
                <span>ID: {{ sampleCode }}</span>
                <span>STATUS: ACTIVE</span>
            </div>
        </div>

        <div class="mt-3 text-center text-xs text-slate-400">
            Label ini siap dicetak langsung pada stiker thermal atau kertas label rak gudang.
        </div>
    </div>
</template>
