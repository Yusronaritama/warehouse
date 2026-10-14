<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    item: { type: Object, default: null },
    actionType: { type: String, default: 'in' }, // 'in' or 'out'
});

const emit = defineEmits(['update:modelValue', 'confirm']);

const amount = ref(1);
const selectedUnit = ref('');
const notes = ref('');

const hasPackaging = computed(() => {
    return !!(props.item?.packaging_unit && (props.item?.conversion_factor || 1) > 1);
});

const baseUnit = computed(() => {
    return props.item?.base_unit || props.item?.unit || 'Unit';
});

const packagingUnit = computed(() => {
    return props.item?.packaging_unit || '';
});

const factor = computed(() => {
    return props.item?.conversion_factor || 1;
});

watch(
    () => props.modelValue,
    (newVal) => {
        if (newVal) {
            amount.value = 1;
            notes.value = '';
            selectedUnit.value = hasPackaging.value ? packagingUnit.value : baseUnit.value;
        }
    }
);

// Calculated base quantity for mutation
const calculatedBaseAmount = computed(() => {
    const qty = parseInt(amount.value) || 0;
    if (hasPackaging.value && selectedUnit.value === packagingUnit.value) {
        return qty * factor.value;
    }
    return qty;
});

// Projected stock in base unit after mutation
const projectedStock = computed(() => {
    if (!props.item) return 0;
    const current = props.item.stock || 0;
    const delta = calculatedBaseAmount.value;
    return props.actionType === 'in' ? current + delta : current - delta;
});

function formatBreakdown(stockQty) {
    if (stockQty < 0) return '0 ' + baseUnit.value;
    if (!hasPackaging.value) return `${stockQty} ${baseUnit.value}`;
    const f = factor.value;
    const pkgs = Math.floor(stockQty / f);
    const rem = stockQty % f;
    if (pkgs > 0 && rem > 0) return `${pkgs} ${packagingUnit.value} + ${rem} ${baseUnit.value}`;
    if (pkgs > 0) return `${pkgs} ${packagingUnit.value}`;
    return `${rem} ${baseUnit.value}`;
}

function close() {
    emit('update:modelValue', false);
}

function submit() {
    const qty = parseInt(amount.value) || 0;
    if (qty <= 0) return;

    if (props.actionType === 'out' && props.item && calculatedBaseAmount.value > props.item.stock) {
        alert(`Stok tidak mencukupi! Dibutuhkan ${calculatedBaseAmount.value} ${baseUnit.value}, stok yang tersedia ${props.item.stock} ${baseUnit.value}.`);
        return;
    }

    emit('confirm', {
        item: props.item,
        type: props.actionType,
        amount: qty,
        input_unit: selectedUnit.value,
        notes: notes.value,
    });
    close();
}
</script>

