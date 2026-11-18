<template>
    <AdminLayout page-title="Create Department">
        <Head title="Create Department" />

        <div class="max-w-2xl mx-auto space-y-6">
            <div class="flex items-center gap-3">
                <Link
                    :href="route('admin.departments.index')"
                    class="w-10 h-10 rounded-lg flex items-center justify-center bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                    <i class="bi bi-arrow-left"></i>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Create Department</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Add a new department</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <DepartmentForm :form="form" submit-label="Create Department" @submit="submit" />
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DepartmentForm from '@/Components/Admin/DepartmentForm.vue';

const form = useForm({
    name:        '',
    code:        '',
    description: '',
    is_active:   true,
});

const submit = () => {
    form.post(route('admin.departments.store'));
};
</script>
