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
    <div v-if="modelValue && item" class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-[#242933]/75 backdrop-blur-xs">
        <!-- Nordic Dim Modal: bg-[#3B4252] border border-[#4C566A]/60 -->
        <div class="bg-[#3B4252] border border-[#4C566A]/60 rounded-xl max-w-md w-full p-6 shadow-2xl text-[#ECEFF4]">
            <div class="flex items-center justify-between pb-3 border-b border-[#4C566A]/60">
                <h3 class="text-lg font-bold text-[#ECEFF4] flex items-center gap-2">
                    <span
                        class="px-2 py-0.5 rounded text-xs font-mono font-bold"
                        :class="actionType === 'in' ? 'bg-[#A3BE8C]/20 text-[#A3BE8C] border border-[#A3BE8C]/40' : 'bg-[#EBCB8B]/20 text-[#EBCB8B] border border-[#EBCB8B]/40'"
                    >
                        {{ actionType === 'in' ? 'INBOUND' : 'OUTBOUND' }}
                    </span>
                    <span>Catat Mutasi Stok</span>
                </h3>
                <button @click="close" class="text-[#A5B1C2] hover:text-[#ECEFF4] text-xl font-bold">&times;</button>
            </div>

            <div class="mt-4 space-y-3">
                <!-- Info Barang & Stok Bertingkat -->
                <div class="p-3.5 rounded-lg bg-[#2E3440] border border-[#4C566A]/60 text-xs space-y-1.5">
                    <div class="font-bold text-[#ECEFF4] text-sm">{{ item.name }}</div>
                    <div class="text-[#A5B1C2] flex items-center justify-between">
                        <span>SKU: <strong class="font-mono text-[#88C0D0]">{{ item.code }}</strong></span>
                        <span class="font-mono text-[#D8DEE9] bg-[#3B4252] px-1.5 py-0.5 rounded border border-[#4C566A]/60">
                            {{ item.location }}
                        </span>
                    </div>

                    <div class="pt-1 border-t border-[#4C566A]/40 flex items-center justify-between">
                        <span class="text-[#A5B1C2]">Stok Saat Ini:</span>
                        <div class="text-right">
                            <span class="font-bold font-mono text-[#A3BE8C] text-sm">
                                {{ item.formatted_stock || (item.stock + ' ' + (item.base_unit || item.unit)) }}
                            </span>
                            <div v-if="hasPackaging" class="text-[10px] text-[#A5B1C2] font-mono">
                                Total: {{ item.stock }} {{ baseUnit }} &bull; 1 {{ packagingUnit }} = {{ factor }} {{ baseUnit }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pilihan Satuan Mutasi (Multi-UoM) -->
                <div v-if="hasPackaging" class="p-2.5 bg-[#2E3440] border border-[#4C566A]/60 rounded-lg">
                    <label class="block text-xs font-semibold text-[#D8DEE9] mb-1.5 flex items-center justify-between">
                        <span>Pilih Satuan Mutasi:</span>
                        <span class="text-[10px] text-[#88C0D0] font-normal">Fleksibel Bertingkat</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            @click="selectedUnit = packagingUnit"
                            :class="[
                                'px-2.5 py-2 rounded-lg text-xs font-semibold border transition-all text-left flex flex-col',
                                selectedUnit === packagingUnit
                                    ? 'bg-[#5E81AC]/20 border-[#88C0D0] text-[#88C0D0] ring-1 ring-[#88C0D0]/40'
                                    : 'bg-[#3B4252] border-[#4C566A]/60 text-[#D8DEE9] hover:border-[#88C0D0]/50'
                            ]"
                        >
                            <span class="font-bold text-[#ECEFF4]">{{ packagingUnit }} (Kemasan)</span>
                            <span class="text-[10px] text-[#A5B1C2] mt-0.5">Isi @ {{ factor }} {{ baseUnit }}</span>
                        </button>

                        <button
                            type="button"
                            @click="selectedUnit = baseUnit"
                            :class="[
                                'px-2.5 py-2 rounded-lg text-xs font-semibold border transition-all text-left flex flex-col',
                                selectedUnit === baseUnit
                                    ? 'bg-[#5E81AC]/20 border-[#88C0D0] text-[#88C0D0] ring-1 ring-[#88C0D0]/40'
                                    : 'bg-[#3B4252] border-[#4C566A]/60 text-[#D8DEE9] hover:border-[#88C0D0]/50'
                            ]"
                        >
                            <span class="font-bold text-[#ECEFF4]">{{ baseUnit }} (Eceran)</span>
                            <span class="text-[10px] text-[#A5B1C2] mt-0.5">Satuan dasar terkecil</span>
                        </button>
                    </div>
                </div>

                <!-- Input Jumlah Mutasi -->
                <div>
                    <label class="block text-xs font-semibold text-[#D8DEE9] mb-1 flex items-center justify-between">
                        <span>Jumlah {{ actionType === 'in' ? 'Masuk (+)' : 'Keluar (-)' }}</span>
                        <span class="text-xs text-[#88C0D0] font-mono font-bold">{{ selectedUnit }}</span>
                    </label>
                    <div class="relative">
                        <input
                            v-model="amount"
                            type="number"
                            min="1"
                            class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3 py-2 text-sm text-[#ECEFF4] focus:border-[#88C0D0] focus:ring-1 focus:ring-[#88C0D0] font-mono font-bold"
                        />
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-[#A5B1C2] pointer-events-none">
                            {{ selectedUnit }}
                        </span>
                    </div>

                    <!-- Live Conversion Helper -->
                    <div v-if="hasPackaging && selectedUnit === packagingUnit" class="mt-1.5 p-2 rounded bg-[#2E3440] border border-[#4C566A]/60 text-[11px] font-mono flex items-center justify-between text-[#D8DEE9]">
                        <span class="text-[#A5B1C2]">Total Kuantitas Dasar:</span>
                        <span class="text-[#88C0D0] font-bold">= {{ calculatedBaseAmount }} {{ baseUnit }}</span>
                    </div>

                    <!-- Projected Stock Helper -->
                    <div class="mt-1 text-[11px] font-mono text-[#A5B1C2] flex items-center justify-between">
                        <span>Proyeksi sisa stok:</span>
                        <span :class="projectedStock < 0 ? 'text-[#BF616A] font-bold' : 'text-[#D8DEE9] font-semibold'">
                            {{ formatBreakdown(projectedStock) }}
                        </span>
                    </div>
                </div>

                <!-- Keterangan / Surat Jalan -->
                <div>
                    <label class="block text-xs font-semibold text-[#D8DEE9] mb-1">Keterangan / Tujuan / No. Surat Jalan</label>
                    <input
                        v-model="notes"
                        type="text"
                        :placeholder="actionType === 'in' ? 'cth: Restock PO-4091 dari Supplier' : 'cth: Dikirim ke Bay 2 / Proyek A'"
                        class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3 py-2 text-sm text-[#ECEFF4] focus:border-[#88C0D0] focus:ring-1 focus:ring-[#88C0D0]"
                    />
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 flex items-center justify-end gap-2.5 border-t border-[#4C566A]/40">
                    <button
                        type="button"
                        @click="close"
                        class="px-4 py-2 rounded-lg bg-[#434C5E] text-[#ECEFF4] text-sm font-semibold hover:bg-[#4C566A] transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        @click="submit"
                        class="px-5 py-2 rounded-lg text-sm font-semibold shadow-xs transition-colors"
                        :class="actionType === 'in' ? 'bg-[#A3BE8C] hover:bg-[#8FBCBB] text-[#2E3440] font-bold' : 'bg-[#EBCB8B] hover:bg-[#D08770] text-[#2E3440] font-bold'"
                    >
                        Konfirmasi {{ actionType === 'in' ? 'Stok Masuk' : 'Stok Keluar' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
