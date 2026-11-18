<template>
    <div class="bg-white rounded-xl p-5 shadow-card flex items-center gap-4 hover:shadow-card-hover transition-all">
        <div
            class="w-14 h-14 rounded-xl flex items-center justify-center text-2xl text-white shrink-0"
            :class="`bg-${color}-600`"
        >
            <i :class="icon"></i>
        </div>
        <div class="flex-1">
            <p class="text-xs text-gray-500 uppercase tracking-wider">{{ label }}</p>
            <h3 class="text-2xl font-bold text-gray-800">{{ value }}</h3>
            <p v-if="subtext" class="text-xs mt-0.5" :class="subtextColor">
                {{ subtext }}
            </p>
        </div>
    </div>
</template>

<script setup>
defineProps({
    icon: {
        type: String,
        default: 'bi bi-info-circle',
    },
    label: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        default: 0,
    },
    color: {
        type: String,
        default: 'indigo',
    },
    subtext: String,
    subtextColor: {
        type: String,
        default: 'text-gray-500',
    },
});
</script>
