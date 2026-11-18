<template>
    <EmployeeLayout page-title="Dashboard">
        <Head title="My Dashboard" />

        <div class="space-y-6">
            <!-- Welcome Header -->
            <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 rounded-2xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32"></div>
                <div class="relative z-10">
                    <h1 class="text-2xl sm:text-3xl font-bold mb-1">
                        স্বাগতম, {{ user?.name }}! 👋
                    </h1>
                    <p class="text-white/80 text-sm sm:text-base">
                        {{ today }}
                    </p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-11 h-11 rounded-lg bg-green-100 flex items-center justify-center">
                            <i class="bi bi-check-circle-fill text-green-600 text-lg"></i>
                        </div>
                        <span class="text-xs font-medium text-gray-500 uppercase">Present Days</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-800">{{ stats?.presentDays || 0 }}</p>
                    <p class="text-xs text-gray-500 mt-1">This month</p>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-11 h-11 rounded-lg bg-red-100 flex items-center justify-center">
                            <i class="bi bi-x-circle-fill text-red-600 text-lg"></i>
                        </div>
                        <span class="text-xs font-medium text-gray-500 uppercase">Absent Days</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-800">{{ stats?.absentDays || 0 }}</p>
                    <p class="text-xs text-gray-500 mt-1">This month</p>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-11 h-11 rounded-lg bg-amber-100 flex items-center justify-center">
                            <i class="bi bi-clock-history text-amber-600 text-lg"></i>
                        </div>
                        <span class="text-xs font-medium text-gray-500 uppercase">Overtime</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-800">{{ stats?.overtimeHours || 0 }}h</p>
                    <p class="text-xs text-gray-500 mt-1">This month</p>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-11 h-11 rounded-lg bg-indigo-100 flex items-center justify-center">
                            <i class="bi bi-cash-coin text-indigo-600 text-lg"></i>
                        </div>
                        <span class="text-xs font-medium text-gray-500 uppercase">Last Salary</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-800">
                        ৳{{ formatCurrency(stats?.lastSalary || 0) }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Last month</p>
                </div>
            </div>

            <!-- Quick Actions & Recent Attendance -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Attendance -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center">
                                <i class="bi bi-calendar-check text-indigo-600"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800">Recent Attendance</h3>
                                <p class="text-xs text-gray-500">Last 5 records</p>
                            </div>
                        </div>
                        <Link
                            :href="route('employee.attendance')"
                            class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1"
                        >
                            View All <i class="bi bi-arrow-right"></i>
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold">Date</th>
                                    <th class="px-6 py-3 text-left font-semibold">Check In</th>
                                    <th class="px-6 py-3 text-left font-semibold">Check Out</th>
                                    <th class="px-6 py-3 text-left font-semibold">Hours</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="record in recentAttendance" :key="record.id" class="hover:bg-gray-50/70">
                                    <td class="px-6 py-3 text-gray-700">{{ record.date }}</td>
                                    <td class="px-6 py-3 text-gray-700">
                                        <span class="inline-flex items-center gap-1 text-green-600">
                                            <i class="bi bi-box-arrow-in-right"></i>
                                            {{ record.check_in || '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-gray-700">
                                        <span class="inline-flex items-center gap-1 text-red-600">
                                            <i class="bi bi-box-arrow-right"></i>
                                            {{ record.check_out || '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-gray-700 font-medium">
                                        {{ record.hours || '0' }}h
                                    </td>
                                </tr>
                                <tr v-if="!recentAttendance?.length">
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        <i class="bi bi-inbox text-3xl block mb-2 text-gray-300"></i>
                                        No attendance records
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quick Info -->
                <div class="space-y-6">
                    <!-- Profile Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-purple-50">
                            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                <i class="bi bi-person-badge text-indigo-600"></i>
                                My Profile
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Employee ID</span>
                                <span class="font-medium text-gray-800">{{ employee?.employee_id || 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Department</span>
                                <span class="font-medium text-gray-800">{{ employee?.department || 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Designation</span>
                                <span class="font-medium text-gray-800">{{ employee?.designation || 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Joined</span>
                                <span class="font-medium text-gray-800">{{ employee?.joining_date || 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-orange-50">
                            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                <i class="bi bi-lightning-charge-fill text-amber-500"></i>
                                Quick Links
                            </h3>
                        </div>
                        <div class="p-3 space-y-1">
                            <Link
                                :href="route('employee.attendance')"
                                class="flex items-center gap-3 p-3 rounded-lg hover:bg-indigo-50 transition group"
                            >
                                <div class="w-9 h-9 rounded-lg bg-indigo-100 flex items-center justify-center group-hover:bg-indigo-600 transition">
                                    <i class="bi bi-calendar-check text-indigo-600 group-hover:text-white"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700">My Attendance</span>
                                <i class="bi bi-chevron-right ml-auto text-gray-400 text-xs"></i>
                            </Link>
                            <Link
                                :href="route('employee.payroll')"
                                class="flex items-center gap-3 p-3 rounded-lg hover:bg-green-50 transition group"
                            >
                                <div class="w-9 h-9 rounded-lg bg-green-100 flex items-center justify-center group-hover:bg-green-600 transition">
                                    <i class="bi bi-cash-coin text-green-600 group-hover:text-white"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700">My Payslips</span>
                                <i class="bi bi-chevron-right ml-auto text-gray-400 text-xs"></i>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </EmployeeLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';

const page = usePage();

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },
    recentAttendance: {
        type: Array,
        default: () => [],
    },
    employee: {
        type: Object,
        default: () => ({}),
    },
});

const user = computed(() => page.props.auth?.user);
const today = computed(() =>
    new Date().toLocaleDateString('bn-BD', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    })
);

const formatCurrency = (value) => {
    if (!value) return '0';
    return Number(value).toLocaleString('en-BD');
};
</script>
