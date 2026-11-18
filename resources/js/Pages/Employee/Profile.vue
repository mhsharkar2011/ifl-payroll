<template>
    <EmployeeLayout page-title="My Profile">
        <Head title="My Profile" />
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Profile Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex items-center gap-4">
                    <img
                        :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(employee?.first_name + ' ' + employee?.last_name)}&background=fff&color=6366f1&bold=true&size=128`"
                        class="w-20 h-20 rounded-full ring-4 ring-white/30"
                    />
                    <div>
                        <h2 class="text-2xl font-bold">{{ employee?.first_name }} {{ employee?.last_name }}</h2>
                        <p class="text-white/80">{{ employee?.designation }} • {{ employee?.department }}</p>
                        <p class="text-white/70 text-sm mt-1">{{ employee?.email }}</p>
                    </div>
                </div>
            </div>

            <!-- Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Employee ID</p>
                    <p class="text-sm font-medium text-gray-800">{{ employee?.employee_id || 'N/A' }}</p>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Phone</p>
                    <p class="text-sm font-medium text-gray-800">{{ employee?.phone || 'N/A' }}</p>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Joining Date</p>
                    <p class="text-sm font-medium text-gray-800">{{ employee?.joining_date || 'N/A' }}</p>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Salary</p>
                    <p class="text-sm font-medium text-gray-800">৳{{ Number(employee?.salary || 0).toLocaleString('en-BD') }}</p>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 md:col-span-2">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Address</p>
                    <p class="text-sm font-medium text-gray-800">{{ employee?.address || 'Not provided' }}</p>
                </div>
            </div>
        </div>
    </EmployeeLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';

defineProps({
    employee: {
        type: Object,
        default: () => ({}),
    },
});
</script>
