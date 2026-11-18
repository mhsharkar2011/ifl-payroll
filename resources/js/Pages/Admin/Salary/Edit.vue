<template>
  <AdminLayout page-title="Edit Salary Structure">
    <Head title="Edit Salary Structure" />
    <div class="max-w-3xl mx-auto space-y-6">
      <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Edit Salary Structure</h1>
      <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6">
        <SalaryForm
          :employees="employees"
          :structure="structure"
          :action="route('admin.salary.update', structure.id)"
          method="put"
          submit-label="Update"
        />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SalaryForm from '@/Components/SalaryForm.vue';

defineProps({ employees: Array, structure: Object });
</script>
