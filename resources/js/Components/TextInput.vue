<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="border-slate-300 text-slate-900 placeholder:text-slate-400 disabled:bg-slate-50 disabled:text-slate-500 block w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm shadow-sm transition-colors duration-150 focus:border-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-900/20 disabled:cursor-not-allowed"
        v-model="model"
        ref="input"
    />
</template>
