<template>
  <AdminLayout page-title="Payslip">
    <Head :title="`Payslip — ${employeeName}`" />

    <div class="max-w-4xl mx-auto space-y-6 print:max-w-none print:m-0 print:space-y-0">

      <!-- ==================== HEADER ACTIONS (Hidden on Print) ==================== -->
      <div class="flex items-center justify-between print:hidden">
        <div class="flex items-center gap-4">
          <Link
            :href="route('admin.payroll.index')"
            class="w-10 h-10 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center hover:bg-gray-50 transition"
          >
            <i class="bi bi-arrow-left text-gray-600 dark:text-gray-300"></i>
          </Link>
          <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Payslip</h1>
            <p class="text-sm text-gray-500">Generated on {{ formatDate(payroll.generated_at) }}</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button
            @click="printPayslip"
            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-lg transition"
          >
            <i class="bi bi-printer"></i> Print / Save PDF
          </button>
        </div>
      </div>

      <!-- ==================== PAYSLIP DOCUMENT ==================== -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden print:shadow-none print:border-none print:bg-white print:text-black">

        <!-- Document Header -->
        <div class="p-8 border-b border-gray-100 dark:border-gray-700 flex justify-between items-start print:border-gray-300">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-2xl shadow-lg print:shadow-none">
              <i class="bi bi-fingerprint"></i>
            </div>
            <div>
              <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 print:text-black">IFL-Payroll</h2>
              <p class="text-sm text-gray-500 dark:text-gray-400 print:text-gray-600">Employee Payslip</p>
            </div>
          </div>
          <div class="text-right">
            <p class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 print:text-indigo-600">
              {{ monthName(payroll.month) }} {{ payroll.year }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              Status:
              <span class="font-semibold capitalize">{{ payroll.status }}</span>
            </p>
          </div>
        </div>

        <!-- Employee Info -->
        <div class="p-8 bg-gray-50 dark:bg-gray-800/50 grid grid-cols-2 sm:grid-cols-4 gap-6 print:bg-white">
          <div>
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold mb-1">Employee Name</p>
            <p class="font-bold text-gray-800 dark:text-gray-100 print:text-black">{{ employeeName }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold mb-1">Employee ID</p>
            <p class="font-semibold text-gray-800 dark:text-gray-100 print:text-black">{{ payroll.employee?.employee_id || '-' }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold mb-1">Department</p>
            <p class="font-semibold text-gray-800 dark:text-gray-100 print:text-black">{{ payroll.employee?.department?.name || '-' }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold mb-1">Designation</p>
            <p class="font-semibold text-gray-800 dark:text-gray-100 print:text-black">{{ payroll.employee?.designation?.name || '-' }}</p>
          </div>
        </div>

        <!-- Salary Breakdown -->
        <div class="p-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 print:grid-cols-2">

            <!-- Earnings -->
            <div>
              <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700 pb-2 mb-4 print:text-black print:border-gray-300">
                Earnings
              </h3>
              <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 print:divide-gray-200">
                  <tr>
                    <td class="py-3 text-gray-600 dark:text-gray-400 print:text-gray-700">Basic Salary</td>
                    <td class="py-3 text-right font-medium text-gray-800 dark:text-gray-100 print:text-black">{{ formatCurrency(payroll.basic_salary) }}</td>
                  </tr>
                  <tr>
                    <td class="py-3 text-gray-600 dark:text-gray-400 print:text-gray-700">House Rent (50% of Basic)</td>
                    <td class="py-3 text-right font-medium text-gray-800 dark:text-gray-100 print:text-black">{{ formatCurrency(payroll.basic_salary * 0.5) }}</td>
                  </tr>

                  <!-- Dynamic Allowances (if passed from backend) or fallback to standard -->
                  <tr>
                    <td class="py-3 text-gray-600 dark:text-gray-400 print:text-gray-700">Medical Allowance</td>
                    <td class="py-3 text-right font-medium text-gray-800 dark:text-gray-100 print:text-black">{{ formatCurrency(payroll.medical_allowance ?? 750) }}</td>
                  </tr>
                  <tr>
                    <td class="py-3 text-gray-600 dark:text-gray-400 print:text-gray-700">Conveyance</td>
                    <td class="py-3 text-right font-medium text-gray-800 dark:text-gray-100 print:text-black">{{ formatCurrency(payroll.conveyance ?? 450) }}</td>
                  </tr>
                  <tr>
                    <td class="py-3 text-gray-600 dark:text-gray-400 print:text-gray-700">Food Allowance</td>
                    <td class="py-3 text-right font-medium text-gray-800 dark:text-gray-100 print:text-black">{{ formatCurrency(payroll.food_allowance ?? 1250) }}</td>
                  </tr>

                  <!-- Overtime -->
                  <tr v-if="payroll.overtime_amount > 0">
                    <td class="py-3 text-gray-600 dark:text-gray-400 print:text-gray-700">
                      Overtime Amount
                      <span class="text-[10px] text-gray-400 block">({{ payroll.overtime_hours }} hrs)</span>
                    </td>
                    <td class="py-3 text-right font-medium text-gray-800 dark:text-gray-100 print:text-black">{{ formatCurrency(payroll.overtime_amount) }}</td>
                  </tr>

                  <!-- ✅ NEW: Off Day Duty Allowance -->
                  <tr v-if="payroll.off_day_amount > 0">
                    <td class="py-3 text-gray-600 dark:text-gray-400 print:text-gray-700">
                      Off Day Duty Allowance
                      <span class="text-[10px] text-gray-400 block">({{ payroll.off_day_hours }} hrs @ 2x rate)</span>
                    </td>
                    <td class="py-3 text-right font-medium text-indigo-600 dark:text-indigo-400 print:text-black">
                      {{ formatCurrency(payroll.off_day_amount) }}
                    </td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr class="border-t-2 border-gray-200 dark:border-gray-700 print:border-gray-400">
                    <td class="py-3 font-bold text-gray-800 dark:text-gray-100 print:text-black">Total Earnings</td>
                    <td class="py-3 text-right font-bold text-gray-800 dark:text-gray-100 print:text-black">{{ formatCurrency(payroll.total_earnings) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>

            <!-- Deductions & Summary -->
            <div>
              <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700 pb-2 mb-4 print:text-black print:border-gray-300">
                Deductions
              </h3>
              <table class="w-full text-sm mb-6">
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 print:divide-gray-200">
                  <tr>
                    <td class="py-3 text-gray-600 dark:text-gray-400 print:text-gray-700">Total Deductions</td>
                    <td class="py-3 text-right font-medium text-red-600 dark:text-red-400 print:text-red-600">{{ formatCurrency(payroll.deductions) }}</td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr class="border-t-2 border-gray-200 dark:border-gray-700 print:border-gray-400">
                    <td class="py-3 font-bold text-gray-800 dark:text-gray-100 print:text-black">Total Deductions</td>
                    <td class="py-3 text-right font-bold text-red-600 dark:text-red-400 print:text-red-600">{{ formatCurrency(payroll.deductions) }}</td>
                  </tr>
                </tfoot>
              </table>

              <!-- Net Pay Card -->
              <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-4 border border-emerald-200 dark:border-emerald-800 print:bg-white print:border-gray-300">
                <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider mb-1 print:text-gray-600">
                  Net Payable Amount
                </p>
                <p class="text-3xl font-bold text-emerald-700 dark:text-emerald-400 print:text-black">
                  {{ formatCurrency(payroll.net_salary + (payroll.bonus || 0)) }}
                </p>
                <p v-if="payroll.bonus > 0" class="text-xs text-emerald-600 dark:text-emerald-500 mt-1 print:text-gray-500">
                  Includes Bonus: {{ formatCurrency(payroll.bonus) }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer / Signatures -->
        <div class="px-8 py-10 mt-8 border-t border-gray-100 dark:border-gray-700 flex justify-between print:border-gray-300">
          <div class="text-center">
            <div class="w-48 border-b border-gray-400 dark:border-gray-500 mb-2"></div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Employee Signature</p>
          </div>
          <div class="text-center">
            <div class="w-48 border-b border-gray-400 dark:border-gray-500 mb-2"></div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Authorized Signature</p>
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

const props = defineProps({
  payroll: { type: Object, required: true },
});

const employeeName = computed(() => {
  if (!props.payroll.employee) return "Unknown Employee";
  return (
    `${props.payroll.employee.first_name || ""} ${props.payroll.employee.last_name || ""}`.trim() ||
    "Unknown Employee"
  );
});

const formatCurrency = (amount) => {
  return new Intl.NumberFormat("en-BD", {
    style: "currency",
    currency: "BDT",
    maximumFractionDigits: 0,
  }).format(Number(amount) || 0);
};

const formatDate = (date) => {
  if (!date) return "-";
  return new Date(date).toLocaleDateString("en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
};

const monthName = (monthNumber) => {
  const months = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
  ];
  return months[monthNumber - 1] || "";
};

const printPayslip = () => {
  window.print();
};
</script>

<style>
/* Print Optimizations */
@media print {
  body {
    background-color: white !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
  /* Hide the sidebar and navigation menus */
  aside, nav, header, footer {
    display: none !important;
  }
  /* Reset the main layout margin */
  .main-content, .lg\:pl-64 {
    padding-left: 0 !important;
    margin-left: 0 !important;
  }
}
</style>
