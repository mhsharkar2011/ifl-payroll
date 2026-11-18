<template>
  <AdminLayout page-title="Off Day Duty Payslip">
    <Head title="Off Day Duty Payslip" />

    <div class="max-w-3xl mx-auto space-y-6 print:max-w-none print:m-0">

      <!-- Header Actions (Print Hidden) -->
      <div class="flex items-center justify-between print:hidden">
        <Link :href="route('admin.payroll.index')" class="px-4 py-2 text-sm font-semibold bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
          <i class="bi bi-arrow-left mr-1"></i> Back to Payroll
        </Link>
        <button @click="window.print()" class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow hover:bg-indigo-700">
          <i class="bi bi-printer mr-1"></i> Print / PDF
        </button>
      </div>

      <!-- Document -->
      <div class="bg-white p-8 rounded-2xl shadow-xl border border-gray-200 print:shadow-none print:border-none">

        <div class="text-center border-b border-gray-200 pb-6 mb-6">
          <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-wider">Off Day Duty Allowance</h1>
          <p class="text-gray-500 mt-1">{{ monthName(payroll.month) }} {{ payroll.year }}</p>
        </div>

        <div class="grid grid-cols-2 gap-6 mb-8 text-sm">
          <div>
            <p class="text-gray-500">Employee Name</p>
            <p class="font-bold text-gray-800">{{ employeeName }}</p>
          </div>
          <div class="text-right">
            <p class="text-gray-500">Employee ID</p>
            <p class="font-bold text-gray-800">{{ payroll.employee?.employee_id }}</p>
          </div>
        </div>

        <!-- Calculation Breakdown -->
        <div class="bg-indigo-50 rounded-xl p-6 border border-indigo-100 mb-8">
          <h2 class="text-sm font-bold text-indigo-900 uppercase tracking-wider mb-4">Calculation Details</h2>

          <table class="w-full text-sm">
            <tbody class="divide-y divide-indigo-200/50">
              <tr>
                <td class="py-3 text-indigo-700">Basic Hourly Rate</td>
                <td class="py-3 text-right font-mono text-indigo-900">{{ formatCurrency(payroll.basic_salary / 208) }}</td>
              </tr>
              <tr>
                <td class="py-3 text-indigo-700">Overtime Multiplier</td>
                <td class="py-3 text-right font-mono text-indigo-900">2.0x</td>
              </tr>
              <tr>
                <td class="py-3 text-indigo-700 font-semibold">Total Off Day Hours</td>
                <td class="py-3 text-right font-mono font-bold text-indigo-900">{{ payroll.off_day_hours || 0 }} hrs</td>
              </tr>
            </tbody>
          </table>

          <div class="mt-4 pt-4 border-t border-indigo-200 flex justify-between items-center">
            <span class="font-bold text-indigo-900">Total Allowance</span>
            <span class="text-2xl font-bold text-indigo-600">{{ formatCurrency(payroll.off_day_amount || 0) }}</span>
          </div>
        </div>

        <!-- Signature -->
        <div class="flex justify-between mt-16 pt-8">
          <div class="text-center w-40">
            <div class="border-b border-gray-400 mb-2"></div>
            <p class="text-xs text-gray-500">HR Manager</p>
          </div>
          <div class="text-center w-40">
            <div class="border-b border-gray-400 mb-2"></div>
            <p class="text-xs text-gray-500">Employee Signature</p>
          </div>
        </div>

      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({ payroll: Object });

const employeeName = computed(() => {
  if (!props.payroll.employee) return "Unknown";
  return `${props.payroll.employee.first_name} ${props.payroll.employee.last_name}`;
});

const formatCurrency = (amount) => {
  return new Intl.NumberFormat("en-BD", { style: "currency", currency: "BDT", maximumFractionDigits: 0 }).format(amount || 0);
};

const monthName = (m) => {
  return ["January","February","March","April","May","June","July","August","September","October","November","December"][m-1];
};
</script>
