<template>
    <div class="relative">
        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
        <input
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            type="text"
            :placeholder="placeholder"
            class="pl-10 pr-4 py-2 w-full bg-white border border-gray-300 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
        />
    </div>
</template>

<script setup>
defineProps({
    modelValue: String,
    placeholder: {
        type: String,
        default: 'Search...',
    },
});

defineEmits(['update:modelValue']);
</script>
