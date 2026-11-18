<template>
  <AdminLayout page-title="Holiday Duty Claim">
    <Head title="Festival Holiday Duty Claim" />

    <div class="max-w-4xl mx-auto space-y-6 print:max-w-none print:m-0">
      <!-- Print Actions -->
      <div class="flex items-center justify-between print:hidden">
        <button
          @click="$inertia.visit(route('admin.off-day-duties.index'))"
          class="px-4 py-2 text-sm font-semibold bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
        >
          <i class="bi bi-arrow-left mr-1"></i> Back
        </button>
        <button
          @click="printPage"
          class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow hover:bg-indigo-700"
        >
          <i class="bi bi-printer mr-1"></i> Print / Save PDF
        </button>
      </div>

      <!-- Document (A4 Optimized) -->
      <div
        id="print-section"
        class="bg-white p-6 rounded-2xl shadow-xl border border-gray-200 print:shadow-none print:border-none print:p-0"
      >
        <!-- Header -->
        <div class="text-center mb-4">
          <h1 class="text-lg font-bold text-gray-800 uppercase tracking-wide">
            INCREDIBLE FASHIONS LIMITED
          </h1>
          <h2 class="text-sm font-semibold text-gray-700 mt-1">
            Festival Holiday Duty Payment Claim
          </h2>
        </div>

        <!-- 1. Employee & Claim Information -->
        <h3 class="font-bold text-xs text-gray-800 mb-1 uppercase">
          1. Employee & Claim Information
        </h3>
        <table class="w-full border-collapse border border-gray-400 text-xs mb-4">
          <tbody>
            <tr>
              <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-50 w-40">
                Employee Name:
              </td>
              <td class="border border-gray-400 px-2 py-1 w-1/3">
                {{ employeeName }}
              </td>
              <td
                class="border border-gray-400 px-2 py-1 font-semibold bg-gray-50 w-40"
              >
                Month of Claim:
              </td>
              <td class="border border-gray-400 px-2 py-1">
                {{ monthName }} {{ year }}
              </td>
            </tr>
            <tr>
              <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-50"> Employee ID:</td>
              <td class="border border-gray-400 px-2 py-1">{{ employee.employee_id }}</td>
              <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-50">Current Basic Salary:</td>
              <td class="border border-gray-400 px-2 py-1 font-mono">{{ formatCurrency(basic_salary) }}</td>
            </tr>
            <tr>
              <td
                class="border border-gray-400 px-2 py-1 font-semibold bg-gray-50"
              >
                Department:
              </td>
              <td class="border border-gray-400 px-2 py-1">
                {{ employee.department?.name || "-" }}
              </td>
              <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-50">Phone:</td>
              <td class="border border-gray-400 px-2 py-1">{{ employee.phone }}</td>
            </tr>
            <tr>
              <td
                class="border border-gray-400 px-2 py-1 font-semibold bg-gray-50"
              >
                Designation:
              </td>
              <td class="border border-gray-400 px-2 py-1">
                {{ employee.designation?.name || "-" }}
              </td>
              <td class="border border-gray-400 px-2 py-1 font-semibold bg-gray-50">Email:</td>
              <td class="border border-gray-400 px-2 py-1">{{ employee.email }}</td>
            </tr>
            <tr>
              <td
                class="border border-gray-400 px-2 py-1 font-semibold bg-gray-50"
              >
                Bank Account Name:
              </td>
              <td class="border border-gray-400 px-2 py-1">
                {{ employee.bank?.bank_name || "-" }}
              </td>
                <td
                    class="border border-gray-400 px-2 py-1 font-semibold bg-gray-50"
                >
                    Bank Name:
                </td>
              <td class="border border-gray-400 px-2 py-1">
                {{ employee.bank?.bank_account_number || "-" }}
              </td>

            </tr>
          </tbody>
        </table>

        <!-- 2. Holiday Duty Log & Calculation Table -->
        <h3 class="font-bold text-xs text-gray-800 mb-1 mt-4 uppercase">
          2. Holiday Duty Log & Calculation Table
        </h3>
        <table
          class="w-full border-collapse border border-gray-400 text-[11px] mb-4 text-center"
        >
          <thead class="bg-gray-100">
            <tr>
              <th class="border border-gray-400 px-1 py-1">Date of Duty</th>
              <th class="border border-gray-400 px-1 py-1">Festival Holiday</th>
              <th class="border border-gray-400 px-1 py-1">Duty Hrs</th>
              <th class="border border-gray-400 px-1 py-1">
                Full Days<br />(&gt;= 8)
              </th>
              <th class="border border-gray-400 px-1 py-1">
                Pro-rata Hrs<br />(&lt; 8)
              </th>
              <th class="border border-gray-400 px-1 py-1">
                Comp. Leave<br />(Days)
              </th>
              <th class="border border-gray-400 px-1 py-1">Wages Earned</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="duty in duties" :key="duty.id">
              <td class="border border-gray-400 px-1 py-1">
                {{ formatDate(duty.duty_date) }}
              </td>
              <td class="border border-gray-400 px-1 py-1">
                {{ duty.reason || "Festival Holiday" }}
              </td>
              <td class="border border-gray-400 px-1 py-1">
                {{ duty.hours_worked }}
              </td>
              <td class="border border-gray-400 px-1 py-1">
                {{ duty.hours_worked >= 8 ? 2 : 0 }}
              </td>
              <td class="border border-gray-400 px-1 py-1">
                {{ duty.hours_worked < 8 ? duty.hours_worked : 0 }}
              </td>
              <td class="border border-gray-400 px-1 py-1">
                {{ duty.compensatory_leave_days }}
              </td>
              <td class="border border-gray-400 px-1 py-1 font-mono">
                {{ formatCurrency(duty.total_amount) }}
              </td>
            </tr>
            <!-- Empty rows if no duties -->
            <tr v-if="!duties.length">
              <td
                colspan="7"
                class="border border-gray-400 px-2 py-2 text-gray-500 italic"
              >
                No approved duties found.
              </td>
            </tr>
          </tbody>
          <tfoot class="font-bold bg-gray-50">
            <tr>
              <td
                class="border border-gray-400 px-1 py-1 text-right"
                colspan="2"
              >
                TOTALS
              </td>
              <td class="border border-gray-400 px-1 py-1">{{ totalHours }}</td>
              <td class="border border-gray-400 px-1 py-1">
                {{ totalFullDays }}
              </td>
              <td class="border border-gray-400 px-1 py-1">
                {{ totalProRataHours }}
              </td>
              <td class="border border-gray-400 px-1 py-1">
                {{ totalCompLeave }}
              </td>
              <td class="border border-gray-400 px-1 py-1 font-mono">
                {{ formatCurrency(totalWages) }}
              </td>
            </tr>
          </tfoot>
        </table>

        <!-- 3. Authorizations & Approvals -->
        <h3 class="font-bold text-xs text-gray-800 mb-1 mt-4 uppercase">
          3. Authorizations & Approvals
        </h3>

        <div class="space-y-3 text-[11px] border border-gray-400 p-3">
          <!-- Employee Declaration -->
          <div class="grid grid-cols-2 gap-4 mt-0 items-start">
            <div>
              <p class="font-bold mb-0">Declaration By</p>
              <p class="text-[10px] text-gray-600 mb-0">
                I hereby certify that I performed duty on the festival holidays
                listed above during the specified hours and that the
                calculations are accurate according to company policy.
              </p>
            </div>
            <div class="mt-0 text-right">
              <span>Signature: _________________________</span>
              <p class="mt-4 text-right">Date: ____________________________</p>
            </div>
          </div>

          <hr class="border-gray-300" />

          <!-- Manager Approval -->
          <div class="grid grid-cols-2 gap-4 mt-2 items-start">
            <div>
              <p class="font-bold mb-0">Approved By</p>
              <p class="text-[10px] text-gray-600 mb-2">
                I have verified the attendance logs and confirm the duty hours
                submitted by the employee are correct.
              </p>
            </div>
            <div class="mt-0 text-right">
              <span>Department Head:____________________________ </span>
              <p class="mt-4 text-right">Date: ____________________________
              </p>
            </div>
          </div>

          <hr class="border-gray-300" />

          <!-- Accounts Verification -->
          <div>
            <p class="font-bold mb-1">Verified By</p>
            <strong v-if="duties.length && duties[0].approver" class="inline-block border-b border-gray-800">
              {{ duties[0].approver.name }}
            </strong>
            <strong v-else>___________________</strong>
            <div class="grid grid-cols-3 gap-4 mt-2 items-start">
              <div>
                <p class="mb-1">
                  Total Payment:
                  <strong class="font-mono">{{
                    formatCurrency(totalWages)
                  }}</strong>
                </p>

                Total Compensatory Leave:
                <strong>{{ totalCompLeave }}</strong>
              </div>
              <div>
                <p>Accountant Signature: __________________________</p>
                <p class="mt-4">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Date: __________________________
                </p>
              </div>

              <div class="text-right">
                <p>Authorized Signature: __________________________</p>
                <p class="mt-4 text-right">Date: __________________________
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- 4. Policy Reference Rules -->
        <h3 class="font-bold text-xs text-gray-800 mb-1 mt-4 uppercase">
          4. Policy Reference Rules
        </h3>
        <ul
          class="list-disc list-inside text-[10px] text-gray-600 space-y-0.5 pl-2"
        >
          <li>
            <strong>(Leave & Wages):</strong> For each day of festival holiday
            duty, employees are entitled to 01 (one) day of compensatory leave
            AND 02 (two) days' basic salary as compensatory wages.
          </li>
          <li>
            <strong>(Duty Hours):</strong> The standard duty duration is 08
            hours. If duty is less than 08 hours, payment will be made on a
            pro-rata basis.
          </li>
          <li>
            <strong>(Calculations):</strong> Full day: ((Basic Salary ÷ 30) × 2)
            × Duty Days | Pro-rata: ((Basic Salary ÷ 30) × 2 ÷ 8) × Duty Hours
          </li>
        </ul>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from "vue";
