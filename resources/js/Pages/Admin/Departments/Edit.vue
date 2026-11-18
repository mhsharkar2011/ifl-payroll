<template>
    <AdminLayout page-title="Edit Department">
        <Head title="Edit Department" />

        <div class="max-w-2xl mx-auto space-y-6">
            <div class="flex items-center gap-3">
                <Link
                    :href="route('admin.departments.index')"
                    class="w-10 h-10 rounded-lg flex items-center justify-center bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                    <i class="bi bi-arrow-left"></i>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Edit Department</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ department.name }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <DepartmentForm :form="form" submit-label="Update Department" @submit="submit" />
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DepartmentForm from '@/Components/Admin/DepartmentForm.vue';

const props = defineProps({
    department: { type: Object, required: true },
});

const form = useForm({
    name:        props.department.name,
    code:        props.department.code || '',
    description: props.department.description || '',
    is_active:   !!props.department.is_active,
});

const submit = () => {
    form.post(route('admin.departments.update', props.department.id));
};
</script>
