<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="href"
        class="block w-full px-4 py-2 text-start text-sm leading-5 text-[#ECEFF4] transition duration-150 ease-in-out hover:bg-[#3B4252] hover:text-white focus:bg-[#3B4252] focus:outline-none"
    >
        <slot />
    </Link>
</template>
