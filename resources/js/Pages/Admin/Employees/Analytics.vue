<template>
    <AdminLayout page-title="Employee Analytics">
        <Head title="Employee Analytics" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('admin.employees.index')"
                        class="w-10 h-10 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                    >
                        <i class="bi bi-arrow-left text-gray-600 dark:text-gray-300"></i>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Employee Analytics</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Visual overview of your workforce</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <select
                        v-model="period"
                        @change="reload"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                    >
                        <option value="6">Last 6 Months</option>
                        <option value="12">Last 12 Months</option>
                        <option value="24">Last 2 Years</option>
                    </select>
                    <button
                        @click="exportReport"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition shadow-lg"
                    >
                        <i class="bi bi-download"></i> Export
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white shadow-lg">
                            <i class="bi bi-people-fill text-lg"></i>
                        </div>
                        <span class="text-xs font-semibold text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/30 px-2 py-1 rounded-full">
                            {{ stats?.employeeGrowth || 0 }}%
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Total Employees</p>
                    <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ stats?.totalEmployees || 0 }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">vs last period</p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white shadow-lg">
                            <i class="bi bi-check-circle-fill text-lg"></i>
                        </div>
                        <span class="text-xs font-semibold text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/30 px-2 py-1 rounded-full">
                            {{ stats?.activeRate || 0 }}%
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Active</p>
                    <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ stats?.activeEmployees || 0 }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">of total workforce</p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-lg">
                            <i class="bi bi-cash-coin text-lg"></i>
                        </div>
                        <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30 px-2 py-1 rounded-full">
                            Monthly
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Total Payroll</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">৳{{ formatCurrency(stats?.totalSalary) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">average salary ৳{{ formatCurrency(stats?.averageSalary) }}</p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center text-white shadow-lg">
                            <i class="bi bi-graph-up-arrow text-lg"></i>
                        </div>
                        <span class="text-xs font-semibold text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/30 px-2 py-1 rounded-full">
                            Avg
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Attendance Rate</p>
                    <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ stats?.attendanceRate || 0 }}%</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">last 30 days</p>
                </div>
            </div>

            <!-- Charts Row 1 -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Employees by Department (Bar Chart) -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <i class="bi bi-building text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-gray-100">Employees by Department</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Distribution across departments</p>
                        </div>
                    </div>
                    <div class="p-6">
                        <div v-if="departmentsData.length" class="space-y-3">
                            <div v-for="dept in departmentsData" :key="dept.id" class="group">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ dept.name }}</span>
                                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">
                                        {{ dept.count }} ({{ dept.percentage }}%)
                                    </span>
                                </div>
                                <div class="h-2 w-full bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div
                                        class="h-full rounded-full transition-all duration-500"
                                        :style="{
                                            width: dept.percentage + '%',
                                            background: dept.color
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-400">
                            <i class="bi bi-inbox text-3xl mb-2"></i>
                            <p class="text-sm">No department data</p>
                        </div>
                    </div>
                </div>

                <!-- Gender/Status Distribution (Donut Chart) -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <i class="bi bi-pie-chart text-green-600 dark:text-green-400"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-gray-100">Status Distribution</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Active vs Inactive employees</p>
                        </div>
                    </div>
                    <div class="p-6 flex items-center justify-center gap-8 flex-wrap">
                        <!-- Donut -->
                        <div class="relative w-40 h-40">
                            <svg viewBox="0 0 100 100" class="transform -rotate-90 w-full h-full">
                                <!-- Background circle -->
                                <circle cx="50" cy="50" r="40" fill="none" stroke="currentColor" stroke-width="12" class="text-gray-100 dark:text-gray-700" />
                                <!-- Active (green) -->
                                <circle
                                    cx="50" cy="50" r="40"
                                    fill="none"
                                    stroke="#10b981"
                                    stroke-width="12"
                                    :stroke-dasharray="`${activePercent} ${100 - activePercent}`"
                                    stroke-dashoffset="0"
                                    stroke-linecap="round"
                                />
                                <!-- Inactive (gray) - drawn after active -->
                                <circle
                                    cx="50" cy="50" r="40"
                                    fill="none"
                                    stroke="#9ca3af"
                                    stroke-width="12"
                                    :stroke-dasharray="`${inactivePercent} ${100 - inactivePercent}`"
                                    :stroke-dashoffset="-activePercent"
                                    stroke-linecap="round"
                                />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                                    {{ stats?.totalEmployees || 0 }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Total</span>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-4 h-4 rounded-full bg-green-500"></div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Active</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ stats?.activeEmployees || 0 }} ({{ activePercent }}%)
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-4 h-4 rounded-full bg-gray-400"></div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Inactive</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ (stats?.totalEmployees || 0) - (stats?.activeEmployees || 0) }} ({{ inactivePercent }}%)
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 2 -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Salary Distribution -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <i class="bi bi-bar-chart text-amber-600 dark:text-amber-400"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-gray-100">Salary by Designation</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Average salary per designation</p>
                        </div>
                    </div>
                    <div class="p-6">
                        <div v-if="salaryByDesignation.length" class="space-y-3">
                            <div v-for="item in salaryByDesignation" :key="item.id" class="group">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ item.name }}</span>
                                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">
                                        ৳{{ formatCurrency(item.avg_salary) }}
                                    </span>
                                </div>
                                <div class="h-2 w-full bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div
                                        class="h-full bg-gradient-to-r from-amber-500 to-orange-500 rounded-full transition-all duration-500"
                                        :style="{ width: item.percentage + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-400">
                            <i class="bi bi-inbox text-3xl mb-2"></i>
                            <p class="text-sm">No designation data</p>
                        </div>
                    </div>
                </div>

                <!-- Attendance Trend (Line Chart - SVG based) -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                            <i class="bi bi-activity text-indigo-600 dark:text-indigo-400"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-gray-100">Attendance Trend</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Last 7 days</p>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex items-end justify-between gap-1 h-40">
                            <div
                                v-for="(day, index) in attendanceTrend"
                                :key="index"
                                class="flex-1 flex flex-col items-center gap-2"
                            >
                                <div class="flex-1 w-full flex items-end justify-center">
                                    <div
                                        class="w-full rounded-t-lg bg-gradient-to-t from-indigo-500 to-purple-500 transition-all duration-500 hover:from-indigo-600 hover:to-purple-600"
                                        :style="{ height: day.height + '%' }"
                                        :title="`${day.count} employees - ${day.date}`"
                                    ></div>
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ day.label }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Performers / Recent Activity -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                        <i class="bi bi-trophy text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 dark:text-gray-100">Top Performers</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Highest attendance this month</p>
                    </div>
                </div>
                <div class="p-6">
                    <div v-if="topPerformers.length" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div
                            v-for="(emp, index) in topPerformers"
                            :key="emp.id"
                            class="bg-gradient-to-br from-gray-50 to-white dark:from-gray-700/50 dark:to-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 relative overflow-hidden"
                        >
                            <!-- Rank Badge -->
                            <div
                                class="absolute top-3 right-3 w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold"
                                :class="{
                                    'bg-amber-400 text-white': index === 0,
                                    'bg-gray-300 text-gray-700': index === 1,
                                    'bg-orange-400 text-white': index === 2,
                                }"
                            >
                                #{{ index + 1 }}
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full overflow-hidden ring-2 ring-indigo-100 bg-gray-100 flex items-center justify-center shrink-0">
                                    <img
                                        v-if="emp.avatar_url"
                                        :src="emp.avatar_url"
                                        class="w-full h-full object-cover"
                                    />
                                    <img
                                        v-else
                                        :src="generateAvatar(emp)"
                                        class="w-full h-full object-cover"
                                    />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate">
                                        {{ emp.name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ emp.department }}</p>
                                </div>
                            </div>
                            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-600 flex items-center justify-between">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Attendance</span>
                                <span class="text-sm font-bold text-green-600 dark:text-green-400">
                                    {{ emp.attendance_rate }}%
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-400">
                        <i class="bi bi-trophy text-3xl mb-2"></i>
                        <p class="text-sm">No attendance data yet</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    stats:                { type: Object, default: () => ({}) },
    departmentsData:      { type: Array,  default: () => [] },
    salaryByDesignation:  { type: Array,  default: () => [] },
    attendanceTrend:      { type: Array,  default: () => [] },
    topPerformers:        { type: Array,  default: () => [] },
});

const period = ref('6');

/* ========== Donut Chart ========== */
const activePercent = computed(() => {
    const total = props.stats?.totalEmployees || 0;
    const active = props.stats?.activeEmployees || 0;
    if (total === 0) return 0;
    return Math.round((active / total) * 100);
});

const inactivePercent = computed(() => 100 - activePercent.value);

/* ========== Helpers ========== */
const formatCurrency = (value) => {
    if (!value) return '0';
    return Number(value).toLocaleString('en-BD');
};

const generateAvatar = (emp) => {
    const name = emp.name || 'Employee';
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=6366f1&color=fff&bold=true`;
};

const reload = () => {
    router.get(route('admin.employees.analytics'), { period: period.value }, {
        preserveScroll: true,
    });
};

const exportReport = () => {
    window.location.href = route('admin.employees.analytics.export', { period: period.value });
};
</script>
