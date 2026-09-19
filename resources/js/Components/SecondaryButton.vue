<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors duration-150 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2 active:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
    >
        <slot />
    </button>
</template>
