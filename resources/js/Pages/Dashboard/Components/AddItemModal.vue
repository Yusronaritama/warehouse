<script setup>
import { ref } from 'vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    rackLocations: { type: Array, required: true },
});

const emit = defineEmits(['update:modelValue', 'save']);

const form = ref({
    code: '',
    name: '',
    category: 'Heavy Equipment',
    location: 'Zone A (A-01) Rak 1',
    stock: 1,
    unit: 'Unit',
    status: 'Ready',
    condition: 'Good',
    minStock: 2,
});

function close() {
    emit('update:modelValue', false);
}

function submit() {
    if (!form.value.name || !form.value.code || !form.value.location) {
        alert('Mohon lengkapi kode, nama barang, dan teks lokasi rak!');
        return;
    }

    emit('save', { ...form.value });
    close();

    form.value = {
        code: '',
        name: '',
        category: 'Heavy Equipment',
        location: 'Zone A (A-01) Rak 1',
        stock: 1,
        unit: 'Unit',
        status: 'Ready',
        condition: 'Good',
        minStock: 2,
    };
}
</script>

<template>
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 rounded-xl max-w-lg w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <span class="p-1 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </span>
                    Tambah Barang & Cantumkan Lokasi Rak
                </h3>
                <button @click="close" class="text-slate-400 hover:text-white text-xl">&times;</button>
            </div>

            <form @submit.prevent="submit" class="mt-4 space-y-3.5">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kode SKU / Aset</label>
                        <input
                            v-model="form.code"
                            type="text"
                            placeholder="cth: AST-LIFT-002"
                            required
                            class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-amber-500 font-mono"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kategori</label>
                        <select
                            v-model="form.category"
                            class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-amber-500"
                        >
                            <option value="Heavy Equipment">Heavy Equipment</option>
                            <option value="Material Handlers">Material Handlers</option>
                            <option value="Safety Gear">Safety Gear</option>
                            <option value="Electronics">Electronics</option>
                            <option value="Spare Parts">Spare Parts</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Barang / Deskripsi Aset</label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="cth: Forklift Diesel 3 Ton Isuzu Engine"
                        required
                        class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-amber-500"
                    />
                </div>

                <!-- Input Khusus Lokasi Rak -->
                <div class="p-3 bg-slate-950/80 border border-amber-500/30 rounded-lg">
                    <label class="block text-xs font-semibold text-amber-400 mb-1 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        Teks Lokasi Rak (Sesuai Permintaan Client)
                    </label>
                    <input
                        v-model="form.location"
                        type="text"
                        placeholder="cth: Zone A (A-01) Rak Tingkat 2"
                        required
                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-amber-500 font-mono"
                    />
                    <span class="text-[11px] text-slate-400 mt-1 block">
                        Tercantum di tabel dan dapat diperbarui sewaktu-waktu.
                    </span>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Jumlah Stok</label>
                        <input
                            v-model="form.stock"
                            type="number"
                            min="1"
                            class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-amber-500"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Satuan</label>
                        <input
                            v-model="form.unit"
                            type="text"
                            placeholder="Unit / Pcs"
                            class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-amber-500"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Min. Alert</label>
                        <input
                            v-model="form.minStock"
                            type="number"
                            min="1"
                            class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-amber-500"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Status Operasional</label>
                        <select
                            v-model="form.status"
                            class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-amber-500"
                        >
                            <option value="Ready">Ready</option>
                            <option value="Low Stock">Low Stock</option>
                            <option value="In Maintenance">In Maintenance</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kondisi Fisik</label>
                        <select
                            v-model="form.condition"
                            class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-amber-500"
                        >
                            <option value="Good">Good (Siap Pakai)</option>
                            <option value="New">New (Baru Datang)</option>
                            <option value="Under Repair">Under Repair</option>
                        </select>
                    </div>
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
                        type="submit"
                        class="px-5 py-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-sm font-bold shadow-lg shadow-amber-500/20"
                    >
                        Simpan ke Gudang
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