<template>
    <div v-if="modelValue && item" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 rounded-xl max-w-md w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <span
                        class="px-2 py-0.5 rounded text-xs font-mono font-bold"
                        :class="actionType === 'in' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400'"
                    >
                        {{ actionType === 'in' ? 'INBOUND' : 'OUTBOUND' }}
                    </span>
                    Catat Mutasi Stok
                </h3>
                <button @click="close" class="text-slate-400 hover:text-white text-xl">&times;</button>
            </div>

            <div class="mt-4 space-y-3">
                <!-- Info Barang & Stok Bertingkat -->
                <div class="p-3.5 rounded-lg bg-slate-950 border border-slate-800 text-xs space-y-1.5">
                    <div class="font-bold text-white text-sm">{{ item.name }}</div>
                    <div class="text-slate-400 flex items-center justify-between">
                        <span>SKU: <strong class="font-mono text-amber-400">{{ item.code }}</strong></span>
                        <span class="font-mono text-slate-300 bg-slate-900 px-1.5 py-0.5 rounded border border-slate-800">
                            {{ item.location }}
                        </span>
                    </div>

                    <div class="pt-1 border-t border-slate-900 flex items-center justify-between">
                        <span class="text-slate-400">Stok Saat Ini:</span>
                        <div class="text-right">
                            <span class="font-bold font-mono text-emerald-400 text-sm">
                                {{ item.formatted_stock || (item.stock + ' ' + (item.base_unit || item.unit)) }}
                            </span>
                            <div v-if="hasPackaging" class="text-[10px] text-slate-500 font-mono">
                                Total: {{ item.stock }} {{ baseUnit }} &bull; 1 {{ packagingUnit }} = {{ factor }} {{ baseUnit }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pilihan Satuan Mutasi (Multi-UoM) -->
                <div v-if="hasPackaging" class="p-2.5 bg-slate-950/80 border border-slate-800 rounded-lg">
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                        <span>Pilih Satuan Mutasi:</span>
                        <span class="text-[10px] text-amber-400 font-normal">Fleksibel Bertingkat</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            @click="selectedUnit = packagingUnit"
                            :class="[
                                'px-2.5 py-2 rounded-lg text-xs font-semibold border transition-all text-left flex flex-col',
                                selectedUnit === packagingUnit
                                    ? 'bg-amber-500/15 border-amber-500 text-amber-300 ring-1 ring-amber-500/50'
                                    : 'bg-slate-900 border-slate-700 text-slate-300 hover:border-slate-600'
                            ]"
                        >
                            <span class="font-bold text-white">{{ packagingUnit }} (Kemasan)</span>
                            <span class="text-[10px] text-slate-400 mt-0.5">Isi @ {{ factor }} {{ baseUnit }}</span>
                        </button>

                        <button
                            type="button"
                            @click="selectedUnit = baseUnit"
                            :class="[
                                'px-2.5 py-2 rounded-lg text-xs font-semibold border transition-all text-left flex flex-col',
                                selectedUnit === baseUnit
                                    ? 'bg-amber-500/15 border-amber-500 text-amber-300 ring-1 ring-amber-500/50'
                                    : 'bg-slate-900 border-slate-700 text-slate-300 hover:border-slate-600'
                            ]"
                        >
                            <span class="font-bold text-white">{{ baseUnit }} (Eceran)</span>
                            <span class="text-[10px] text-slate-400 mt-0.5">Satuan dasar terkecil</span>
                        </button>
                    </div>
                </div>

                <!-- Input Jumlah Mutasi -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1 flex items-center justify-between">
                        <span>Jumlah {{ actionType === 'in' ? 'Masuk (+)' : 'Keluar (-)' }}</span>
                        <span class="text-xs text-amber-400 font-mono font-bold">{{ selectedUnit }}</span>
                    </label>
                    <div class="relative">
                        <input
                            v-model="amount"
                            type="number"
                            min="1"
                            class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-amber-500 font-mono font-bold"
                        />
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none">
                            {{ selectedUnit }}
                        </span>
                    </div>

                    <!-- Live Conversion Helper -->
                    <div v-if="hasPackaging && selectedUnit === packagingUnit" class="mt-1.5 p-2 rounded bg-slate-950 border border-slate-800 text-[11px] font-mono flex items-center justify-between text-slate-300">
                        <span class="text-slate-400">Total Kuantitas Dasar:</span>
                        <span class="text-amber-300 font-bold">= {{ calculatedBaseAmount }} {{ baseUnit }}</span>
                    </div>

                    <!-- Projected Stock Helper -->
                    <div class="mt-1 text-[11px] font-mono text-slate-400 flex items-center justify-between">
                        <span>Proyeksi sisa stok:</span>
                        <span :class="projectedStock < 0 ? 'text-rose-400 font-bold' : 'text-slate-300 font-semibold'">
                            {{ formatBreakdown(projectedStock) }}
                        </span>
                    </div>
                </div>

                <!-- Keterangan / Surat Jalan -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Keterangan / Tujuan / No. Surat Jalan</label>
                    <input
                        v-model="notes"
                        type="text"
                        :placeholder="actionType === 'in' ? 'cth: Restock PO-4091 dari Supplier' : 'cth: Dikirim ke Bay 2 / Proyek A'"
                        class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-amber-500"
                    />
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 flex items-center justify-end gap-2.5">
                    <button
                        type="button"
                        @click="close"
                        class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 text-sm font-semibold hover:bg-slate-700"
                    >
                        Batal
                    </button>
                    <button
                        @click="submit"
                        class="px-5 py-2 rounded-lg text-sm font-bold shadow-lg"
                        :class="actionType === 'in' ? 'bg-emerald-500 hover:bg-emerald-400 text-slate-950 shadow-emerald-500/20' : 'bg-amber-500 hover:bg-amber-400 text-slate-950 shadow-amber-500/20'"
                    >
                        Konfirmasi {{ actionType === 'in' ? 'Stok Masuk' : 'Stok Keluar' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
