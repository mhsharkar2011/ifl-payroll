<template>
  <AdminLayout page-title="Manual Off Day Duty">
    <Head title="Manual Off Day Duty" />

    <div class="max-w-6xl mx-auto space-y-6">

      <!-- Header -->
      <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Off Day Duty Entry</h1>
          <p class="text-sm text-gray-500 mt-1">Manually assign 2x allowance for holiday/Friday work.</p>
        </div>
        <div class="flex gap-2">
          <input v-model.number="filters.month" @change="refreshData" type="number" min="1" max="12" class="w-20 px-3 py-2 border rounded-lg text-sm" />
          <input v-model.number="filters.year" @change="refreshData" type="number" class="w-28 px-3 py-2 border rounded-lg text-sm" />
        </div>
      </div>

      <!-- Flash Messages -->
      <div v-if="$page.props.flash?.success" class="bg-green-50 text-green-700 p-4 rounded-xl border border-green-200">
        <i class="bi bi-check-circle-fill mr-2"></i> {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash?.error" class="bg-red-50 text-red-700 p-4 rounded-xl border border-red-200">
        <i class="bi bi-exclamation-triangle-fill mr-2"></i> {{ $page.props.flash.error }}
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Form Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 h-fit">
          <h3 class="font-bold text-gray-800 dark:text-gray-100 mb-4">Add Off Day Hours</h3>

          <form @submit.prevent="submit" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">Employee</label>
              <select v-model="form.employee_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="" disabled>Select employee...</option>
                <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                  {{ emp.employee_id }} — {{ emp.first_name }} {{ emp.last_name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">Off Day Hours</label>
              <input v-model.number="form.off_day_hours" type="number" step="0.5" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="e.g. 8" />
            </div>

            <div class="p-3 bg-indigo-50 text-indigo-800 text-xs rounded-lg">
              <i class="bi bi-info-circle mr-1"></i>
              This will automatically calculate a 2x hourly rate based on the employee's salary structure.
            </div>

            <button type="submit" :disabled="form.processing" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg disabled:opacity-50 flex items-center justify-center gap-2">
              <i v-if="form.processing" class="bi bi-arrow-repeat animate-spin"></i>
              <i v-else class="bi bi-save"></i> Save Entry
            </button>
          </form>
        </div>

        <!-- Table Card -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-bold text-gray-800 dark:text-gray-100">Current Month Records</h3>
            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">{{ payrolls.length }} Records</span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                <tr>
                  <th class="px-4 py-3 text-left">Employee</th>
                  <th class="px-4 py-3 text-right">Basic</th>
                  <th class="px-4 py-3 text-right">Off Day Hours</th>
                  <th class="px-4 py-3 text-right">Off Day Amount</th>
                  <th class="px-4 py-3 text-center">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="p in payrolls" :key="p.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3">
                    <p class="font-semibold text-gray-800">{{ p.employee_name }}</p>
                    <p class="text-xs text-gray-500">{{ p.employee_code }}</p>
                  </td>
                  <td class="px-4 py-3 text-right font-mono text-gray-600">{{ formatCurrency(p.basic_salary) }}</td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-indigo-600">{{ p.off_day_hours }} hrs</td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-emerald-600">{{ formatCurrency(p.off_day_amount) }}</td>
                  <td class="px-4 py-3 text-center">
                    <span :class="p.is_generated ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700'" class="px-2 py-1 text-[10px] font-bold uppercase rounded-md">
                      {{ p.is_generated ? 'Generated' : 'Draft' }}
                    </span>
                  </td>
                </tr>
                <tr v-if="!payrolls.length">
                  <td colspan="5" class="px-4 py-8 text-center text-gray-500">No records generated for this month yet.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from "vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
  payrolls: Array,
  employees: Array,
  filters: Object,
});

const form = useForm({
  employee_id: "",
  month: props.filters.month,
  year: props.filters.year,
  off_day_hours: 0,
  notes: "",
});

const submit = () => {
  form.post(route("admin.payroll.off-day-manual.store"), {
    preserveScroll: true,
    onSuccess: () => form.reset("employee_id", "off_day_hours", "notes"),
  });
};

const refreshData = () => {
  router.get(route("admin.payroll.off-day-manual"), {
    month: form.month,
    year: form.year,
  }, { preserveState: true, replace: true });
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat("en-BD", { style: "currency", currency: "BDT", maximumFractionDigits: 0 }).format(amount || 0);
};
</script>
