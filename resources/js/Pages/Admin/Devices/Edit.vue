<template>
    <AdminLayout page-title="Edit Device">
        <Head title="Edit Device" />

        <div class="max-w-2xl mx-auto">
            <div class="mb-6 flex items-center gap-4">
                <Link :href="route('admin.devices.index')" class="w-10 h-10 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50">
                    <i class="bi bi-arrow-left text-gray-600"></i>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Edit Device</h1>
                    <p class="text-sm text-gray-500">{{ device.name }}</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Device Name *</label>
                    <input v-model="form.name" type="text" class="w-full px-4 py-2.5 border rounded-lg text-sm" :class="form.errors.name ? 'border-red-400' : 'border-gray-300'" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">IP Address *</label>
                        <input v-model="form.ip_address" type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-mono" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Port *</label>
                        <input v-model="form.port" type="number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-mono" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Model</label>
                        <input v-model="form.model" type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                        <input v-model="form.password" type="number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-mono" />
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input v-model="form.is_active" type="checkbox" id="is_active" class="rounded" />
                    <label for="is_active" class="text-sm font-medium text-gray-700">Active Device</label>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Notes</label>
                    <textarea v-model="form.notes" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <Link :href="route('admin.devices.index')" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-500 rounded-lg disabled:opacity-50 flex items-center gap-2">
                        <i v-if="form.processing" class="bi bi-arrow-repeat animate-spin"></i>
                        <i v-else class="bi bi-check-lg"></i>
                        Update Device
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    device: { type: Object, required: true },
});

const form = useForm({
    name: props.device.name || '',
    ip_address: props.device.ip_address || '',
    port: props.device.port || 4370,
    password: props.device.password || 0,
    model: props.device.model || '',
    notes: props.device.notes || '',
    is_active: props.device.is_active ?? true,
});

const submit = () => {
    form.post(route('admin.devices.update', props.device.id));
};
</script>
