<template>
    <div
        class="relative rounded-full overflow-hidden ring-2 ring-indigo-100 dark:ring-indigo-900/30 bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center shrink-0"
        :class="sizeClass"
    >
        <img
            v-if="!imageFailed && employee.avatar_url"
            :src="employee.avatar_url"
            :alt="employee.full_name || 'Employee'"
            class="w-full h-full object-cover transition-opacity duration-300"
            @error="onImageError"
            @load="onImageLoad"
        />
        <span v-else class="font-bold text-indigo-600 dark:text-indigo-400" :class="textClass">
            {{ initials }}
        </span>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    employee: { type: Object, required: true },
    size:     { type: String, default: 'md' } // sm, md, lg
});

const imageFailed = ref(false);

// ✅ Reset error state if the avatar_url changes (e.g., user uploads a new photo)
watch(
    () => props.employee.avatar_url,
    () => {
        imageFailed.value = false;
    }
);

const onImageError = () => {
    imageFailed.value = true;
};

const onImageLoad = (e) => {
    // Fade in the image when it loads
    e.target.style.opacity = '1';
};

const initials = computed(() => {
    const first = props.employee.first_name ? props.employee.first_name[0] : '';
    const last = props.employee.last_name ? props.employee.last_name[0] : '';
    return (first + last).toUpperCase() || 'E';
});

const sizeClass = computed(() => {
    switch (props.size) {
        case 'lg': return 'w-16 h-16 ring-3';
        case 'sm': return 'w-8 h-8';
        default:   return 'w-10 h-10'; // md
    }
});

const textClass = computed(() => {
    switch (props.size) {
        case 'lg': return 'text-xl';
        case 'sm': return 'text-xs';
        default:   return 'text-sm'; // md
    }
});
</script>
