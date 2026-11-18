<template>
    <div class="fixed top-4 right-4 z-[9999] space-y-2 pointer-events-none">
        <transition-group
            enter-active-class="transition ease-out duration-300"
            enter-from-class="translate-x-full opacity-0"
            enter-to-class="translate-x-0 opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="translate-x-0 opacity-100"
            leave-to-class="translate-x-full opacity-0"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="pointer-events-auto flex items-start gap-3 w-80 px-4 py-3 rounded-xl shadow-lg border backdrop-blur-sm"
                :class="toastClass(toast.type)"
            >
                <div class="shrink-0 mt-0.5">
                    <i :class="toastIcon(toast.type)" class="text-xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p v-if="toast.title" class="font-semibold text-sm">
                        {{ toast.title }}
                    </p>
                    <p class="text-sm" :class="{ 'mt-0.5': toast.title }">
                        {{ toast.message }}
                    </p>
                </div>
                <button
                    @click="remove(toast.id)"
                    class="shrink-0 opacity-60 hover:opacity-100 transition"
                >
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>
        </transition-group>
    </div>
</template>

<script setup>
import { useToast } from '@/Composables/useToast';

const { toasts, remove } = useToast();

const toastClass = (type) => {
    const classes = {
        success: 'bg-green-50 border-green-200 text-green-800',
        error: 'bg-red-50 border-red-200 text-red-800',
        warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
        info: 'bg-blue-50 border-blue-200 text-blue-800',
    };
    return classes[type] || classes.info;
};

const toastIcon = (type) => {
    const icons = {
        success: 'bi bi-check-circle-fill text-green-600',
        error: 'bi bi-exclamation-circle-fill text-red-600',
        warning: 'bi bi-exclamation-triangle-fill text-yellow-600',
        info: 'bi bi-info-circle-fill text-blue-600',
    };
    return icons[type] || icons.info;
};
</script>
