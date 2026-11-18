<template>
    <AdminLayout page-title="Attendance Details">
        <Head :title="`Attendance - ${employeeName}`" />

        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('admin.attendance.index')"
                        class="w-10 h-10 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                    >
                        <i class="bi bi-arrow-left text-gray-600 dark:text-gray-300"></i>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Attendance Details</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Record for <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ employeeName }}</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('admin.attendance.edit', attendance.id)"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-500 rounded-lg hover:from-amber-600 hover:to-orange-600 transition shadow-lg shadow-amber-500/30"
                    >
                        <i class="bi bi-pencil"></i> Edit
                    </Link>
                    <button
                        @click="confirmDelete"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition"
                    >
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
            </div>

            <!-- Flash -->
            <div v-if="$page.props.flash?.success"
                 class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 flex items-center gap-3">
                <i class="bi bi-check-circle-fill text-green-500 text-xl"></i>
                <p class="text-sm text-green-700 dark:text-green-300 font-medium">{{ $page.props.flash.success }}</p>
            </div>

            <!-- Hero card -->
            <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 rounded-2xl shadow-lg overflow-hidden">
                <div class="p-8 text-white relative">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32"></div>
                    <div class="absolute bottom-0 right-20 w-40 h-40 bg-white/10 rounded-full -mb-20"></div>

                    <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center gap-6">
                        <div class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0">
                            <i :class="[statusIcon, 'text-4xl']"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs uppercase font-semibold opacity-80 mb-1">Attendance Event</p>
                            <h2 class="text-3xl font-bold">{{ formatAttendanceStatus(attendance.status) }}</h2>

                            <div class="flex flex-wrap items-center gap-3 mt-4">
                                <span class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-sm">
                                    <i class="bi bi-person"></i>
                                    {{ employeeName }}
                                </span>
                                <span class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-sm">
                                    <i class="bi bi-calendar"></i>
                                    {{ formatDate(attendance.timestamp) }}
                                </span>
                                <span class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-sm">
                                    <i class="bi bi-clock"></i>
                                    {{ formatTime(attendance.timestamp) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                            <i class="bi bi-hash text-indigo-600 dark:text-indigo-400 text-lg"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-medium">Employee ID</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-gray-100 truncate font-mono">
                                {{ attendance.employee?.employee_id || '—' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                            <i class="bi bi-shield-check text-purple-600 dark:text-purple-400 text-lg"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-medium">Verified By</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-gray-100 truncate">
                                {{ verifyLabel }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <i class="bi bi-clock-history text-amber-600 dark:text-amber-400 text-lg"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-medium">Recorded At</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-gray-100 truncate">
                                {{ formatTime(attendance.timestamp) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two-column layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Record details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Attendance details -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-indigo-600 flex items-center justify-center text-white">
                                    <i class="bi bi-info-circle-fill"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 dark:text-gray-100">Record Details</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Full attendance record information</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold mb-1">Record ID</p>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-100 font-mono">#{{ attendance.id }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold mb-1">Status</p>
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                    :class="statusClass"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    {{ formatAttendanceStatus(attendance.status) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold mb-1">Timestamp</p>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                    {{ formatDate(attendance.timestamp) }} — {{ formatTime(attendance.timestamp) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold mb-1">Verification Method</p>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-100 flex items-center gap-2">
                                    <i :class="verifyIcon"></i>
                                    {{ verifyLabel }}
                                </p>
                            </div>
                            <div class="sm:col-span-2">
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold mb-1">Device Serial</p>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-100 font-mono">
                                    {{ attendance.device_serial || '—' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold mb-1">Created</p>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                    {{ formatDateTime(attendance.created_at) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold mb-1">Last Updated</p>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                    {{ formatDateTime(attendance.updated_at) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Same-day timeline -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-green-600 flex items-center justify-center text-white">
                                    <i class="bi bi-list-check"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 dark:text-gray-100">Same-Day Activity</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        All events for {{ employeeName }} on {{ formatDate(attendance.timestamp) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <div v-if="sameDay && sameDay.length" class="relative">
                                <!-- Vertical line -->
                                <div class="absolute left-4 top-2 bottom-2 w-0.5 bg-gray-200 dark:bg-gray-700"></div>

                                <div class="space-y-5">
                                    <div
                                        v-for="record in sameDay"
                                        :key="record.id"
                                        class="relative flex items-start gap-4 pl-10"
                                    >
                                        <!-- Bullet -->
                                        <div
                                            class="absolute left-0 top-0 w-8 h-8 rounded-full flex items-center justify-center border-2"
                                            :class="record.id === attendance.id
                                                ? 'bg-indigo-600 border-indigo-600 text-white ring-4 ring-indigo-200 dark:ring-indigo-900/40'
                                                : statusBulletClass(record.status)"
                                        >
                                            <i :class="[statusIconFor(record.status), 'text-xs']"></i>
                                        </div>

                                        <div class="flex-1 min-w-0 pt-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <p class="font-semibold text-gray-800 dark:text-gray-100 text-sm">
                                                    {{ formatAttendanceStatus(record.status) }}
                                                </p>
                                                <span v-if="record.id === attendance.id"
                                                      class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">
                                                    VIEWING
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                {{ formatTime(record.timestamp) }}
                                                <span v-if="record.verified_by !== null">
                                                    • {{ verifyLabelFor(record.verified_by) }}
                                                </span>
                                            </p>
                                        </div>

                                        <Link
                                            v-if="record.id !== attendance.id"
                                            :href="route('admin.attendance.show', record.id)"
                                            class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 shrink-0"
                                        >
                                            View
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="text-center py-8">
                                <i class="bi bi-inbox text-4xl text-gray-300 dark:text-gray-600 mb-2"></i>
                                <p class="text-sm text-gray-500 dark:text-gray-400">No other activity that day</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Employee info -->
                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center text-white">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 dark:text-gray-100">Employee</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Linked profile</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="attendance.employee" class="p-6">
                            <div class="flex items-center gap-3 pb-4 border-b border-dashed border-gray-100 dark:border-gray-700">
                                <img
                                    :src="employeeAvatar"
                                    class="w-14 h-14 rounded-full ring-2 ring-indigo-100 dark:ring-indigo-900/40"
                                />
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-800 dark:text-gray-100 truncate">
                                        {{ employeeName }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">
                                        {{ attendance.employee.employee_id }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-3 pt-4">
                                <div class="flex items-center gap-2 text-sm">
                                    <i class="bi bi-envelope text-gray-400 w-4"></i>
                                    <a
                                        :href="`mailto:${attendance.employee.email}`"
                                        class="text-indigo-600 dark:text-indigo-400 hover:underline truncate"
                                    >
                                        {{ attendance.employee.email || '—' }}
                                    </a>
                                </div>
                                <div class="flex items-center gap-2 text-sm">
                                    <i class="bi bi-telephone text-gray-400 w-4"></i>
                                    <a
                                        :href="`tel:${attendance.employee.phone}`"
                                        class="text-indigo-600 dark:text-indigo-400 hover:underline"
                                    >
                                        {{ attendance.employee.phone || '—' }}
                                    </a>
                                </div>
                                <div class="flex items-center gap-2 text-sm">
                                    <i class="bi bi-building text-gray-400 w-4"></i>
                                    <span class="text-gray-700 dark:text-gray-300">
                                        {{ attendance.employee.department?.name || '—' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 text-sm">
                                    <i class="bi bi-award text-gray-400 w-4"></i>
                                    <span class="text-gray-700 dark:text-gray-300">
                                        {{ attendance.employee.designation?.name || '—' }}
                                    </span>
                                </div>
                            </div>

                            <Link
                                :href="route('admin.employees.show', attendance.employee.id)"
                                class="mt-5 inline-flex items-center gap-2 w-full px-4 py-2 text-sm font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition justify-center"
                            >
                                <i class="bi bi-person"></i>
                                View Full Profile
                            </Link>
                        </div>

                        <div v-else class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                            <i class="bi bi-person-x text-3xl text-gray-300 dark:text-gray-600 mb-2"></i>
                            <p>Employee record not found</p>
                        </div>
                    </div>

                    <!-- Quick actions -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 space-y-3">
                        <h4 class="font-semibold text-gray-800 dark:text-gray-100 text-sm">Quick Actions</h4>
                        <Link
                            :href="route('admin.attendance.index', { date: formatDateForQuery(attendance.timestamp) })"
                            class="flex items-center gap-3 w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg transition"
                        >
                            <i class="bi bi-calendar-day text-indigo-500"></i>
                            All attendance for this day
                        </Link>
                        <Link
                            :href="route('admin.attendance.index', { search: attendance.employee?.employee_id })"
                            class="flex items-center gap-3 w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg transition"
                        >
                            <i class="bi bi-person-lines-fill text-indigo-500"></i>
                            Employee's attendance
                        </Link>
                        <button
                            @click="confirmDelete"
                            class="flex items-center gap-3 w-full px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"
                        >
                            <i class="bi bi-trash"></i>
                            Delete this record
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete modal -->
        <div v-if="showDeleteModal"
             class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
             @click.self="showDeleteModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
                <div class="flex items-start gap-4 mb-5">
                    <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
                        <i class="bi bi-trash3-fill text-red-600 dark:text-red-400 text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg">Delete Attendance Record?</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            This will permanently delete the record for
                            <strong>{{ employeeName }}</strong> on
                            <strong>{{ formatDate(attendance.timestamp) }}</strong>.
                        </p>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button @click="showDeleteModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                    <button @click="deleteAttendance" :disabled="deleteForm.processing"
                            class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 flex items-center gap-2">
                        <i v-if="deleteForm.processing" class="bi bi-arrow-repeat animate-spin"></i>
                        <i v-else class="bi bi-trash"></i>
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    attendance: {
        type: Object,
        required: true,
    },
    sameDay: {
        type: Array,
        default: () => [],
    },
});

/* ========== Computed helpers ========== */
const employeeName = computed(() => {
    const e = props.attendance?.employee;
    if (!e) return 'Unknown Employee';
    return `${e.first_name} ${e.last_name}`.trim();
});

const employeeAvatar = computed(() => {
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(employeeName.value)}&background=6366f1&color=fff&bold=true&size=128`;
});

const statusIcon = computed(() => statusIconFor(props.attendance?.status));
const statusClass = computed(() => statusClassFor(props.attendance?.status));

const verifyIcon = computed(() => verifyIconFor(props.attendance?.verified_by));
const verifyLabel = computed(() => verifyLabelFor(props.attendance?.verified_by));

/* ========== Status mapping ========== */
function formatAttendanceStatus(status) {
    const map = {
        check_in:     'Check In',
        check_out:    'Check Out',
        break_in:     'Break In',
        break_out:    'Break Out',
        overtime_in:  'Overtime In',
        overtime_out: 'Overtime Out',
    };
    return map[status] || status || '—';
}

function statusIconFor(status) {
    const map = {
        check_in:     'bi bi-box-arrow-in-right',
        check_out:    'bi bi-box-arrow-right',
        break_in:     'bi bi-cup-hot',
        break_out:    'bi bi-cup-hot-fill',
        overtime_in:  'bi bi-clock-history',
        overtime_out: 'bi bi-clock-fill',
    };
    return map[status] || 'bi bi-circle';
}

function statusClassFor(status) {
    const map = {
        check_in:     'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 ring-1 ring-green-200 dark:ring-green-800',
        check_out:    'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 ring-1 ring-red-200 dark:ring-red-800',
        break_in:     'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-800',
        break_out:    'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-800',
        overtime_in:  'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 ring-1 ring-blue-200 dark:ring-blue-800',
        overtime_out: 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 ring-1 ring-blue-200 dark:ring-blue-800',
    };
    return map[status] || 'bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 ring-1 ring-gray-200 dark:ring-gray-600';
}

function statusBulletClass(status) {
    const map = {
        check_in:     'bg-green-100 dark:bg-green-900/40 border-green-500 text-green-700 dark:text-green-300',
        check_out:    'bg-red-100 dark:bg-red-900/40 border-red-500 text-red-700 dark:text-red-300',
        break_in:     'bg-amber-100 dark:bg-amber-900/40 border-amber-500 text-amber-700 dark:text-amber-300',
        break_out:    'bg-amber-100 dark:bg-amber-900/40 border-amber-500 text-amber-700 dark:text-amber-300',
        overtime_in:  'bg-blue-100 dark:bg-blue-900/40 border-blue-500 text-blue-700 dark:text-blue-300',
        overtime_out: 'bg-blue-100 dark:bg-blue-900/40 border-blue-500 text-blue-700 dark:text-blue-300',
    };
    return map[status] || 'bg-gray-100 dark:bg-gray-700 border-gray-400 text-gray-600 dark:text-gray-300';
}

/* ========== Verification mapping ========== */
function verifyIconFor(type) {
    const map = {
        0: 'bi bi-person-badge text-gray-500',
        1: 'bi bi-fingerprint text-indigo-600 dark:text-indigo-400',
        2: 'bi bi-person-bounding-box text-purple-600 dark:text-purple-400',
        3: 'bi bi-credit-card-2-front text-blue-600 dark:text-blue-400',
    };
    return map[type] || 'bi bi-question-circle text-gray-400';
}

function verifyLabelFor(type) {
    const map = {
        0: 'Manual / Password',
        1: 'Fingerprint',
        2: 'Face Recognition',
        3: 'Card',
    };
    return map[type] || 'Not specified';
}

/* ========== Date/time formatting ========== */
function formatDate(value) {
    if (!value) return '—';
    try {
        return new Date(value).toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        });
    } catch { return '—'; }
}

function formatTime(value) {
    if (!value) return '—';
    try {
        return new Date(value).toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
        });
    } catch { return '—'; }
}

function formatDateTime(value) {
    if (!value) return '—';
    try {
        return new Date(value).toLocaleString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch { return '—'; }
}

function formatDateForQuery(value) {
    if (!value) return '';
    try {
        return new Date(value).toISOString().split('T')[0];
    } catch { return ''; }
}

/* ========== Delete ========== */
const showDeleteModal = ref(false);
const deleteForm = useForm({});

const confirmDelete = () => {
    showDeleteModal.value = true;
};

const deleteAttendance = () => {
    deleteForm.post(route('admin.attendance.destroy', props.attendance.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};
</script>
