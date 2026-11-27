<script setup>
import { ref } from 'vue';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    racks: {
        type: Array,
        default: () => [],
    },
    isSubmitting: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['submit', 'reset']);

const isCustomRackMode = ref(false);

function setRackCode(code) {
    props.form.rack_code = code;
    const matched = props.racks.find((r) => r.code === code);
    if (matched && matched.zone) {
        props.form.zone = matched.zone;
    }
}

function onRackSelect(event) {
    const selectedCode = event.target.value;
    if (selectedCode === '__CUSTOM__') {
        isCustomRackMode.value = true;
        props.form.rack_code = '';
        return;
    }
    setRackCode(selectedCode);
}

function onCustomRackInput(event) {
    const val = event.target.value;
    props.form.rack_code = val;
    const matched = props.racks.find((r) => r.code.toUpperCase() === val.trim().toUpperCase());
    if (matched && matched.zone) {
        props.form.zone = matched.zone;
    }
}
</script>

<template>
    <!-- Left: Generator Form (7 cols) -->
    <div class="bg-[#3B4252] border border-[#4C566A]/60 rounded-xl p-5 shadow-lg">
        <div class="flex items-center justify-between pb-4 border-b border-[#4C566A]/60 mb-5">
            <div class="flex items-center gap-2.5">
                <span class="p-1.5 bg-[#434C5E] text-[#88C0D0] border border-[#4C566A] rounded-md">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-base font-bold text-[#ECEFF4]">Generate Barcode Rak Baru</h2>
                    <p class="text-xs text-[#A5B1C2]">Tentukan lokasi rak tujuan, format barcode, dan label stiker.</p>
                </div>
            </div>
            <span class="text-xs px-2.5 py-1 rounded bg-[#2E3440] text-[#D8DEE9] border border-[#4C566A]/60 font-mono">
                {{ form.barcode_type }}
            </span>
        </div>

        <form @submit.prevent="emit('submit')" class="space-y-4">
            <!-- Format & Target Rack Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Format Tipe Barcode -->
                <div>
                    <label class="block text-xs font-semibold text-[#A5B1C2] mb-1.5">Format Barcode</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            @click="form.barcode_type = 'CODE128'"
                            class="px-3 py-2 text-xs font-semibold rounded-lg border text-center transition-all flex items-center justify-center gap-1.5"
                            :class="form.barcode_type === 'CODE128' ? 'border-[#88C0D0] bg-[#88C0D0]/15 text-[#88C0D0] ring-1 ring-[#88C0D0]/30 font-bold' : 'border-[#4C566A]/60 bg-[#2E3440] text-[#A5B1C2] hover:text-[#ECEFF4]'"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h2v12H4zm4 0h1v12H8zm3 0h3v12h-3zm5 0h1v12h-1zm3 0h2v12h-2z"/>
                            </svg>
                            CODE128 (1D)
                        </button>
                        <button
                            type="button"
                            @click="form.barcode_type = 'QR_CODE'"
                            class="px-3 py-2 text-xs font-semibold rounded-lg border text-center transition-all flex items-center justify-center gap-1.5"
                            :class="form.barcode_type === 'QR_CODE' ? 'border-[#88C0D0] bg-[#88C0D0]/15 text-[#88C0D0] ring-1 ring-[#88C0D0]/30 font-bold' : 'border-[#4C566A]/60 bg-[#2E3440] text-[#A5B1C2] hover:text-[#ECEFF4]'"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            QR Code (2D)
                        </button>
                    </div>
                </div>

                <!-- Target Lokasi Rak (Fleksibel: Pilih Master atau Ketik Sendiri) -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-[#A5B1C2]">
                            Lokasi Rak Tujuan <span class="text-[#BF616A]">*</span>
                        </label>
                        <!-- Mode Selector Toggle -->
                        <div class="inline-flex items-center p-0.5 rounded-lg bg-[#2E3440] border border-[#4C566A]/60 text-[11px]">
                            <button
                                type="button"
                                @click="isCustomRackMode = false; if (!form.rack_code && racks.length > 0) setRackCode(racks[0].code)"
                                class="px-2 py-0.5 rounded font-medium transition-all"
                                :class="!isCustomRackMode ? 'bg-[#5E81AC] text-[#ECEFF4] font-semibold' : 'text-[#A5B1C2] hover:text-[#ECEFF4]'"
                            >
                                Pilih Rak Master
                            </button>
                            <button
                                type="button"
                                @click="isCustomRackMode = true"
                                class="px-2 py-0.5 rounded font-medium transition-all"
                                :class="isCustomRackMode ? 'bg-[#5E81AC] text-[#ECEFF4] font-semibold' : 'text-[#A5B1C2] hover:text-[#ECEFF4]'"
                            >
                                + Ketik Rak Sendiri
                            </button>
                        </div>
                    </div>

                    <!-- Opsi 1: Select Dropdown Rak yang Sudah Ada -->
                    <div v-if="!isCustomRackMode">
                        <select
                            :value="form.rack_code"
                            @change="onRackSelect"
                            class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3 py-2 text-sm text-[#ECEFF4] focus:border-[#88C0D0] focus:ring-1 focus:ring-[#88C0D0]"
                            required
                        >
                            <option v-for="r in racks" :key="r.id" :value="r.code">
                                Rak {{ r.code }} &bull; {{ r.zone || 'Zone A' }} ({{ r.items_count }} item tersimpan)
                            </option>
                            <option value="__CUSTOM__" class="text-[#EBCB8B] font-semibold bg-[#2E3440]">
                                + Ketik Nama Rak Kustom Sendiri...
                            </option>
                        </select>
                    </div>

                    <!-- Opsi 2: Box Input Teks Bebas / Rak Baru Sendiri -->
                    <div v-else class="space-y-2">
                        <div class="relative">
                            <input
                                v-model="form.rack_code"
                                type="text"
                                placeholder="Ketik kode/nama rak (contoh: D-01, RAK-BUFFER, B-03)..."
                                required
                                class="w-full bg-[#2E3440] border border-[#88C0D0]/50 rounded-lg pl-3 pr-28 py-2 text-sm text-[#ECEFF4] font-mono uppercase focus:border-[#88C0D0] focus:ring-1 focus:ring-[#88C0D0]"
                                @input="onCustomRackInput"
                            />
                            <span class="absolute right-2 top-1/2 -translate-y-1/2 px-2 py-0.5 rounded text-[10px] bg-[#88C0D0]/15 text-[#88C0D0] border border-[#88C0D0]/30 font-mono font-semibold">
                                Rak Sendiri
                            </span>
                        </div>

                        <!-- Chips Preset Rak Tersedia untuk Kemudahan -->
                        <div v-if="racks && racks.length > 0" class="flex flex-wrap items-center gap-1.5">
                            <span class="text-[10px] text-[#A5B1C2]">Pilihan cepat:</span>
                            <button
                                v-for="r in racks.slice(0, 6)"
                                :key="r.id"
                                type="button"
                                @click="setRackCode(r.code)"
                                class="px-2 py-0.5 rounded text-[10px] font-mono bg-[#2E3440] hover:bg-[#434C5E] text-[#D8DEE9] border border-[#4C566A]/60 transition-colors"
                            >
                                {{ r.code }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Zona & Label Input -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-[#A5B1C2] mb-1.5">Zona / Area Gudang</label>
                    <input
                        v-model="form.zone"
                        type="text"
                        placeholder="Contoh: Zone A - Lantai 1"
                        class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3 py-2 text-sm text-[#ECEFF4] placeholder-[#8C96A9] focus:border-[#88C0D0] focus:ring-1 focus:ring-[#88C0D0]"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#A5B1C2] mb-1.5">
                        Label Deskriptif Rak <span class="text-[#BF616A]">*</span>
                    </label>
                    <input
                        v-model="form.label"
                        type="text"
                        placeholder="Contoh: RAK UTAMA KOMPONEN MEKANIK"
                        class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3 py-2 text-sm text-[#ECEFF4] placeholder-[#8C96A9] focus:border-[#88C0D0] focus:ring-1 focus:ring-[#88C0D0]"
                        required
                    />
                </div>
            </div>

            <!-- Custom Code (Optional) & Deskripsi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-[#A5B1C2] mb-1.5">
                        Kode Barcode Khusus <span class="text-[#8C96A9] text-[11px]">(Opsional, kosongkan untuk auto)</span>
                    </label>
                    <input
                        v-model="form.custom_code"
                        type="text"
                        placeholder="Auto: BC-A01-XXXX"
                        class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3 py-2 text-sm text-[#ECEFF4] placeholder-[#8C96A9] font-mono uppercase focus:border-[#88C0D0] focus:ring-1 focus:ring-[#88C0D0]"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#A5B1C2] mb-1.5">Catatan / Spesifikasi Rak</label>
                    <input
                        v-model="form.description"
                        type="text"
                        placeholder="Kapasitas beban, jenis barang, atau petunjuk"
                        class="w-full bg-[#2E3440] border border-[#4C566A] rounded-lg px-3 py-2 text-sm text-[#ECEFF4] placeholder-[#8C96A9] focus:border-[#88C0D0] focus:ring-1 focus:ring-[#88C0D0]"
                    />
                </div>
            </div>

            <!-- Error Alert -->
            <div v-if="form.errors && (form.errors.rack_code || form.errors.custom_code || form.errors.label)" class="p-3 rounded-lg bg-[#BF616A]/15 border border-[#BF616A]/30 text-[#BF616A] text-xs">
                {{ form.errors.rack_code || form.errors.custom_code || form.errors.label }}
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-3 border-t border-[#4C566A]/60">
                <button
                    type="button"
                    @click="emit('reset')"
                    class="px-4 py-2 text-xs font-medium text-[#A5B1C2] hover:text-[#ECEFF4] hover:bg-[#434C5E] rounded-lg transition-colors"
                >
                    Reset Form
                </button>

                <button
                    type="submit"
                    :disabled="isSubmitting"
                    class="px-5 py-2.5 rounded-lg text-xs font-bold text-[#ECEFF4] bg-[#5E81AC] hover:bg-[#81A1C1] shadow-md disabled:opacity-60 transition-all flex items-center gap-2 active:scale-95"
                >
                    <svg v-if="isSubmitting" class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Daftarkan & Generate Barcode
                </button>
            </div>
        </form>
    </div>
</template>
