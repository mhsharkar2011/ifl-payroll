<template>
    <AdminLayout page-title="Employee Details">
        <Head :title="`${employee.first_name} ${employee.last_name}`" />

        <div class="max-w-7xl mx-auto space-y-6">
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
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Employee Details</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">View complete employee information</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('admin.employees.edit', employee.id)"
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

            <!-- ==================== Profile Card with Avatar ==================== -->
            <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 rounded-2xl shadow-lg overflow-hidden">
                <div class="p-8 text-white relative">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32"></div>
                    <div class="absolute bottom-0 right-20 w-40 h-40 bg-white/10 rounded-full -mb-20"></div>

                    <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center gap-6">
                        <!-- ✅ Avatar with uploaded photo + fallback -->
                        <div class="relative shrink-0">
                            <div class="w-24 h-24 rounded-2xl ring-4 ring-white/30 shadow-xl overflow-hidden bg-white/20 flex items-center justify-center">
                                <!-- Fallback Icon -->
                                <i
                                    v-if="imageFailed"
                                    class="bi bi-person-circle text-6xl text-white/60"
                                ></i>

                                <!-- Image (uploaded or generated) -->
                                <img
                                    v-else
                                    :src="avatarUrl"
                                    :alt="employee.first_name + ' ' + employee.last_name"
                                    class="w-full h-full object-cover"
                                    @error="onImageError"
                                />
                            </div>
                            <!-- Status Ring -->
                            <span
                                class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full ring-4 ring-white flex items-center justify-center"
                                :class="statusRing"
                            >
                                <i class="bi bi-check text-white text-xs"></i>
                            </span>
                        </div>

                        <div class="flex-1">
                            <h2 class="text-3xl font-bold">
                                {{ employee.first_name }} {{ employee.last_name }}
                            </h2>
                            <p class="text-white/80 mt-1 flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center gap-1">
                                    <i class="bi bi-briefcase"></i>
                                    <span v-if="employee.designation">{{ employee.designation.name }}</span>
                                    <span v-else class="italic opacity-70">No designation</span>
                                </span>
                                <span class="opacity-50">•</span>
                                <span class="inline-flex items-center gap-1">
                                    <i class="bi bi-building"></i>
                                    <span v-if="employee.department">{{ employee.department.name }}</span>
                                    <span v-else class="italic opacity-70">No department</span>
                                </span>
                            </p>
                            <div class="flex flex-wrap items-center gap-4 mt-4">
                                <span class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-sm">
                                    <i class="bi bi-hash"></i>
                                    {{ employee.employee_id }}
                                </span>
                                <span class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-sm">
                                    <i class="bi bi-envelope"></i>
                                    {{ employee.email }}
                                </span>
                                <span
                                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-semibold"
                                    :class="statusBadgeClass"
                                >
                                    <span class="w-2 h-2 rounded-full bg-white"></span>
                                    {{ capitalize(employee.status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <i class="bi bi-cash-coin text-green-600 dark:text-green-400 text-lg"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-medium">Salary</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-gray-100 truncate">
                                ৳{{ formatCurrency(employee.salary) }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                            <i class="bi bi-calendar-check text-indigo-600 dark:text-indigo-400 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-medium">Attendance</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ attendanceCount }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <i class="bi bi-clock-history text-amber-600 dark:text-amber-400 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-medium">Overtime</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ overtimeHours }}h</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                            <i class="bi bi-calendar-event text-purple-600 dark:text-purple-400 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-medium">Joined</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ formatDate(employee.joining_date) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Personal Information -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-indigo-600 flex items-center justify-center text-white">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 dark:text-gray-100">Personal Information</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Basic employee details</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold mb-1">First Name</p>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ employee.first_name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold mb-1">Last Name</p>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ employee.last_name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold mb-1">Email</p>
                                <a
                                    :href="`mailto:${employee.email}`"
                                    class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1"
                                >
                                    <i class="bi bi-envelope"></i>
                                    {{ employee.email }}
                                </a>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold mb-1">Phone</p>
                                <a
                                    :href="`tel:${employee.phone}`"
                                    class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1"
                                >
                                    <i class="bi bi-telephone"></i>
                                    {{ employee.phone }}
                                </a>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold mb-1">Date of Birth</p>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                    {{ employee.date_of_birth ? formatDate(employee.date_of_birth) : 'Not provided' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold mb-1">Address</p>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                    {{ employee.address || 'Not provided' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Attendance -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20">
                            <div class="flex items-center justify-between flex-wrap gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-green-600 flex items-center justify-center text-white">
                                        <i class="bi bi-calendar-check-fill"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-800 dark:text-gray-100">Recent Attendance</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Last 10 records</p>
                                    </div>
                                </div>
                                <Link
                                    :href="route('admin.attendance.index', { employee_id: employee.id })"
                                    class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium flex items-center gap-1"
                                >
                                    View All <i class="bi bi-arrow-right"></i>
                                </Link>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 uppercase text-xs">
                                    <tr>
                                        <th class="px-6 py-3 text-left font-semibold">Date</th>
                                        <th class="px-6 py-3 text-left font-semibold">Time</th>
                                        <th class="px-6 py-3 text-left font-semibold">Status</th>
                                        <th class="px-6 py-3 text-left font-semibold">Verified</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <tr
                                        v-for="attendance in employee.attendances"
                                        :key="attendance.id"
                                        class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition"
                                    >
                                        <td class="px-6 py-3 text-gray-700 dark:text-gray-300">
                                            {{ formatDate(attendance.timestamp) }}
                                        </td>
                                        <td class="px-6 py-3 text-gray-700 dark:text-gray-300">
                                            {{ formatTime(attendance.timestamp) }}
                                        </td>
                                        <td class="px-6 py-3">
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold"
                                                :class="getAttendanceStatusClass(attendance.status)"
                                            >
                                                <i :class="getAttendanceStatusIcon(attendance.status)" class="text-[10px]"></i>
                                                {{ formatAttendanceStatus(attendance.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3">
                                            <i
                                                :class="getVerifyIcon(attendance.verified_by)"
                                                :title="getVerifyLabel(attendance.verified_by)"
                                                class="text-lg"
                                            ></i>
                                        </td>
                                    </tr>
                                    <tr v-if="!employee.attendances || employee.attendances.length === 0">
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            <i class="bi bi-inbox text-3xl block mb-2 text-gray-300 dark:text-gray-600"></i>
                                            No attendance records
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Recent Payrolls -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20">
                            <div class="flex items-center justify-between flex-wrap gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-amber-600 flex items-center justify-center text-white">
                                        <i class="bi bi-cash-stack"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-800 dark:text-gray-100">Recent Payrolls</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Last 6 months</p>
                                    </div>
                                </div>
                                <Link
                                    :href="route('admin.payroll.index', { employee_id: employee.id })"
                                    class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium flex items-center gap-1"
                                >
                                    View All <i class="bi bi-arrow-right"></i>
                                </Link>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 uppercase text-xs">
                                    <tr>
                                        <th class="px-6 py-3 text-left font-semibold">Period</th>
                                        <th class="px-6 py-3 text-left font-semibold">Net Salary</th>
                                        <th class="px-6 py-3 text-left font-semibold">Status</th>
                                        <th class="px-6 py-3 text-right font-semibold">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <tr
                                        v-for="payroll in employee.payrolls"
                                        :key="payroll.id"
                                        class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition"
                                    >
                                        <td class="px-6 py-3">
                                            <p class="font-medium text-gray-800 dark:text-gray-100">
                                                {{ getMonthName(payroll.month) }} {{ payroll.year }}
                                            </p>
                                        </td>
                                        <td class="px-6 py-3 font-semibold text-gray-800 dark:text-gray-100">
                                            ৳ {{ formatCurrency(payroll.net_salary) }}
                                        </td>
                                        <td class="px-6 py-3">
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold"
                                                :class="getPayrollStatusClass(payroll.status)"
                                            >
                                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                                {{ capitalize(payroll.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-right">
                                            <Link
                                                :href="route('admin.payroll.show', payroll.id)"
                                                class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium"
                                            >
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="!employee.payrolls || employee.payrolls.length === 0">
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            <i class="bi bi-inbox text-3xl block mb-2 text-gray-300 dark:text-gray-600"></i>
                                            No payroll records
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Employment Info -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center text-white">
                                    <i class="bi bi-briefcase-fill"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 dark:text-gray-100">Employment</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Job details</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="flex items-start justify-between gap-3 pb-4 border-b border-dashed border-gray-100 dark:border-gray-700">
                                <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-xs">
                                    <i class="bi bi-hash"></i>
                                    <span>Employee ID</span>
                                </div>
                                <span class="font-mono text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-gray-700 dark:text-gray-200 font-semibold">
                                    {{ employee.employee_id }}
                                </span>
                            </div>
                            <div class="flex items-start justify-between gap-3 pb-4 border-b border-dashed border-gray-100 dark:border-gray-700">
                                <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-xs">
                                    <i class="bi bi-building"></i>
                                    <span>Department</span>
                                </div>
                                <span class="text-sm font-medium text-gray-800 dark:text-gray-100 text-right">
                                    {{ employee.department?.name || '—' }}
                                </span>
                            </div>
                            <div class="flex items-start justify-between gap-3 pb-4 border-b border-dashed border-gray-100 dark:border-gray-700">
                                <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-xs">
                                    <i class="bi bi-person-badge"></i>
                                    <span>Designation</span>
                                </div>
                                <span class="text-sm font-medium text-gray-800 dark:text-gray-100 text-right">
                                    {{ employee.designation?.name || '—' }}
                                </span>
                            </div>
                            <div class="flex items-start justify-between gap-3 pb-4 border-b border-dashed border-gray-100 dark:border-gray-700">
                                <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-xs">
                                    <i class="bi bi-calendar-event"></i>
                                    <span>Joining Date</span>
                                </div>
                                <span class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                    {{ formatDate(employee.joining_date) }}
                                </span>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-xs">
                                    <i class="bi bi-cash-coin"></i>
                                    <span>Salary</span>
                                </div>
                                <span class="text-sm font-bold text-green-600 dark:text-green-400">
                                    ৳ {{ formatCurrency(employee.salary) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Device Info -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-purple-600 flex items-center justify-center text-white">
                                    <i class="bi bi-fingerprint"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 dark:text-gray-100">Device Info</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">ZKTeco integration</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <div
                                class="flex items-center justify-between p-4 rounded-xl"
                                :class="employee.device_user_id
                                    ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800'
                                    : 'bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800'"
                            >
                                <div class="flex items-center gap-3">
                                    <i
                                        class="bi text-2xl"
                                        :class="employee.device_user_id
                                            ? 'bi-check-circle-fill text-green-600 dark:text-green-400'
                                            : 'bi-exclamation-circle-fill text-amber-600 dark:text-amber-400'"
                                    ></i>
                                    <div>
                                        <p class="text-xs font-semibold"
                                           :class="employee.device_user_id
                                               ? 'text-green-800 dark:text-green-300'
                                               : 'text-amber-800 dark:text-amber-300'">
                                            {{ employee.device_user_id ? 'Enrolled' : 'Not Enrolled' }}
                                        </p>
                                        <p class="text-sm font-bold"
                                           :class="employee.device_user_id
                                               ? 'text-green-900 dark:text-green-200'
                                               : 'text-amber-900 dark:text-amber-200'">
                                            {{ employee.device_user_id || 'No Device ID' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <button
                                v-if="employee.device_user_id"
                                @click="resetDeviceId"
                                class="w-full mt-4 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition flex items-center justify-center gap-2"
                            >
                                <i class="bi bi-arrow-counterclockwise"></i>
                                Reset Device ID
                            </button>
                        </div>
                    </div>

                    <!-- Account Info -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-emerald-600 flex items-center justify-center text-white">
                                    <i class="bi bi-person-badge-fill"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 dark:text-gray-100">Account</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Login information</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <div
                                class="flex items-center justify-between p-4 rounded-xl"
                                :class="employee.user
                                    ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800'
                                    : 'bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600'"
                            >
                                <div class="flex items-center gap-3">
                                    <i
                                        class="bi text-2xl"
                                        :class="employee.user
                                            ? 'bi-check-circle-fill text-green-600 dark:text-green-400'
                                            : 'bi-dash-circle-fill text-gray-400 dark:text-gray-500'"
                                    ></i>
                                    <div>
                                        <p class="text-xs font-semibold"
                                           :class="employee.user
                                               ? 'text-green-800 dark:text-green-300'
                                               : 'text-gray-600 dark:text-gray-400'">
                                            {{ employee.user ? 'Active Account' : 'No Login Account' }}
                                        </p>
                                        <p class="text-sm font-bold"
                                           :class="employee.user
                                               ? 'text-green-900 dark:text-green-200'
                                               : 'text-gray-700 dark:text-gray-300'">
                                            {{ employee.user ? 'Can Login' : 'Cannot Login' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
            @click.self="showDeleteModal = false"
        >
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
                        <i class="bi bi-exclamation-triangle-fill text-red-600 dark:text-red-400 text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg">Delete Employee?</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Are you sure you want to delete
                            <strong class="text-gray-800 dark:text-gray-100">
                                {{ employee.first_name }} {{ employee.last_name }}
                            </strong>?
                            This action cannot be undone.
                        </p>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button
                        @click="showDeleteModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deleteEmployee"
                        :disabled="deleteForm.processing"
                        class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition disabled:opacity-50 flex items-center gap-2"
                    >
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
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    employee: {
        type: Object,
        required: true,
    },
});

/* ============================================================
 |  Avatar Logic
 * ============================================================ */
const imageFailed = ref(false);

/**
 * ✅ Compute avatar URL with fallback chain:
 *   1. Uploaded avatar (employee.avatar_url)
 *   2. UI Avatar generated from name (with white bg for gradient card)
 */
const avatarUrl = computed(() => {
    // Priority 1: Uploaded avatar
    if (props.employee?.avatar_url) {
        return props.employee.avatar_url;
    }

    // Priority 2: Generated from name
    const name = `${props.employee?.first_name || ''} ${props.employee?.last_name || ''}`.trim() || 'Employee';
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=ffffff&color=6366f1&bold=true&size=128`;
});

/**
 * Handle image load error - fallback to generated avatar
 */
const onImageError = (event) => {
    // Prevent infinite loop
    if (event.target.src.includes('ui-avatars.com')) {
        imageFailed.value = true;
        return;
    }
    const name = `${props.employee?.first_name || ''} ${props.employee?.last_name || ''}`.trim() || 'Employee';
    event.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=ffffff&color=6366f1&bold=true&size=128`;
};

/* ========== Stats ========== */
const attendanceCount = computed(() => {
    return props.employee.attendances?.length || 0;
});

const overtimeHours = computed(() => {
    if (!props.employee.attendances) return 0;
    const otRecords = props.employee.attendances.filter(
        (a) => a.status === 'overtime_in' || a.status === 'overtime_out'
    );
    return Math.round(otRecords.length * 0.5 * 10) / 10;
});

/* ========== Status Helpers ========== */
const statusRing = computed(() => {
    const map = {
        active: 'bg-green-500',
        inactive: 'bg-gray-500',
        terminated: 'bg-red-500',
    };
    return map[props.employee.status] || 'bg-gray-500';
});

const statusBadgeClass = computed(() => {
    const map = {
        active: 'bg-green-500/90 backdrop-blur-sm text-white',
        inactive: 'bg-gray-500/90 backdrop-blur-sm text-white',
        terminated: 'bg-red-500/90 backdrop-blur-sm text-white',
    };
    return map[props.employee.status] || 'bg-gray-500/90 text-white';
});

/* ========== Delete Modal ========== */
const showDeleteModal = ref(false);
const deleteForm = useForm({});

const confirmDelete = () => {
    showDeleteModal.value = true;
};

const deleteEmployee = () => {
    deleteForm.post(route('admin.employees.destroy', props.employee.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};

/* ========== Reset Device ID ========== */
const resetDeviceId = () => {
    if (!confirm('Reset device user ID?')) return;

    router.post(route('admin.employees.reset-device-id', props.employee.id), {}, {
        preserveScroll: true,
    });
};

/* ========== Helpers ========== */
const formatCurrency = (value) => {
    if (!value) return '0';
    return Number(value).toLocaleString('en-BD');
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const formatTime = (timestamp) => {
    if (!timestamp) return '-';
    return new Date(timestamp).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
    });
};

const getMonthName = (month) => {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return months[month - 1] || '-';
};

const capitalize = (str) => {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
};

const formatAttendanceStatus = (status) => {
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

const getAttendanceStatusClass = (status) => {
    const map = {
        check_in:     'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 ring-1 ring-green-200 dark:ring-green-800',
        check_out:    'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 ring-1 ring-red-200 dark:ring-red-800',
        break_in:     'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-800',
        break_out:    'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-800',
        overtime_in:  'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 ring-1 ring-blue-200 dark:ring-blue-800',
        overtime_out: 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 ring-1 ring-blue-200 dark:ring-blue-800',
    };
    return map[status] || 'bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 ring-1 ring-gray-200 dark:ring-gray-600';
};

const getAttendanceStatusIcon = (status) => {
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

const getVerifyIcon = (type) => {
    const map = {
        0: 'bi bi-person-badge text-gray-500',
        1: 'bi bi-fingerprint text-indigo-600 dark:text-indigo-400',
        2: 'bi bi-person-bounding-box text-purple-600 dark:text-purple-400',
        3: 'bi bi-credit-card-2-front text-blue-600 dark:text-blue-400',
    };
    return map[type] || 'bi bi-question-circle text-gray-400';
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

const getPayrollStatusClass = (status) => {
    const map = {
        draft:    'bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 ring-1 ring-gray-200 dark:ring-gray-600',
        approved: 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 ring-1 ring-blue-200 dark:ring-blue-800',
        paid:     'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 ring-1 ring-green-200 dark:ring-green-800',
    };
    return map[status] || 'bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 ring-1 ring-gray-200 dark:ring-gray-600';
};
</script>
