<template>
    <select
        :value="modelValue"
        @change="$emit('update:modelValue', $event.target.value)"
        class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
    >
        <option value="">{{ placeholder }}</option>
        <option
            v-for="option in options"
            :key="option.value"
            :value="option.value"
        >
            {{ option.label }}
        </option>
    </select>
</template>

<script setup>
defineProps({
    modelValue: [String, Number],
    options: {
        type: Array,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: 'Select...',
    },
});

defineEmits(['update:modelValue']);
</script>