import { Head } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
  employee: Object,
  basic_salary: Number,
  duties: Array,
  month: Number,
  year: Number,
});

const employeeName = computed(() => {
  return `${props.employee.first_name || ""} ${
    props.employee.last_name || ""
  }`.trim();
});

const monthName = computed(() => {
  const months = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December",
  ];
  return months[props.month - 1] || "";
});

// Aggregations
const totalHours = computed(() =>
  props.duties.reduce((acc, curr) => acc + curr.hours_worked, 0)
);
const totalFullDays = computed(
  () => props.duties.filter((d) => d.hours_worked >= 8).length * 2
);
const totalProRataHours = computed(() =>
  props.duties
    .filter((d) => d.hours_worked < 8)
    .reduce((acc, curr) => acc + curr.hours_worked, 0)
);
const totalCompLeave = computed(() =>
  props.duties.reduce((acc, curr) => acc + curr.compensatory_leave_days, 0)
);
const totalWages = computed(() =>
  props.duties.reduce((acc, curr) => acc + curr.total_amount, 0)
);

const formatCurrency = (amount) => {
  return new Intl.NumberFormat("en-BD", {
    style: "currency",
    currency: "BDT",
    maximumFractionDigits: 0,
  }).format(amount || 0);
};

const formatDate = (date) => {
  if (!date) return "";
  return new Date(date)
    .toLocaleDateString("en-GB", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    })
    .replace(/\//g, "-");
};

const printPage = () => {
  window.print();
};
</script>

<style>
/* ✅ ROBUST PRINT STYLES FOR A4 */
@page {
  size: A4 portrait;
  margin: 10mm;
}

@media print {
  /* Hide the sidebar, header, footer, and any UI elements */
  aside,
  nav,
  header,
  footer,
  .print\:hidden {
    display: none !important;
  }

  /* Force the body and layout to be plain white */
  body,
  html {
    background-color: white !important;
    height: auto !important;
    overflow: visible !important;
  }

  /* Reset the main layout paddings and scrollbars */
  .main-content,
  .layui-layout-admin .layui-body,
  main,
  #app {
    padding: 0 !important;
    margin: 0 !important;
    margin-left: 0 !important;
    overflow: visible !important;
    height: auto !important;
  }

  /* Make sure the document container takes up the full page */
  #print-section {
    box-shadow: none !important;
    border: none !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  /* Ensure tables don't break awkwardly across pages */
  table {
    page-break-inside: auto;
  }
  tr {
    page-break-inside: avoid;
    page-break-after: auto;
  }
  thead {
    display: table-header-group;
  }
  tfoot {
    display: table-footer-group;
  }
}
</style>
