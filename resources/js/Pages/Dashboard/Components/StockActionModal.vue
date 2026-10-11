<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    item: { type: Object, default: null },
    actionType: { type: String, default: 'in' }, // 'in' or 'out'
});

const emit = defineEmits(['update:modelValue', 'confirm']);

const amount = ref(1);
const notes = ref('');

watch(() => props.modelValue, (newVal) => {
    if (newVal) {
        amount.value = 1;
        notes.value = '';
    }
});

function close() {
    emit('update:modelValue', false);
}

function submit() {
    const qty = parseInt(amount.value) || 0;
    if (qty <= 0) return;

    if (props.actionType === 'out' && props.item && props.item.stock < qty) {
        alert('Stok tidak mencukupi untuk pengeluaran barang ini!');
        return;
    }

    emit('confirm', {
        item: props.item,
        type: props.actionType,
        amount: qty,
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
                <div class="p-3.5 rounded-lg bg-slate-950 border border-slate-800 text-xs">
                    <div class="font-bold text-white text-sm">{{ item.name }}</div>
                    <div class="text-slate-400 mt-1">
                        SKU: <span class="font-mono text-amber-400">{{ item.code }}</span>
                    </div>
                    <div class="text-slate-400 mt-0.5 flex items-center gap-1.5">
                        <span>Lokasi Rak:</span>
                        <span class="font-mono text-slate-200 bg-slate-850 px-1.5 py-0.5 rounded border border-slate-700">
                            {{ item.location }}
                        </span>
                    </div>
                    <div class="text-slate-400 mt-1">
                        Stok Saat Ini: <span class="font-bold text-emerald-400">{{ item.stock }} {{ item.unit }}</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">
                        Jumlah {{ actionType === 'in' ? 'Masuk (+)' : 'Keluar (-)' }}
                    </label>
                    <input
                        v-model="amount"
                        type="number"
                        min="1"
                        :max="actionType === 'out' ? item.stock : 9999"
                        class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-amber-500 font-mono font-bold"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Keterangan / Tujuan / No. Surat Jalan</label>
                    <input
                        v-model="notes"
                        type="text"
                        :placeholder="actionType === 'in' ? 'cth: Restock PO-4091 dari Supplier' : 'cth: Dikirim ke Bay 2 / Proyek A'"
                        class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-amber-500"
                    />
                </div>

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
