<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    itemToEdit: { type: Object, default: null },
    rackLocations: { type: Array, default: () => [] },
    availableCategories: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue', 'save', 'update']);

const isEditMode = computed(() => !!props.itemToEdit);

const DEFAULT_CATEGORIES = [
    'Bahan Baku',
    'Painting',
    'Masking',
    'Buffing',
    'Packaging',
];

// Form data
const form = ref({
    code: '',
    name: '',
    category: 'Bahan Baku',
    location: '',
    stock: 0,
    unit: 'Pcs',
    base_unit: 'Pcs',
    packaging_unit: '',
    conversion_factor: 1,
    min_stock: 5,
    status: 'Ready',
    condition: 'Baik',
});

const isSubmitting = ref(false);
const customCategory = ref('');
const isAddingCustomCategory = ref(false);
const isCustomLocation = ref(false);

const dbRackOptions = computed(() => {
    const options = new Set();
    if (Array.isArray(props.rackLocations)) {
        props.rackLocations.forEach((r) => {
            if (r && r.code) {
                const rawZone = (r.zone || '').trim();
                const zone = rawZone
                    ? (rawZone.toLowerCase().startsWith('zone') ? rawZone : `Zone ${rawZone}`)
                    : `Zone ${r.code.charAt(0)}`;
                options.add(`${zone} (${r.code})`);
            }
        });
    }
    if (form.value.location && form.value.location.trim()) {
        options.add(form.value.location.trim());
    }
    return Array.from(options).sort();
});

const allCategories = computed(() => {
    const set = new Set([
        ...DEFAULT_CATEGORIES,
        ...(props.availableCategories || []),
    ]);
    return Array.from(set).filter(Boolean);
});

// Watch itemToEdit to populate form
watch(() => props.itemToEdit, (item) => {
    if (item) {
        form.value = {
            code: item.code || '',
            name: item.name || '',
            category: item.category || 'Spare Parts',
            location: item.location || '',
            stock: item.stock ?? 0,
            unit: item.unit || item.base_unit || 'Unit',
            base_unit: item.base_unit || item.unit || 'Unit',
            packaging_unit: item.packaging_unit || '',
            conversion_factor: item.conversion_factor || 1,
            min_stock: item.min_stock ?? item.minStock ?? 5,
            status: item.status || 'Ready',
            condition: item.condition || 'Baik',
        };
    } else {
        // Reset form for create
        generateNewSku();
        form.value.name = '';
        form.value.category = allCategories.value[0] || 'Bahan Baku';
        form.value.location = props.rackLocations[0]?.code ? `Zone A (${props.rackLocations[0].code}) Rak 1` : 'Zone A (A-01) Rak 1';
        form.value.stock = 0;
        form.value.unit = 'Pcs';
        form.value.base_unit = 'Pcs';
        form.value.packaging_unit = '';
        form.value.conversion_factor = 1;
        form.value.min_stock = 5;
        form.value.status = 'Ready';
        form.value.condition = 'Baik';
    }
}, { immediate: true });

function generateNewSku() {
    const randomNum = Math.floor(100 + Math.random() * 900);
    form.value.code = `SKU-WMS-${randomNum}`;
}

function close() {
    emit('update:modelValue', false);
}

function handleAddCategory() {
    if (customCategory.value.trim()) {
        form.value.category = customCategory.value.trim();
        customCategory.value = '';
        isAddingCustomCategory.value = false;
    }
}

function handleSubmit() {
    if (!form.value.name.trim() || !form.value.code.trim()) {
        alert('Nama Barang dan Kode SKU wajib diisi!');
        return;
    }

    isSubmitting.value = true;

    if (isEditMode.value) {
        emit('update', {
            id: props.itemToEdit.id,
            ...form.value,
            minStock: form.value.min_stock,
        });
    } else {
        emit('save', {
            ...form.value,
            minStock: form.value.min_stock,
        });
    }

    setTimeout(() => {
        isSubmitting.value = false;
        close();
    }, 250);
}
</script>

