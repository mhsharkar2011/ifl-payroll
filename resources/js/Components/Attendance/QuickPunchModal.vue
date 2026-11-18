<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
        @click.self="$emit('close')"
    >
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Quick Punch</h3>
                <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="p-6">
                <slot />
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['close']);
</script>
