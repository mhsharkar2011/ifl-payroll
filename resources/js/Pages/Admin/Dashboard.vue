<template>
    <AdminLayout page-title="Dashboard">
        <!-- Welcome Header -->
        <div class="mb-6 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 rounded-2xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32"></div>
            <div class="absolute bottom-0 right-20 w-40 h-40 bg-white/10 rounded-full -mb-20"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold mb-1">
                            স্বাগতম, {{ $page.props.auth?.user?.name || 'Admin' }}! 👋
                        </h1>
                        <p class="text-white/80 text-sm sm:text-base">
                            আজকের তারিখ: {{ today }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <Link
                            :href="route('admin.attendance.index')"
                            class="px-4 py-2 bg-white/20 backdrop-blur-sm rounded-lg text-sm font-medium hover:bg-white/30 transition flex items-center gap-2"
                        >
                            <i class="bi bi-arrow-repeat"></i>
                            <span class="hidden sm:inline">Sync Now</span>
                        </Link>
                        <Link
                            :href="route('admin.reports.attendance')"
                            class="px-4 py-2 bg-white text-indigo-600 rounded-lg text-sm font-medium hover:bg-gray-100 transition flex items-center gap-2"
                        >
                            <i class="bi bi-file-earmark-bar-graph"></i>
                            <span class="hidden sm:inline">Reports</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6 mb-6">
            <!-- Total Employees -->
            <div class="group bg-white rounded-2xl p-5 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-100">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white text-xl shadow-lg shadow-indigo-500/30 group-hover:scale-110 transition-transform">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-50 text-green-700 text-xs font-semibold rounded-full">
                        <i class="bi bi-arrow-up-short"></i> Active
                    </span>
                </div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Total Employees</p>
                <h3 class="text-3xl font-bold text-gray-800">{{ stats?.totalEmployees || 0 }}</h3>
                <div class="mt-3 h-1 w-full bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-full" style="width: 100%"></div>
                </div>
            </div>

            <!-- Today Present -->
            <div class="group bg-white rounded-2xl p-5 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-100">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white text-xl shadow-lg shadow-green-500/30 group-hover:scale-110 transition-transform">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-50 text-green-700 text-xs font-semibold rounded-full">
                        <i class="bi bi-check-circle-fill"></i> {{ getPresentRate() }}%
                    </span>
                </div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Today Present</p>
                <h3 class="text-3xl font-bold text-gray-800">{{ stats?.todayPresent || 0 }}</h3>
                <div class="mt-3 h-1 w-full bg-gray-100 rounded-full overflow-hidden">
                    <div
                        class="h-full bg-gradient-to-r from-green-500 to-emerald-600 rounded-full transition-all duration-500"
                        :style="{ width: getPresentRate() + '%' }"
                    ></div>
                </div>
            </div>

            <!-- Today Absent -->
            <div class="group bg-white rounded-2xl p-5 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-100">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center text-white text-xl shadow-lg shadow-red-500/30 group-hover:scale-110 transition-transform">
                        <i class="bi bi-person-x-fill"></i>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-red-50 text-red-700 text-xs font-semibold rounded-full">
                        <i class="bi bi-x-circle-fill"></i> {{ getAbsentRate() }}%
                    </span>
                </div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Today Absent</p>
                <h3 class="text-3xl font-bold text-gray-800">{{ stats?.todayAbsent || 0 }}</h3>
                <div class="mt-3 h-1 w-full bg-gray-100 rounded-full overflow-hidden">
                    <div
                        class="h-full bg-gradient-to-r from-red-500 to-rose-600 rounded-full transition-all duration-500"
                        :style="{ width: getAbsentRate() + '%' }"
                    ></div>
                </div>
            </div>

            <!-- Overtime -->
            <div class="group bg-white rounded-2xl p-5 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-100">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white text-xl shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-transform">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-amber-50 text-amber-700 text-xs font-semibold rounded-full">
                        <i class="bi bi-graph-up-arrow"></i> Month
                    </span>
                </div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Total Overtime</p>
                <h3 class="text-3xl font-bold text-gray-800">
                    {{ stats?.totalOvertime || 0 }}<span class="text-lg text-gray-400 ml-1">h</span>
                </h3>
                <div class="mt-3 h-1 w-full bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-amber-500 to-orange-600 rounded-full" style="width: 60%"></div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Attendance -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center">
                            <i class="bi bi-calendar-check text-indigo-600 text-lg"></i>
                        </div>
                        <div>
                            <h6 class="font-bold text-gray-800">Recent Attendance</h6>
                            <p class="text-xs text-gray-500">Last 10 records</p>
                        </div>
                    </div>
                    <Link
                        :href="route('admin.attendance.index')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition"
                    >
                        View All <i class="bi bi-arrow-right"></i>
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold">Employee</th>
                                <th class="px-6 py-3 text-left font-semibold">Time</th>
                                <th class="px-6 py-3 text-left font-semibold">Status</th>
                                <th class="px-6 py-3 text-left font-semibold">Verified</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="attendance in recentAttendance"
                                :key="attendance.id"
                                class="hover:bg-gray-50/70 transition"
                            >
                                <!-- ✅ Modified Employee Cell with Avatar -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="relative shrink-0">
                                            <div class="w-9 h-9 rounded-full overflow-hidden ring-2 ring-indigo-100 bg-gray-100 flex items-center justify-center">
                                                <i
                                                    v-if="imageFailed[attendance.id]"
                                                    class="bi bi-person-circle text-xl text-gray-400"
                                                ></i>

                                                <img
                                                    v-else-if="attendance.employee_avatar_url"
                                                    :src="attendance.employee_avatar_url"
                                                    :alt="attendance.employee_name"
                                                    class="w-full h-full object-cover"
                                                    @error="(e) => onAvatarError(e, attendance)"
                                                />

                                                <img
                                                    v-else
                                                    :src="getAvatar(attendance.employee_name)"
                                                    :alt="attendance.employee_name"
                                                    class="w-full h-full object-cover"
                                                />
                                            </div>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-800 truncate">
                                                {{ attendance.employee_name }}
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                ID: {{ attendance.employee_id || attendance.id }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="text-gray-700 font-medium">{{ formatTime(attendance.timestamp) }}</div>
                                    <div class="text-xs text-gray-400">{{ formatDate(attendance.timestamp) }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold"
                                        :class="getStatusClass(attendance.status)"
                                    >
                                        <i :class="getStatusIcon(attendance.status)" class="text-[10px]"></i>
                                        {{ formatStatus(attendance.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        class="w-8 h-8 rounded-lg flex items-center justify-center"
                                        :class="getVerifyBg(attendance.verified_by)"
                                        :title="getVerifyLabel(attendance.verified_by)"
                                    >
                                        <i
                                            :class="getVerifyIcon(attendance.verified_by)"
                                            class="text-sm"
                                        ></i>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="!recentAttendance || recentAttendance.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3 text-gray-400">
                                        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center">
                                            <i class="bi bi-inbox text-2xl"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-500">No attendance records found</p>
                                            <p class="text-xs mt-1">Records will appear here once employees check in</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="space-y-6">
                <!-- Payroll Summary -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                            <i class="bi bi-cash-coin text-green-600 text-lg"></i>
                        </div>
                        <div>
                            <h6 class="font-bold text-gray-800">Payroll Summary</h6>
                            <p class="text-xs text-gray-500">{{ currentMonth }}</p>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-green-500 flex items-center justify-center text-white">
                                    <i class="bi bi-wallet2"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Total Payroll</span>
                            </div>
                            <span class="font-bold text-green-700 text-lg">
                                ৳ {{ formatCurrency(payrollSummary?.total_payroll) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 bg-white border border-gray-100 rounded-xl text-center">
                                <div class="w-8 h-8 mx-auto rounded-lg bg-green-100 flex items-center justify-center mb-2">
                                    <i class="bi bi-check-circle-fill text-green-600"></i>
                                </div>
                                <p class="text-xs text-gray-500 mb-1">Paid</p>
                                <p class="text-xl font-bold text-gray-800">{{ payrollSummary?.paid_count || 0 }}</p>
                            </div>
                            <div class="p-3 bg-white border border-gray-100 rounded-xl text-center">
                                <div class="w-8 h-8 mx-auto rounded-lg bg-amber-100 flex items-center justify-center mb-2">
                                    <i class="bi bi-clock-fill text-amber-600"></i>
                                </div>
                                <p class="text-xs text-gray-500 mb-1">Pending</p>
                                <p class="text-xl font-bold text-gray-800">{{ payrollSummary?.pending_count || 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                            <i class="bi bi-lightning-charge-fill text-amber-500 text-lg"></i>
                        </div>
                        <div>
                            <h6 class="font-bold text-gray-800">Quick Actions</h6>
                            <p class="text-xs text-gray-500">Shortcuts to common tasks</p>
                        </div>
                    </div>
                    <div class="p-4 space-y-2">
                        <Link
                            :href="route('admin.employees.create')"
                            class="flex items-center gap-3 p-3 rounded-xl hover:bg-indigo-50 group transition"
                        >
                            <div class="w-9 h-9 rounded-lg bg-indigo-100 group-hover:bg-indigo-600 flex items-center justify-center transition">
                                <i class="bi bi-person-plus text-indigo-600 group-hover:text-white transition"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">Add Employee</span>
                            <i class="bi bi-chevron-right ml-auto text-gray-400 group-hover:text-indigo-600 text-xs"></i>
                        </Link>

                        <Link
                            :href="route('admin.attendance.index')"
                            class="flex items-center gap-3 p-3 rounded-xl hover:bg-green-50 group transition"
                        >
                            <div class="w-9 h-9 rounded-lg bg-green-100 group-hover:bg-green-600 flex items-center justify-center transition">
                                <i class="bi bi-arrow-repeat text-green-600 group-hover:text-white transition"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-green-700">Sync Attendance</span>
                            <i class="bi bi-chevron-right ml-auto text-gray-400 group-hover:text-green-600 text-xs"></i>
                        </Link>

                        <Link
                            :href="route('admin.payroll.generate')"
                            class="flex items-center gap-3 p-3 rounded-xl hover:bg-amber-50 group transition"
                        >
                            <div class="w-9 h-9 rounded-lg bg-amber-100 group-hover:bg-amber-600 flex items-center justify-center transition">
                                <i class="bi bi-calculator text-amber-600 group-hover:text-white transition"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-amber-700">Generate Payroll</span>
                            <i class="bi bi-chevron-right ml-auto text-gray-400 group-hover:text-amber-600 text-xs"></i>
                        </Link>

                        <Link
                            :href="route('admin.reports.attendance')"
                            class="flex items-center gap-3 p-3 rounded-xl hover:bg-blue-50 group transition"
                        >
                            <div class="w-9 h-9 rounded-lg bg-blue-100 group-hover:bg-blue-600 flex items-center justify-center transition">
                                <i class="bi bi-file-earmark-bar-graph text-blue-600 group-hover:text-white transition"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">View Reports</span>
                            <i class="bi bi-chevron-right ml-auto text-gray-400 group-hover:text-blue-600 text-xs"></i>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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
    payrollSummary: {
        type: Object,
        default: () => ({}),
    },
});

/* ========== Avatar Logic ========== */
const imageFailed = ref({});

/**
 * Generate avatar URL from name
 */
const getAvatar = (name) => {
    const safeName = encodeURIComponent(name || 'User');
    return `https://ui-avatars.com/api/?name=${safeName}&background=6366f1&color=fff&bold=true&size=64`;
};

/**
 * Handle image load error - fallback to generated avatar
 */
const onAvatarError = (event, attendance) => {
    // Prevent infinite loop
    if (event.target.src.includes('ui-avatars.com')) {
        imageFailed.value[attendance.id] = true;
        return;
    }
    // Fallback to UI Avatar
    event.target.src = getAvatar(attendance.employee_name);
};

/* ========== Date & Time Helpers ========== */
const today = computed(() => {
    return new Date().toLocaleDateString('bn-BD', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
});

const currentMonth = computed(() => {
    return new Date().toLocaleDateString('en-US', {
        month: 'long',
        year: 'numeric',
    });
});

/* ========== Rate Calculations ========== */
const getPresentRate = () => {
    const total = props.stats?.totalEmployees || 0;
    const present = props.stats?.todayPresent || 0;
    if (total === 0) return 0;
    return Math.round((present / total) * 100);
};

const getAbsentRate = () => {
    const total = props.stats?.totalEmployees || 0;
    const absent = props.stats?.todayAbsent || 0;
    if (total === 0) return 0;
    return Math.round((absent / total) * 100);
};

/* ========== Formatters ========== */
const formatCurrency = (value) => {
    if (!value) return '0';
    return Number(value).toLocaleString('en-BD');
};

const formatTime = (timestamp) => {
    if (!timestamp) return '-';
    const date = new Date(timestamp);
    return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
    });
};

const formatDate = (timestamp) => {
    if (!timestamp) return '-';
    const date = new Date(timestamp);
    return date.toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const formatStatus = (status) => {
    const map = {
        check_in: 'Check In',
        check_out: 'Check Out',
        break_in: 'Break In',
        break_out: 'Break Out',
        overtime_in: 'OT In',
        overtime_out: 'OT Out',
    };
    return map[status] || status;
};

/* ========== Status Styling ========== */
const getStatusClass = (status) => {
    const map = {
        check_in: 'bg-green-50 text-green-700 ring-1 ring-green-200',
        check_out: 'bg-red-50 text-red-700 ring-1 ring-red-200',
        break_in: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
        break_out: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
        overtime_in: 'bg-blue-50 text-blue-700 ring-1 ring-blue-200',
        overtime_out: 'bg-blue-50 text-blue-700 ring-1 ring-blue-200',
    };
    return map[status] || 'bg-gray-50 text-gray-700 ring-1 ring-gray-200';
};

const getStatusIcon = (status) => {
    const map = {
        check_in: 'bi bi-box-arrow-in-right',
        check_out: 'bi bi-box-arrow-right',
        break_in: 'bi bi-cup-hot',
        break_out: 'bi bi-cup-hot-fill',
        overtime_in: 'bi bi-clock-history',
        overtime_out: 'bi bi-clock-fill',
    };
    return map[status] || 'bi bi-circle';
};

/* ========== Verification Styling ========== */
const getVerifyBg = (type) => {
    const map = {
        0: 'bg-gray-100',
        1: 'bg-indigo-100',
        2: 'bg-purple-100',
        3: 'bg-blue-100',
    };
    return map[type] || 'bg-gray-100';
};

const getVerifyIcon = (type) => {
    const map = {
        0: 'bi bi-person-badge text-gray-600',
        1: 'bi bi-fingerprint text-indigo-600',
        2: 'bi bi-person-bounding-box text-purple-600',
        3: 'bi bi-credit-card-2-front text-blue-600',
    };
    return map[type] || 'bi bi-question-circle text-gray-500';
};

const getVerifyLabel = (type) => {
    const map = {
        0: 'Manual/Password',
        1: 'Fingerprint',
        2: 'Face Recognition',
        3: 'Card',
    };
    return map[type] || 'Unknown';
};
</script>