<template>
    <!-- Slide-Over Backdrop -->
    <div
        v-if="modelValue"
        class="fixed inset-0 z-[70] overflow-hidden bg-[#242933]/70 backdrop-blur-xs transition-opacity"
        @click.self="close"
    >
        <div class="fixed inset-y-0 right-0 flex max-w-full pl-0 sm:pl-10">
            <!-- Slide-Over Drawer Container (Full height & viewport safe on mobile) -->
            <div
                class="w-screen max-w-full sm:max-w-md h-full max-h-[100dvh] bg-[#3B4252] border-l border-[#4C566A]/60 shadow-2xl flex flex-col justify-between"
                role="dialog"
                aria-modal="true"
                aria-labelledby="drawer-title"
            >
                <!-- Drawer Header (bg-[#2E3440] border-b border-[#4C566A]/60) -->
                <div class="shrink-0 px-4 sm:px-6 py-4 sm:py-5 border-b border-[#4C566A]/60 bg-[#2E3440] flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-mono uppercase tracking-wider text-[#88C0D0] font-semibold">
                            {{ isEditMode ? 'PEMBARUAN DATA BARANG' : 'REGISTRASI INVENTARIS' }}
                        </span>
                        <h2 id="drawer-title" class="text-lg font-bold text-[#ECEFF4] mt-0.5">
                            {{ isEditMode ? 'Edit Barang' : 'Tambah Barang Baru' }}
                        </h2>
                    </div>

                    <button
                        type="button"
                        @click="close"
                        class="p-2 rounded-lg text-[#A5B1C2] hover:text-[#ECEFF4] hover:bg-[#434C5E] transition-colors"
                        aria-label="Tutup Panel"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Drawer Form Body (Scrollable with min-h-0 to avoid pushing footer) -->
                <form @submit.prevent="handleSubmit" id="drawer-form" class="flex-1 min-h-0 overflow-y-auto px-4 sm:px-6 py-5 space-y-4 sm:space-y-5">
                    <!-- SKU & Code -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-semibold text-[#D8DEE9]">Kode SKU / Aset</label>
                            <button
                                v-if="!isEditMode"
                                type="button"
                                @click="generateNewSku"
                                class="text-[11px] text-[#88C0D0] hover:underline font-mono"
                            >
                                Acak Kode Baru
                            </button>
                        </div>
                        <input
                            v-model="form.code"
                            type="text"
                            required
                            placeholder="cth: SKU-PLT-004"
                            class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3.5 py-2.5 text-sm font-mono text-[#88C0D0] focus:outline-none focus:border-[#88C0D0] focus:ring-1 focus:ring-[#88C0D0]"
                        />
                    </div>

                    <!-- Name -->
                    <div>
                        <label class="block text-xs font-semibold text-[#D8DEE9] mb-1.5">Nama Barang / Aset</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="cth: Hydraulic Valve Assemblies 120mm"
                            class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3.5 py-2.5 text-sm text-[#ECEFF4] focus:outline-none focus:border-[#88C0D0] focus:ring-1 focus:ring-[#88C0D0]"
                        />
                    </div>

                    <!-- Category -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-semibold text-[#D8DEE9]">Kategori</label>
                            <button
                                type="button"
                                @click="isAddingCustomCategory = !isAddingCustomCategory"
                                class="text-[11px] text-[#88C0D0] hover:underline"
                            >
                                {{ isAddingCustomCategory ? 'Pilih yang ada' : '+ Kategori Baru' }}
                            </button>
                        </div>

                        <div v-if="isAddingCustomCategory" class="flex gap-2">
                            <input
                                v-model="customCategory"
                                type="text"
                                placeholder="Ketik nama kategori..."
                                class="flex-1 bg-[#2E3440] border border-[#4C566A] rounded-lg px-3 py-2 text-sm text-[#ECEFF4] focus:outline-none focus:border-[#88C0D0]"
                            />
                            <button
                                type="button"
                                @click="handleAddCategory"
                                class="px-3 py-2 bg-[#434C5E] text-xs text-[#88C0D0] border border-[#4C566A] rounded-lg hover:bg-[#4C566A]"
                            >
                                Tambah
                            </button>
                        </div>

                        <select
                            v-else
                            v-model="form.category"
                            class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3.5 py-2.5 text-sm text-[#ECEFF4] focus:outline-none focus:border-[#88C0D0]"
                        >
                            <option v-for="cat in allCategories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>

                    <!-- Physical Location / Rack -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-[#D8DEE9]">Lokasi Rak Penyimpanan Fisik</label>
                            <button
                                type="button"
                                @click="isCustomLocation = !isCustomLocation"
                                class="text-[11px] text-[#88C0D0] hover:underline"
                            >
                                {{ isCustomLocation ? 'Pilih Opsi Rak DB' : '+ Ketik Manual' }}
                            </button>
                        </div>

                        <select
                            v-if="!isCustomLocation"
                            v-model="form.location"
                            class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3.5 py-2.5 text-sm font-mono text-[#ECEFF4] focus:outline-none focus:border-[#88C0D0]"
                        >
                            <option value="" disabled>-- Pilih Rak dari Database --</option>
                            <option v-for="opt in dbRackOptions" :key="opt" :value="opt">
                                {{ opt }}
                            </option>
                        </select>

                        <input
                            v-else
                            v-model="form.location"
                            type="text"
                            required
                            placeholder="cth: Zone B (B-02) Rak 1"
                            class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3.5 py-2.5 text-sm font-mono text-[#ECEFF4] focus:outline-none focus:border-[#88C0D0]"
                        />

                        <div v-if="rackLocations.length > 0" class="mt-2 flex flex-wrap gap-1.5 items-center">
                            <span class="text-[10px] text-[#A5B1C2]">Pilihan Cepat:</span>
                            <button
                                v-for="r in rackLocations.slice(0, 6)"
                                :key="r.code"
                                type="button"
                                @click="form.location = `${r.zone || 'Zone A'} (${r.code}) Rak 1`"
                                class="text-[10px] font-mono px-2 py-0.5 rounded bg-[#2E3440] hover:bg-[#434C5E] text-[#D8DEE9] border border-[#4C566A]"
                            >
                                {{ r.code }}
                            </button>
                        </div>
                    </div>

                    <!-- Stock & Base Unit (2 columns) -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#D8DEE9] mb-1.5">Jumlah Stok Fisik</label>
                            <input
                                v-model.number="form.stock"
                                type="number"
                                min="0"
                                required
                                class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3.5 py-2.5 text-sm font-mono text-[#ECEFF4] focus:outline-none focus:border-[#88C0D0]"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#D8DEE9] mb-1.5">Satuan Dasar (UoM)</label>
                            <select
                                v-model="form.base_unit"
                                class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3 py-2.5 text-sm text-[#ECEFF4] focus:outline-none focus:border-[#88C0D0]"
                            >
                                <option value="Pcs">Pcs</option>
                                <option value="Liter">Liter</option>
                                <option value="Roll">Roll</option>
                                <option value="Botol">Botol</option>
                                <option value="Box">Box</option>
                                <option value="Unit">Unit</option>
                                <option value="Kg">Kg</option>
                                <option value="Meter">Meter</option>
                            </select>
                        </div>
                    </div>

                    <!-- Min Stock Threshold -->
                    <div>
                        <label class="block text-xs font-semibold text-[#D8DEE9] mb-1.5">Ambang Batas Minimum (Min Stock Alert)</label>
                        <input
                            v-model.number="form.min_stock"
                            type="number"
                            min="0"
                            required
                            class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3.5 py-2.5 text-sm font-mono text-[#ECEFF4] focus:outline-none focus:border-[#88C0D0]"
                        />
                        <p class="text-[11px] text-[#A5B1C2] mt-1">
                            Sistem akan otomatis menandai "Low Stock" bila stok tersisa berada di bawah atau sama dengan angka ini.
                        </p>
                    </div>

                    <!-- Status & Condition (2 columns) -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#D8DEE9] mb-1.5">Status Barang</label>
                            <select
                                v-model="form.status"
                                class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3 py-2.5 text-sm text-[#ECEFF4] focus:outline-none focus:border-[#88C0D0]"
                            >
                                <option value="Ready">Ready</option>
                                <option value="Low Stock">Low Stock</option>
                                <option value="In Maintenance">In Maintenance</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#D8DEE9] mb-1.5">Kondisi Fisik</label>
                            <select
                                v-model="form.condition"
                                class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3 py-2.5 text-sm text-[#ECEFF4] focus:outline-none focus:border-[#88C0D0]"
                            >
                                <option value="Baru">Baru</option>
                                <option value="Baik">Baik</option>
                                <option value="Perlu Servis">Perlu Servis</option>
                            </select>
                        </div>
                    </div>
                </form>

                <!-- Drawer Footer Actions (Sticky bottom & shrink-0) -->
                <div class="shrink-0 sticky bottom-0 z-10 px-4 sm:px-6 py-3.5 sm:py-4 border-t border-[#4C566A]/60 bg-[#2E3440] flex items-center justify-end gap-3 pb-[max(1rem,env(safe-area-inset-bottom))]">
                    <button
                        type="button"
                        @click="close"
                        class="px-4 py-2.5 rounded-lg text-sm font-medium text-[#A5B1C2] hover:text-[#ECEFF4] hover:bg-[#434C5E] transition-colors"
                    >
                        Batal
                    </button>
                    <!-- CTA Button (+ Simpan): bg-[#5E81AC] hover:bg-[#81A1C1] text-[#ECEFF4] -->
                    <button
                        type="submit"
                        form="drawer-form"
                        :disabled="isSubmitting"
                        class="px-5 py-2.5 rounded-lg bg-[#5E81AC] hover:bg-[#81A1C1] text-[#ECEFF4] font-semibold text-sm shadow-xs active:scale-95 transition-all disabled:opacity-50"
                    >
                        {{ isSubmitting ? 'Menyimpan...' : (isEditMode ? 'Simpan Pembaruan' : 'Daftarkan Barang') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
