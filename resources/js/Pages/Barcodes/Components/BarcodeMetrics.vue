<script setup>
defineProps({
    metrics: {
        type: Object,
        default: () => ({
            totalBarcodes: 0,
            activeCount: 0,
            inactiveCount: 0,
            totalScans: 0,
        }),
    },
    filterStatus: {
        type: String,
        default: 'ALL',
    },
});

const emit = defineEmits(['selectStatus']);
</script>

<template>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <!-- Card 1: Total Barcodes -->
        <div
            @click="emit('selectStatus', 'ALL')"
            class="cursor-pointer bg-[#3B4252] border rounded-xl p-4 transition-all"
            :class="filterStatus === 'ALL' ? 'border-[#88C0D0] bg-[#88C0D0]/10 ring-1 ring-[#88C0D0]/30' : 'border-[#4C566A]/60 hover:border-[#88C0D0]/50'"
        >
            <div class="text-xs text-[#A5B1C2] font-semibold uppercase">Total Barcode Rak</div>
            <div class="text-2xl font-bold font-mono text-[#ECEFF4] mt-1">{{ metrics.totalBarcodes }}</div>
            <div class="text-[11px] text-[#A5B1C2] mt-0.5">Semua entri rak terdaftar</div>
        </div>

        <!-- Card 2: Barcode Aktif -->
        <div
            @click="emit('selectStatus', 'ACTIVE')"
            class="cursor-pointer bg-[#3B4252] border rounded-xl p-4 transition-all"
            :class="filterStatus === 'ACTIVE' ? 'border-[#A3BE8C] bg-[#A3BE8C]/10 ring-1 ring-[#A3BE8C]/30' : 'border-[#4C566A]/60 hover:border-[#A3BE8C]/50'"
        >
            <div class="text-xs text-[#A5B1C2] font-semibold uppercase flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full bg-[#A3BE8C]"></span>
                Barcode Aktif
            </div>
            <div class="text-2xl font-bold font-mono text-[#A3BE8C] mt-1">{{ metrics.activeCount }}</div>
            <div class="text-[11px] text-[#A5B1C2] mt-0.5">Siap scan di rak gudang</div>
        </div>

        <!-- Card 3: Barcode Nonaktif -->
        <div
            @click="emit('selectStatus', 'INACTIVE')"
            class="cursor-pointer bg-[#3B4252] border rounded-xl p-4 transition-all"
            :class="filterStatus === 'INACTIVE' ? 'border-[#BF616A] bg-[#BF616A]/10 ring-1 ring-[#BF616A]/30' : 'border-[#4C566A]/60 hover:border-[#BF616A]/50'"
        >
            <div class="text-xs text-[#A5B1C2] font-semibold uppercase flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full bg-[#BF616A]"></span>
                Barcode Nonaktif
            </div>
            <div class="text-2xl font-bold font-mono text-[#BF616A] mt-1">{{ metrics.inactiveCount }}</div>
            <div class="text-[11px] text-[#A5B1C2] mt-0.5">Ditangguhkan / diganti</div>
        </div>

        <!-- Card 4: Total Pemindaian -->
        <div class="bg-[#3B4252] border border-[#4C566A]/60 rounded-xl p-4">
            <div class="text-xs text-[#A5B1C2] font-semibold uppercase flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full bg-[#88C0D0]"></span>
                Total Pemindaian
            </div>
            <div class="text-2xl font-bold font-mono text-[#88C0D0] mt-1">{{ metrics.totalScans }}</div>
            <div class="text-[11px] text-[#A5B1C2] mt-0.5">Akumulasi scan fisik</div>
        </div>
    </div>
</template>
