<script setup>
defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <label class="text-slate-700 mb-1.5 block text-sm font-medium">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
