<template>
    <form @submit.prevent="$emit('submit')" class="space-y-6">
        <!-- Name -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Name <span class="text-red-500">*</span>
            </label>
            <input
                v-model="form.name"
                type="text"
                placeholder="e.g., Human Resources"
                class="w-full px-4 py-2.5 border rounded-lg text-sm outline-none transition
                       bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100
                       focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40"
                :class="form.errors.name
                    ? 'border-red-400 focus:border-red-500'
                    : 'border-gray-300 dark:border-gray-600 focus:border-indigo-500'"
            />
            <p v-if="form.errors.name" class="text-xs text-red-600 mt-1">{{ form.errors.name }}</p>
        </div>

        <!-- Code -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Code
            </label>
            <input
                v-model="form.code"
                type="text"
                placeholder="e.g., HR"
                maxlength="20"
                class="w-full px-4 py-2.5 border rounded-lg text-sm outline-none transition
                       bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100
                       focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40"
                :class="form.errors.code
                    ? 'border-red-400 focus:border-red-500'
                    : 'border-gray-300 dark:border-gray-600 focus:border-indigo-500'"
            />
            <p v-if="form.errors.code" class="text-xs text-red-600 mt-1">{{ form.errors.code }}</p>
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Description
            </label>
            <textarea
                v-model="form.description"
                rows="3"
                placeholder="Optional details..."
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm outline-none transition
                       bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100
                       focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40"
            ></textarea>
            <p v-if="form.errors.description" class="text-xs text-red-600 mt-1">{{ form.errors.description }}</p>
        </div>

        <!-- Active -->
        <label class="flex items-center gap-3 cursor-pointer">
            <input
                type="checkbox"
                v-model="form.is_active"
                class="w-5 h-5 rounded accent-indigo-600"
            />
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                Active
            </span>
            <span class="text-xs text-gray-500 dark:text-gray-400">
                (inactive departments won't appear in employee dropdowns)
            </span>
        </label>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
            <Link
                :href="route('admin.departments.index')"
                class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600"
            >
                Cancel
            </Link>
            <button
                type="submit"
                :disabled="form.processing"
                class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 flex items-center gap-2"
            >
                <i v-if="form.processing" class="bi bi-arrow-repeat animate-spin"></i>
                <i v-else class="bi bi-check-lg"></i>
                {{ submitLabel }}
            </button>
        </div>
    </form>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    form:        { type: Object, required: true },
    submitLabel: { type: String, default: 'Save' },
});

defineEmits(['submit']);
</script>
