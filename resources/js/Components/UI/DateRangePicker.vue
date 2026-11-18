<template>
    <div class="relative">
        <div class="flex items-center gap-2">
            <div class="relative flex-1">
                <i class="bi bi-calendar-event absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input
                    type="date"
                    :value="startDate"
                    @input="$emit('update:startDate', $event.target.value)"
                    class="pl-10 pr-4 py-2 w-full bg-white border border-gray-300 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
                />
            </div>
            <span class="text-gray-400 text-sm">to</span>
            <div class="relative flex-1">
                <i class="bi bi-calendar-event absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input
                    type="date"
                    :value="endDate"
                    @input="$emit('update:endDate', $event.target.value)"
                    class="pl-10 pr-4 py-2 w-full bg-white border border-gray-300 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
                />
            </div>
            <button
                v-if="startDate || endDate"
                @click="clear"
                class="p-2 text-gray-400 hover:text-red-500 transition"
                title="Clear dates"
            >
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Quick Presets -->
        <div v-if="showPresets" class="flex flex-wrap gap-2 mt-2">
            <button
                v-for="preset in presets"
                :key="preset.label"
                @click="applyPreset(preset)"
                class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-600 hover:bg-indigo-100 hover:text-indigo-700 transition"
            >
                {{ preset.label }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    startDate: {
        type: String,
        default: '',
    },
    endDate: {
        type: String,
        default: '',
    },
    showPresets: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['update:startDate', 'update:endDate', 'change']);

const presets = [
    { label: 'Today', days: 0 },
    { label: 'Yesterday', days: -1 },
    { label: 'Last 7 days', days: 7 },
    { label: 'Last 30 days', days: 30 },
    { label: 'This Month', month: true },
    { label: 'Last Month', lastMonth: true },
];

const formatDate = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const applyPreset = (preset) => {
    const today = new Date();
    let start, end;

    if (preset.days === 0) {
        start = end = formatDate(today);
    } else if (preset.days === -1) {
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);
        start = end = formatDate(yesterday);
    } else if (preset.days) {
        const from = new Date(today);
        from.setDate(from.getDate() - preset.days + 1);
        start = formatDate(from);
        end = formatDate(today);
    } else if (preset.month) {
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        start = formatDate(firstDay);
        end = formatDate(lastDay);
    } else if (preset.lastMonth) {
        const firstDay = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth(), 0);
        start = formatDate(firstDay);
        end = formatDate(lastDay);
    }

    emit('update:startDate', start);
    emit('update:endDate', end);
    emit('change', { startDate: start, endDate: end });
};

const clear = () => {
    emit('update:startDate', '');
    emit('update:endDate', '');
    emit('change', { startDate: '', endDate: '' });
};
</script>
