<template>
    <EmployeeLayout page-title="My Attendance">
        <Head title="My Attendance" />
        <div class="space-y-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-xl font-bold text-gray-800">My Attendance Records</h2>
                <p class="text-sm text-gray-500 mt-1">View your check-in/check-out history</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold">Date</th>
                                <th class="px-6 py-3 text-left font-semibold">Time</th>
                                <th class="px-6 py-3 text-left font-semibold">Status</th>
                                <th class="px-6 py-3 text-left font-semibold">Verified</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="attendance in attendances?.data || []" :key="attendance.id" class="hover:bg-gray-50/70">
                                <td class="px-6 py-3 text-gray-700">{{ formatDate(attendance.timestamp) }}</td>
                                <td class="px-6 py-3 text-gray-700">{{ formatTime(attendance.timestamp) }}</td>
                                <td class="px-6 py-3">
                                    <span :class="statusClass(attendance.status)" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold">
                                        {{ formatStatus(attendance.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-gray-500 text-xs">{{ verifyLabel(attendance.verified_by) }}</td>
                            </tr>
                            <tr v-if="!attendances?.data?.length">
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                    <i class="bi bi-inbox text-3xl block mb-2 text-gray-300"></i>
                                    No attendance records found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </EmployeeLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';

defineProps({
    attendances: {
        type: Object,
        default: () => ({ data: [] }),
    },
});

const formatDate = (ts) => ts ? new Date(ts).toLocaleDateString('en-GB') : '-';
const formatTime = (ts) => ts ? new Date(ts).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }) : '-';
const formatStatus = (s) => ({
    check_in: 'Check In',
    check_out: 'Check Out',
    break_in: 'Break In',
    break_out: 'Break Out',
}[s] || s);
const statusClass = (s) => ({
    check_in: 'bg-green-50 text-green-700 ring-1 ring-green-200',
    check_out: 'bg-red-50 text-red-700 ring-1 ring-red-200',
    break_in: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
    break_out: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
}[s] || 'bg-gray-50 text-gray-700 ring-1 ring-gray-200');
const verifyLabel = (v) => ({ 0: 'Manual', 1: 'Fingerprint', 2: 'Face', 3: 'Card' }[v] || 'Unknown');
</script>
