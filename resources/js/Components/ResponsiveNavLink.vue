<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-blue-900 text-start text-base font-semibold text-blue-900 bg-blue-50/60 focus:outline-none focus:text-blue-900 focus:bg-blue-100 focus:border-blue-900 transition duration-150 ease-in-out'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:text-slate-900 focus:bg-slate-50 focus:border-slate-300 transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
