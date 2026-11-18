<template>
  <AdminLayout page-title="New Salary Structure">
    <Head title="New Salary Structure" />
    <div class="max-w-3xl mx-auto space-y-6">
      <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">New Salary Structure</h1>
      <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6">
        <SalaryForm
          :employees="employees"
          :action="route('admin.salary.store')"
          method="post"
          submit-label="Create"
        />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SalaryForm from '@/Components/SalaryForm.vue';

defineProps({ employees: Array });
</script>
