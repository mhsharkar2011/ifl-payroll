<template>
    <AdminLayout page-title="Employees">
        <Head title="Employees" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Employees</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Manage all your employees
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <!-- View Toggle -->
                    <div class="inline-flex items-center bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-1">
                        <button
                            @click="viewMode = 'grid'"
                            class="px-3 py-1.5 text-xs font-semibold rounded-md transition"
                            :class="viewMode === 'grid'
                                ? 'bg-indigo-600 text-white'
                                : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                        >
                            <i class="bi bi-grid-3x3-gap-fill mr-1"></i> Grid
                        </button>
                        <button
                            @click="viewMode = 'list'"
                            class="px-3 py-1.5 text-xs font-semibold rounded-md transition"
                            :class="viewMode === 'list'
                                ? 'bg-indigo-600 text-white'
                                : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                        >
                            <i class="bi bi-list-ul mr-1"></i> List
                        </button>
                    </div>

                    <Link
                        :href="route('admin.employees.bulk-upload')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-green-700 dark:text-green-300 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition"
                    >
                        <i class="bi bi-cloud-upload"></i>
                        <span class="hidden sm:inline">Bulk Upload</span>
                    </Link>
                    <Link
                        :href="route('admin.employees.create')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition shadow-lg"
                    >
                        <i class="bi bi-plus-lg"></i>
                        <span class="hidden sm:inline">Add Employee</span>
                    </Link>
                </div>
            </div>

            <!-- Success Message -->
            <div
                v-if="$page.props.flash?.success"
                class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 flex items-center gap-3"
            >
                <i class="bi bi-check-circle-fill text-green-500 text-xl"></i>
                <p class="text-sm text-green-700 dark:text-green-300 font-medium">
                    {{ $page.props.flash.success }}
                </p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                            <i class="bi bi-people-fill text-indigo-600 dark:text-indigo-400 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-medium">Total</p>
                            <p class="text-xl font-bold text-gray-800 dark:text-gray-100">
                                {{ employees?.total || 0 }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <i class="bi bi-check-circle-fill text-green-600 dark:text-green-400 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-medium">Active</p>
                            <p class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ stats?.active || 0 }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                            <i class="bi bi-person-x-fill text-gray-600 dark:text-gray-400 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-medium">Inactive</p>
                            <p class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ stats?.inactive || 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div class="relative md:col-span-2">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input
                            v-model="filters.search"
                            @input="debouncedSearch"
                            type="text"
                            placeholder="Search by name, email, ID..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none"
                        />
                    </div>
                    <select
                        v-model="filters.department_id"
                        @change="applyFilters"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none"
                    >
                        <option value="">All Departments</option>
                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                    </select>
                    <select
                        v-model="filters.status"
                        @change="applyFilters"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="terminated">Terminated</option>
                    </select>
                </div>
                <div v-if="hasActiveFilters" class="mt-3 flex items-center justify-end">
                    <button
                        @click="clearFilters"
                        class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 inline-flex items-center gap-1"
                    >
                        <i class="bi bi-x-circle"></i> Clear filters
                    </button>
                </div>
            </div>

            <!-- ==================== GRID VIEW ==================== -->
            <div v-if="viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3">
                <div
                    v-for="employee in employees?.data || []"
                    :key="employee.id"
                    class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-300"
                >
                    <!-- Card Header with gradient + Status Badge -->
                    <div class="h-16 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 relative">
                        <span
                            class="absolute top-2 right-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold backdrop-blur-sm"
                            :class="{
                                'bg-green-500/90 text-white': employee.status === 'active',
                                'bg-gray-500/90 text-white': employee.status === 'inactive',
                                'bg-red-500/90 text-white': employee.status === 'terminated',
                            }"
                        >
                            <span class="w-1 h-1 rounded-full bg-white"></span>
                            {{ employee.status }}
                        </span>
                    </div>

                    <!-- Compact Avatar -->
                    <div class="flex justify-center -mt-8 relative z-10">
                        <div class="relative">
                            <EmployeeAvatar :employee="employee" size="lg" />

                            <!-- Login Badge -->
                            <span
                                v-if="employee.user"
                                class="absolute bottom-0 right-0 w-5 h-5 rounded-full bg-blue-500 ring-2 ring-white dark:ring-gray-800 flex items-center justify-center"
                                title="Has login account"
                            >
                                <i class="bi bi-check text-white text-[10px]"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Compact Card Body -->
                    <div class="px-3 pt-2 pb-3 text-center">
                        <h3 class="font-bold text-gray-800 dark:text-gray-100 text-xs truncate" :title="employee.first_name + ' ' + employee.last_name">
                            {{ employee.first_name }} {{ employee.last_name }}
                        </h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5 truncate" :title="employee.email">
                            {{ employee.email }}
                        </p>

                        <!-- Compact Info Badges -->
                        <div class="flex flex-wrap justify-center gap-1 mt-2 min-h-[20px]">
                            <span
                                v-if="employee.department"
                                class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[9px] font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 truncate max-w-full"
                                :title="employee.department.name"
                            >
                                <i class="bi bi-building text-[8px]"></i>
                                {{ employee.department.name }}
                            </span>
                            <span
                                v-if="employee.designation"
                                class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[9px] font-medium bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 truncate max-w-full"
                                :title="employee.designation.name"
                            >
                                <i class="bi bi-award text-[8px]"></i>
                                {{ employee.designation.name }}
                            </span>
                        </div>

                        <!-- Compact Stats -->
                        <div class="grid grid-cols-2 gap-1 mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                            <div class="min-w-0">
                                <p class="text-[9px] text-gray-500 dark:text-gray-400 uppercase font-medium">ID</p>
                                <p class="text-[10px] font-mono font-semibold text-gray-800 dark:text-gray-100 truncate">
                                    {{ employee.employee_id }}
                                </p>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[9px] text-gray-500 dark:text-gray-400 uppercase font-medium">Salary</p>
                                <p class="text-[10px] font-semibold text-green-600 dark:text-green-400 truncate">
                                    ৳{{ Number(employee.salary || 0).toLocaleString('en-BD') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Compact Footer Actions -->
                    <div class="px-2 py-1.5 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex items-center justify-center gap-0.5">
                        <Link
                            :href="route('admin.employees.show', employee.id)"
                            class="flex-1 inline-flex items-center justify-center gap-1 px-1.5 py-1.5 text-[10px] font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 rounded hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 transition"
                            title="View"
                        >
                            <i class="bi bi-eye text-[10px]"></i>
                            <span class="hidden xl:inline">View</span>
                        </Link>
                        <Link
                            :href="route('admin.employees.edit', employee.id)"
                            class="flex-1 inline-flex items-center justify-center gap-1 px-1.5 py-1.5 text-[10px] font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 rounded hover:bg-amber-50 dark:hover:bg-amber-900/30 hover:text-amber-600 transition"
                            title="Edit"
                        >
                            <i class="bi bi-pencil text-[10px]"></i>
                            <span class="hidden xl:inline">Edit</span>
                        </Link>
                        <button
                            @click="confirmDelete(employee)"
                            class="inline-flex items-center justify-center w-7 h-7 text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-700 rounded hover:bg-red-50 dark:hover:bg-red-900/30 hover:text-red-600 transition"
                            title="Delete"
                        >
                            <i class="bi bi-trash text-[10px]"></i>
                        </button>
                    </div>
                </div>

                <!-- Empty State (Grid) -->
                <div v-if="!employees?.data?.length" class="col-span-full bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-16 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                            <i class="bi bi-people text-3xl text-gray-400"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-700 dark:text-gray-200">No employees found</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ hasActiveFilters ? 'Try changing your filters' : 'Create your first employee to get started' }}
                            </p>
                        </div>
                        <Link
                            v-if="!hasActiveFilters"
                            :href="route('admin.employees.create')"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition"
                        >
                            <i class="bi bi-plus-lg"></i> Add Employee
                        </Link>
                    </div>
                </div>
            </div>

            <!-- ==================== LIST VIEW ==================== -->
            <div v-else class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 uppercase text-xs">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold">Employee</th>
                                <th class="px-6 py-3 text-left font-semibold">ID</th>
                                <th class="px-6 py-3 text-left font-semibold">Department</th>
                                <th class="px-6 py-3 text-left font-semibold">Designation</th>
                                <th class="px-6 py-3 text-left font-semibold">Salary</th>
                                <th class="px-6 py-3 text-left font-semibold">Status</th>
                                <th class="px-6 py-3 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr
                                v-for="employee in employees?.data || []"
                                :key="employee.id"
                                class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="relative shrink-0">
                                            <EmployeeAvatar :employee="employee" size="md" />

                                            <!-- Status dot -->
                                            <span
                                                class="absolute bottom-0 right-0 w-3 h-3 border-2 border-white dark:border-gray-800 rounded-full"
                                                :class="{
                                                    'bg-green-500': employee.status === 'active',
                                                    'bg-gray-400': employee.status === 'inactive',
                                                    'bg-red-500': employee.status === 'terminated',
                                                }"
                                            ></span>

                                            <!-- Login Badge -->
                                            <span
                                                v-if="employee.user"
                                                class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-blue-500 ring-2 ring-white dark:ring-gray-800 flex items-center justify-center"
                                                title="Has login account"
                                            >
                                                <i class="bi bi-check text-white text-[8px]"></i>
                                            </span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-gray-800 dark:text-gray-100 truncate">
                                                {{ employee.first_name }} {{ employee.last_name }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                {{ employee.email }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-mono text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded">
                                        {{ employee.employee_id }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    <span v-if="employee.department" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                        <i class="bi bi-building"></i> {{ employee.department.name }}
                                    </span>
                                    <span v-else class="text-gray-400 text-xs">—</span>
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    <span v-if="employee.designation" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">
                                        <i class="bi bi-award"></i> {{ employee.designation.name }}
                                    </span>
                                    <span v-else class="text-gray-400 text-xs">—</span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-800 dark:text-gray-100">
                                    ৳ {{ Number(employee.salary || 0).toLocaleString("en-BD") }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                        :class="{
                                            'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 ring-1 ring-green-200 dark:ring-green-800': employee.status === 'active',
                                            'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 ring-1 ring-gray-200 dark:ring-gray-600': employee.status === 'inactive',
                                            'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 ring-1 ring-red-200 dark:ring-red-800': employee.status === 'terminated',
                                        }"
                                    >
                                        {{ employee.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link :href="route('admin.employees.show', employee.id)" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-indigo-50 hover:text-indigo-600 transition">
                                            <i class="bi bi-eye"></i>
                                        </Link>
                                        <Link :href="route('admin.employees.edit', employee.id)" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-amber-50 hover:text-amber-600 transition">
                                            <i class="bi bi-pencil"></i>
                                        </Link>
                                        <button @click="confirmDelete(employee)" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-red-50 hover:text-red-600 transition">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!employees?.data?.length">
                                <td colspan="7" class="px-6 py-16 text-center text-gray-500">
                                    No employees found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="employees?.links?.length > 3"
                 class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 px-6 py-4 flex items-center justify-between flex-wrap gap-3">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Showing {{ employees.from }}–{{ employees.to }} of {{ employees.total }}
                </p>
                <div class="flex gap-1 flex-wrap">
                    <Link
                        v-for="link in employees.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-3 py-1.5 text-sm rounded-lg transition"
                        :class="link.active
                            ? 'bg-indigo-600 text-white'
                            : link.url
                                ? 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                : 'text-gray-300 dark:text-gray-600 cursor-not-allowed'"
                    />
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
            @click.self="showDeleteModal = false"
        >
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
                <div class="flex items-start gap-4 mb-5">
                    <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
                        <i class="bi bi-trash3-fill text-red-600 dark:text-red-400 text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg">Delete Employee?</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Are you sure you want to delete
                            <strong>{{ selectedEmployee?.first_name }} {{ selectedEmployee?.last_name }}</strong>?
                        </p>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button @click="showDeleteModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button @click="deleteEmployee" :disabled="deleteForm.processing" class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 flex items-center gap-2">
                        <i v-if="deleteForm.processing" class="bi bi-arrow-repeat animate-spin"></i>
                        <i v-else class="bi bi-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import EmployeeAvatar from "@/Components/EmployeeAvatar.vue"; // ✅ Import the new component

const props = defineProps({
    employees:    { type: Object, default: () => ({ data: [], total: 0 }) },
    departments:  { type: Array,  default: () => [] },
    designations: { type: Array,  default: () => [] },
    stats:        { type: Object, default: () => ({ active: 0, inactive: 0 }) },
    filters:      { type: Object, default: () => ({}) },
});

/* ========== View Mode ========== */
const viewMode = ref(localStorage.getItem('employeeViewMode') || 'list');

watch(viewMode, (val) => {
    localStorage.setItem('employeeViewMode', val);
});

/* ========== Filters ========== */
const filters = ref({
    search:        props.filters?.search || "",
    department_id: props.filters?.department_id || "",
    status:        props.filters?.status || "",
});

const applyFilters = () => {
    router.get(route("admin.employees.index"), filters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

let searchTimeout = null;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
};

const hasActiveFilters = computed(() =>
    !!(filters.value.search || filters.value.department_id || filters.value.status)
);

const clearFilters = () => {
    filters.value = { search: "", department_id: "", status: "" };
    applyFilters();
};

/* ========== Delete ========== */
const showDeleteModal = ref(false);
const selectedEmployee = ref(null);
const deleteForm = useForm({});

const confirmDelete = (employee) => {
    selectedEmployee.value = employee;
    showDeleteModal.value = true;
};

const deleteEmployee = () => {
    if (!selectedEmployee.value) return;
    deleteForm.post(route("admin.employees.destroy", selectedEmployee.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            selectedEmployee.value = null;
        },
    });
};

/* ========== Sync filters ========== */
watch(() => props.filters, (newFilters) => {
    filters.value = {
        search:        newFilters?.search || "",
        department_id: newFilters?.department_id || "",
        status:        newFilters?.status || "",
    };
}, { deep: true });
</script>
