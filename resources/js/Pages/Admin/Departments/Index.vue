<template>
    <AdminLayout page-title="Departments">
        <Head title="Departments" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Departments</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Manage company departments
                    </p>
                </div>
                <Link
                    :href="route('admin.departments.create')"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition shadow-lg"
                >
                    <i class="bi bi-plus-lg"></i> Add Department
                </Link>
            </div>

            <!-- Flash -->
            <div v-if="$page.props.flash?.success"
                 class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 flex items-center gap-3">
                <i class="bi bi-check-circle-fill text-green-500 text-xl"></i>
                <p class="text-sm text-green-700 dark:text-green-300 font-medium">
                    {{ $page.props.flash.success }}
                </p>
            </div>
            <div v-if="$page.props.errors?.error"
                 class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 flex items-center gap-3">
                <i class="bi bi-exclamation-triangle-fill text-red-500 text-xl"></i>
                <p class="text-sm text-red-700 dark:text-red-300">{{ $page.props.errors.error }}</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                            <i class="bi bi-building text-indigo-600 dark:text-indigo-400"></i>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Total</p>
                    </div>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ stats.total }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <i class="bi bi-check-circle text-green-600 dark:text-green-400"></i>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Active</p>
                    </div>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ stats.active }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                            <i class="bi bi-x-circle text-gray-500 dark:text-gray-400"></i>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Inactive</p>
                    </div>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ stats.inactive }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex-1 min-w-[220px] relative">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input
                            v-model="search"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Search by name or code..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                        />
                    </div>
                    <select
                        v-model="status"
                        @change="applyFilters"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <button
                        @click="clearFilters"
                        class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
                    >
                        <i class="bi bi-x-lg mr-1"></i> Clear
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 uppercase text-xs">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold">Name</th>
                                <th class="px-6 py-3 text-left font-semibold">Code</th>
                                <th class="px-6 py-3 text-left font-semibold">Employees</th>
                                <th class="px-6 py-3 text-left font-semibold">Status</th>
                                <th class="px-6 py-3 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="d in departments.data" :key="d.id"
                                class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                            <i class="bi bi-building text-indigo-600 dark:text-indigo-400"></i>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800 dark:text-gray-100">{{ d.name }}</p>
                                            <p v-if="d.description" class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">
                                                {{ d.description }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="d.code"
                                          class="px-2.5 py-1 rounded-full text-xs font-mono font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                        {{ d.code }}
                                    </span>
                                    <span v-else class="text-gray-400 text-xs">—</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1 text-gray-700 dark:text-gray-200">
                                        <i class="bi bi-people"></i>
                                        {{ d.employees_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <button
                                        @click="toggleStatus(d)"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold transition"
                                        :class="d.is_active
                                            ? 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 ring-1 ring-green-200 dark:ring-green-800'
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 ring-1 ring-gray-200 dark:ring-gray-600'"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full"
                                              :class="d.is_active ? 'bg-green-500' : 'bg-gray-400'"></span>
                                        {{ d.is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link
                                            :href="route('admin.departments.edit', d.id)"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 transition"
                                            title="Edit"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </Link>
                                        <button
                                            @click="confirmDelete(d)"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-red-50 dark:hover:bg-red-900/30 hover:text-red-600 transition"
                                            title="Delete"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="!departments.data.length">
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                            <i class="bi bi-building text-3xl text-gray-400"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-700 dark:text-gray-200">No departments yet</h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                Create your first department to get started
                                            </p>
                                        </div>
                                        <Link
                                            :href="route('admin.departments.create')"
                                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition"
                                        >
                                            <i class="bi bi-plus-lg"></i> Add Department
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="departments.links?.length > 3"
                     class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between flex-wrap gap-3">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Showing {{ departments.from }}–{{ departments.to }} of {{ departments.total }}
                    </p>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in departments.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            v-html="link.label"
                            class="px-3 py-1.5 text-sm rounded-lg transition"
                            :class="link.active
                                ? 'bg-indigo-600 text-white'
                                : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                        />
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
                        <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg">Delete Department?</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            This will permanently delete <strong>{{ selected?.name }}</strong>.
                        </p>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button @click="showDeleteModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                    <button @click="deleteItem"
                            class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    departments: { type: Object, required: true },
    stats:       { type: Object, default: () => ({ total: 0, active: 0, inactive: 0 }) },
    filters:     { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

const showDeleteModal = ref(false);
const selected = ref(null);

const applyFilters = () => {
    router.get(route('admin.departments.index'), {
        search: search.value,
        status: status.value,
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    search.value = '';
    status.value = '';
    router.get(route('admin.departments.index'));
};

const toggleStatus = (department) => {
    router.post(route('admin.departments.toggle-status', department.id), {}, {
        preserveScroll: true,
    });
};

const confirmDelete = (department) => {
    selected.value = department;
    showDeleteModal.value = true;
};

const deleteItem = () => {
    router.delete(route('admin.departments.destroy', selected.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            selected.value = null;
        },
    });
};
</script>
