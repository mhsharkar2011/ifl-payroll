<template>
    <AdminLayout page-title="Add Device">
        <Head title="Add Device" />

        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.devices.index')"
                    class="w-10 h-10 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                >
                    <i class="bi bi-arrow-left text-gray-600 dark:text-gray-300"></i>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Add ZKTeco Device</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Configure your biometric device</p>
                </div>
            </div>

            <!-- Error Summary -->
            <div
                v-if="Object.keys(form.errors).length > 0"
                class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4"
            >
                <div class="flex items-start gap-3">
                    <i class="bi bi-exclamation-triangle-fill text-red-500 text-xl shrink-0"></i>
                    <div class="flex-1">
                        <h4 class="font-semibold text-red-800 dark:text-red-300 mb-2">Please fix these errors:</h4>
                        <ul class="text-sm text-red-700 dark:text-red-300 space-y-1">
                            <li v-for="(error, field) in form.errors" :key="field" class="flex items-start gap-2">
                                <i class="bi bi-dot"></i>
                                <span><strong>{{ formatFieldName(field) }}:</strong> {{ error }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Information -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-indigo-600 flex items-center justify-center text-white">
                                <i class="bi bi-router"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 dark:text-gray-100">Basic Information</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Device name and model</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Name -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Device Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="e.g., Main Entrance"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none transition"
                                :class="form.errors.name ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600'"
                            />
                            <p v-if="form.errors.name" class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                                <i class="bi bi-exclamation-circle"></i> {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Model -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Model
                            </label>
                            <input
                                v-model="form.model"
                                type="text"
                                placeholder="e.g., MB460, K40, K14"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
                            />
                        </div>

                        <!-- Serial Number -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Serial Number
                            </label>
                            <input
                                v-model="form.serial_number"
                                type="text"
                                placeholder="e.g., QWC5234400231"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm font-mono focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
                            />
                        </div>
                    </div>
                </div>

                <!-- Network Configuration -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-green-600 flex items-center justify-center text-white">
                                <i class="bi bi-wifi"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 dark:text-gray-100">Network Configuration</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">IP address and connection settings</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- IP Address -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                IP Address <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="bi bi-globe absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input
                                    v-model="form.ip_address"
                                    type="text"
                                    placeholder="192.168.1.100"
                                    class="w-full pl-10 pr-4 py-2.5 border rounded-lg text-sm font-mono bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
                                    :class="form.errors.ip_address ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600'"
                                />
                            </div>
                            <p v-if="form.errors.ip_address" class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                                <i class="bi bi-exclamation-circle"></i> {{ form.errors.ip_address }}
                            </p>
                        </div>

                        <!-- Port -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Port <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.port"
                                type="number"
                                min="1"
                                max="65535"
                                placeholder="4370"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm font-mono bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
                                :class="form.errors.port ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600'"
                            />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                <i class="bi bi-info-circle"></i> ZKTeco default: 4370
                            </p>
                            <p v-if="form.errors.port" class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                                {{ form.errors.port }}
                            </p>
                        </div>

                        <!-- Timeout -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Timeout (seconds)
                            </label>
                            <input
                                v-model="form.timeout"
                                type="number"
                                min="5"
                                max="300"
                                placeholder="30"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm font-mono focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
                            />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                <i class="bi bi-info-circle"></i> Connection timeout (5-300 sec)
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Communication Security -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-600 flex items-center justify-center text-white">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 dark:text-gray-100">Communication Security</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Device ID and password</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Device ID -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Device ID
                            </label>
                            <input
                                v-model="form.device_id"
                                type="number"
                                min="1"
                                max="254"
                                placeholder="1"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm font-mono focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
                            />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                <i class="bi bi-info-circle"></i> Machine number (1-254)
                            </p>
                        </div>

                        <!-- Comm Password -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Comm Password (Comm Key)
                            </label>
                            <div class="relative">
                                <i class="bi bi-key absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input
                                    v-model="form.password"
                                    type="number"
                                    placeholder="0"
                                    class="w-full pl-10 pr-12 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm font-mono focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
                                />
                                <button
                                    type="button"
                                    @click="togglePasswordVisibility"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                >
                                    <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                <i class="bi bi-info-circle"></i> Leave 0 if no password set
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Status & Notes -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center text-white">
                                <i class="bi bi-toggles"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 dark:text-gray-100">Status & Notes</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Device activation and additional info</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-5">
                        <!-- is_active Toggle -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                    <i class="bi bi-power text-green-600 dark:text-green-400"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800 dark:text-gray-100 text-sm">Active Device</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Enable this device for syncing</p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="sr-only peer"
                                />
                                <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-green-600 transition-colors relative">
                                    <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full transition-transform peer-checked:translate-x-5"></div>
                                </div>
                            </label>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Notes
                            </label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                placeholder="Additional notes about this device..."
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition resize-none"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Test Connection Preview -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-4">
                    <div class="flex items-start gap-3">
                        <i class="bi bi-lightbulb-fill text-blue-500 text-xl shrink-0"></i>
                        <div class="text-sm text-blue-800 dark:text-blue-300">
                            <p class="font-semibold mb-1">Connection Test Preview:</p>
                            <p class="font-mono text-xs bg-white dark:bg-gray-800 p-2 rounded border border-blue-200 dark:border-blue-700">
                                {{ form.ip_address || '192.168.1.94' }}:{{ form.port || 4370 }}
                            </p>
                            <p class="mt-2 text-xs">
                                <i class="bi bi-info-circle"></i> Save the device first, then test connection from the device list.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <Link
                        :href="route('admin.devices.index')"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition flex items-center gap-2"
                    >
                        <i class="bi bi-x-lg"></i> Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 shadow-lg"
                    >
                        <i v-if="form.processing" class="bi bi-arrow-repeat animate-spin"></i>
                        <i v-else class="bi bi-check-lg"></i>
                        {{ form.processing ? 'Saving...' : 'Save Device' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const showPassword = ref(false);

/* ========== Form (matches migration columns) ========== */
const form = useForm({
    name:          'Head Office',
    ip_address:    '192.168.1.100',
    port:          4370,
    device_id:     1,
    password:      1324,
    timeout:       30,
    serial_number: 'QWC5234400231',
    model:         'MB460',
    platform:      '',
    version:       '',
    is_active:     true,
    notes:         '',
});

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const submit = () => {
    form.post(route('admin.devices.store'), {
        preserveScroll: true,
    });
};

const formatFieldName = (field) => {
    return field
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
};
</script>
