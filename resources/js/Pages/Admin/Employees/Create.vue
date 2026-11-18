<template>
    <AdminLayout page-title="Add Employee">
        <Head title="Add Employee" />

        <div class="max-w-5xl mx-auto">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.employees.index')"
                        class="w-10 h-10 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                    >
                        <i class="bi bi-arrow-left text-gray-600 dark:text-gray-300"></i>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Add New Employee</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Fill in the information below to create a new employee</p>
                    </div>
                </div>
            </div>

            <!-- Error summary -->
            <div v-if="form.errors.error"
                 class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 flex items-start gap-3">
                <i class="bi bi-exclamation-triangle-fill text-red-500 text-xl"></i>
                <div>
                    <p class="text-sm text-red-700 dark:text-red-300 font-medium">{{ form.errors.error }}</p>
                    <p v-if="form.errors.file" class="text-xs text-red-600 dark:text-red-400 mt-1 font-mono">
                        {{ form.errors.file }}
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Personal Information -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-indigo-600 flex items-center justify-center text-white">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 dark:text-gray-100">Personal Information</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Basic personal details</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- First Name -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.first_name"
                                type="text"
                                placeholder="Enter first name"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none transition
                                       bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100"
                                :class="form.errors.first_name ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600'"
                            />
                            <p v-if="form.errors.first_name" class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle"></i> {{ form.errors.first_name }}
                            </p>
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.last_name"
                                type="text"
                                placeholder="Enter last name"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none transition
                                       bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100"
                                :class="form.errors.last_name ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600'"
                            />
                            <p v-if="form.errors.last_name" class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle"></i> {{ form.errors.last_name }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="bi bi-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="employee@company.com"
                                    class="w-full pl-10 pr-4 py-2.5 border rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none transition
                                           bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100"
                                    :class="form.errors.email ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600'"
                                />
                            </div>
                            <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle"></i> {{ form.errors.email }}
                            </p>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Phone Number <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="bi bi-telephone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    placeholder="+880 1XXX-XXXXXX"
                                    class="w-full pl-10 pr-4 py-2.5 border rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none transition
                                           bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100"
                                    :class="form.errors.phone ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600'"
                                />
                            </div>
                            <p v-if="form.errors.phone" class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle"></i> {{ form.errors.phone }}
                            </p>
                        </div>

                        <!-- Date of Birth -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Date of Birth
                            </label>
                            <input
                                v-model="form.date_of_birth"
                                type="date"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none transition"
                            />
                            <p v-if="form.errors.date_of_birth" class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                                {{ form.errors.date_of_birth }}
                            </p>
                        </div>

                        <!-- Address -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Address
                            </label>
                            <input
                                v-model="form.address"
                                type="text"
                                placeholder="Enter address"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none transition"
                            />
                        </div>
                    </div>
                </div>

                <!-- Employment Details -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-green-600 flex items-center justify-center text-white">
                                <i class="bi bi-briefcase-fill"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 dark:text-gray-100">Employment Details</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Job information and salary</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Employee ID -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Employee ID <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="bi bi-hash absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input
                                    v-model="form.employee_id"
                                    type="text"
                                    placeholder="EMP-001"
                                    class="w-full pl-10 pr-4 py-2.5 border rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none transition
                                           bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100"
                                    :class="form.errors.employee_id ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600'"
                                />
                            </div>
                            <p v-if="form.errors.employee_id" class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle"></i> {{ form.errors.employee_id }}
                            </p>
                        </div>

                        <!-- Department -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Department <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.department_id"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none transition
                                       bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100"
                                :class="form.errors.department_id ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600'"
                            >
                                <option value="">Select Department</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                    {{ dept.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.department_id" class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                                {{ form.errors.department_id }}
                            </p>
                        </div>

                        <!-- Designation -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Designation <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.designation_id"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none transition
                                       bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100"
                                :class="form.errors.designation_id ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600'"
                            >
                                <option value="">Select Designation</option>
                                <option v-for="desig in designations" :key="desig.id" :value="desig.id">
                                    {{ desig.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.designation_id" class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                                {{ form.errors.designation_id }}
                            </p>
                        </div>

                        <!-- Salary -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Monthly Salary <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 font-semibold">৳</span>
                                <input
                                    v-model="form.salary"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                    class="w-full pl-8 pr-4 py-2.5 border rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none transition
                                           bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100"
                                    :class="form.errors.salary ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600'"
                                />
                            </div>
                            <p v-if="form.errors.salary" class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                                {{ form.errors.salary }}
                            </p>
                        </div>

                        <!-- Joining Date -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Joining Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.joining_date"
                                type="date"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none transition
                                       bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100"
                                :class="form.errors.joining_date ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600'"
                            />
                            <p v-if="form.errors.joining_date" class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                                {{ form.errors.joining_date }}
                            </p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.status"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none transition"
                            >
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="terminated">Terminated</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Device & Account Settings -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-600 flex items-center justify-center text-white">
                                <i class="bi bi-fingerprint"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 dark:text-gray-100">Device & Account Settings</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">ZKTeco device sync and login credentials</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-5">
                        <!-- Device User ID -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                ZKTeco Device User ID
                            </label>
                            <div class="relative">
                                <i class="bi bi-fingerprint absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input
                                    v-model="form.device_user_id"
                                    type="text"
                                    placeholder="e.g., 1001 (Leave empty if not enrolled)"
                                    class="w-full pl-10 pr-4 py-2.5 border rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none transition
                                           bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100"
                                    :class="form.errors.device_user_id ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600'"
                                />
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                <i class="bi bi-info-circle"></i> The user ID enrolled in your ZKTeco biometric device
                            </p>
                            <p v-if="form.errors.device_user_id" class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                                {{ form.errors.device_user_id }}
                            </p>
                        </div>

                        <!-- Create User Account Toggle -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                                    <i class="bi bi-person-badge text-indigo-600 dark:text-indigo-400"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800 dark:text-gray-100 text-sm">Create Login Account</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Employee can login to view attendance & payslips</p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    v-model="form.create_user_account"
                                    type="checkbox"
                                    class="sr-only peer"
                                />
                                <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-indigo-600 transition-colors relative">
                                    <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full transition-transform peer-checked:translate-x-5"></div>
                                </div>
                            </label>
                        </div>

                        <!-- Password (conditional) -->
                        <transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 -translate-y-2"
                            enter-to-class="opacity-100 translate-y-0"
                        >
                            <div v-if="form.create_user_account">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Login Password <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <i class="bi bi-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                    <input
                                        v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        placeholder="Minimum 8 characters"
                                        class="w-full pl-10 pr-12 py-2.5 border rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none transition
                                               bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100"
                                        :class="form.errors.password ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600'"
                                    />
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                    >
                                        <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                                    </button>
                                </div>
                                <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                                    {{ form.errors.password }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    <i class="bi bi-shield-check"></i> Password must be at least 8 characters
                                </p>
                            </div>
                        </transition>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <Link
                        :href="route('admin.employees.index')"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition flex items-center gap-2"
                    >
                        <i class="bi bi-x-lg"></i> Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-indigo-500/30"
                    >
                        <i v-if="form.processing" class="bi bi-arrow-repeat animate-spin"></i>
                        <i v-else class="bi bi-check-lg"></i>
                        {{ form.processing ? 'Saving...' : 'Save Employee' }}
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

const props = defineProps({
    departments: {
        type: Array,
        default: () => [],
    },
    designations: {
        type: Array,
        default: () => [],
    },
    availableUsers: {
        type: Array,
        default: () => [],
    },
});

const showPassword = ref(false);

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    employee_id: '',
    department_id: '',      // ← FK (was: department)
    designation_id: '',     // ← FK (was: designation)
    salary: '',
    joining_date: new Date().toISOString().split('T')[0],
    date_of_birth: '',
    address: '',
    device_user_id: '',
    status: 'active',
    create_user_account: false,
    password: '',
});

const submit = () => {
    form.post(route('admin.employees.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>
