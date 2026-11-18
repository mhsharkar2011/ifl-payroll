<template>
  <AdminLayout page-title="Off Day Duties">
    <Head title="Off Day Duties" />

    <div class="space-y-6">
      <!-- Header & View Toggles -->
      <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Off Day Duty Records</h1>
          <p class="text-sm text-gray-500">Manage date-wise off-day/holiday work and allowances.</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <!-- View Mode Toggle -->
          <div class="inline-flex bg-white border border-gray-200 rounded-lg p-1 shadow-sm">
            <button @click="changeMode('daily')" :class="filters.view_mode === 'daily' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100'" class="px-3 py-1.5 text-xs font-semibold rounded-md transition">
              Daily
            </button>
            <button @click="changeMode('monthly')" :class="filters.view_mode === 'monthly' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100'" class="px-3 py-1.5 text-xs font-semibold rounded-md transition">
              Monthly
            </button>
            <button @click="changeMode('yearly')" :class="filters.view_mode === 'yearly' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100'" class="px-3 py-1.5 text-xs font-semibold rounded-md transition">
              Yearly
            </button>
          </div>

          <!-- Dynamic Date Pickers -->
          <div class="flex items-center gap-2">
            <input v-if="filters.view_mode === 'daily'" v-model="filters.date" @change="refresh" type="date" class="px-3 py-2 border rounded-lg text-sm" />
            <template v-else-if="filters.view_mode === 'monthly'">
              <select v-model.number="filters.month" @change="refresh" class="px-3 py-2 border rounded-lg text-sm">
                <option v-for="(m, i) in months" :key="i" :value="i + 1">{{ m }}</option>
              </select>
              <input v-model.number="filters.year" @change="refresh" type="number" class="w-24 px-3 py-2 border rounded-lg text-sm" />
            </template>
            <input v-else-if="filters.view_mode === 'yearly'" v-model.number="filters.year" @change="refresh" type="number" class="w-28 px-3 py-2 border rounded-lg text-sm" />
          </div>

          <!-- ✅ Claim Form Employee Select & Print Button -->
          <div class="flex items-center gap-2 border-l border-gray-300 pl-2 ml-2">
            <select v-model="claimEmployee" class="px-3 py-2 border rounded-lg text-sm w-48">
              <option value="" disabled>Select for Claim Form...</option>
              <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                {{ emp.employee_id }} — {{ emp.first_name }} {{ emp.last_name }}
              </option>
            </select>
            <button
              @click="openClaimReport"
              :disabled="!claimEmployee"
              class="px-4 py-2 bg-gray-800 text-white text-sm font-semibold rounded-lg hover:bg-gray-900 shadow print:hidden disabled:opacity-50"
            >
              <i class="bi bi-file-earmark-text mr-1"></i> Claim Form
            </button>
          </div>

          <button @click="printSummary" class="px-4 py-2 bg-gray-600 text-white text-sm font-semibold rounded-lg hover:bg-gray-700 shadow print:hidden ml-2">
            <i class="bi bi-printer mr-1"></i> Print List
          </button>

          <button @click="openCreateModal" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 shadow print:hidden">
            <i class="bi bi-plus-lg mr-1"></i> Add Duty
          </button>
        </div>
      </div>

      <!-- Flash Messages -->
      <div v-if="$page.props.flash?.success" class="bg-green-50 text-green-700 p-4 rounded-xl border border-green-200 print:hidden">
        <i class="bi bi-check-circle-fill mr-2"></i> {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash?.error" class="bg-red-50 text-red-700 p-4 rounded-xl border border-red-200 print:hidden">
        <i class="bi bi-exclamation-triangle-fill mr-2"></i> {{ $page.props.flash.error }}
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 print:hidden">
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
          <p class="text-xs font-semibold text-gray-500 uppercase">Total Hours ({{ filters.view_mode }})</p>
          <p class="text-2xl font-bold text-indigo-600">{{ totals.hours }} hrs</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
          <p class="text-xs font-semibold text-gray-500 uppercase">Total Allowance ({{ filters.view_mode }})</p>
          <p class="text-2xl font-bold text-emerald-600">{{ formatCurrency(totals.amount) }}</p>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden print:border-none print:shadow-none">
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs print:bg-white print:text-black">
              <tr>
                <th class="px-6 py-3">Date</th>
                <th class="px-6 py-3">Employee ID</th>
                <th class="px-6 py-3">Employee Name</th>
                <th class="px-6 py-3">Hours</th>
                <th class="px-6 py-3">Comp. Leave</th>
                <th class="px-6 py-3">Amount (2x)</th>
                <th class="px-6 py-3">Bank Account No.</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Approved By</th>
                <th class="px-6 py-3">Reason / Notes</th>
                <th class="px-6 py-3 text-right print:hidden">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="duty in duties" :key="duty.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 font-medium text-gray-800">
                  {{ formatDate(duty.duty_date) }}
                </td>
                <td class="px-6 py-4 font-mono text-gray-700">{{ duty.employee.employee_id }}</td>
                <td class="px-6 py-4">
                  <p class="font-semibold text-gray-800">{{ duty.employee.first_name }} {{ duty.employee.last_name }}</p>
                </td>
                <td class="px-6 py-4 font-mono">{{ duty.hours_worked }} hrs</td>
                <td class="px-6 py-4 font-mono font-semibold text-indigo-600">
                  {{ duty.compensatory_leave_days || 0 }} days
                </td>
                <td class="px-6 py-4 font-mono font-bold text-emerald-600">{{ formatCurrency(duty.total_amount) }}</td>

                <!-- ✅ FIXED: Correctly accessing eager-loaded bank data -->
                <td class="px-6 py-4 font-mono text-gray-700">
                  {{ duty.employee.bank?.bank_account_number || "N/A" }}
                </td>

                <td class="px-6 py-4">
                  <span class="px-2 py-1 text-[8px] font-bold uppercase rounded-md"
                    :class="duty.status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'">
                    {{ duty.status }}
                  </span>
                </td>

                <td class="px-6 py-4 text-gray-600">
                  {{ duty.approver?.name || '-' }}
                </td>

                <!-- ✅ FIXED: Removed the weird ternary condition from Reason -->
                <td class="px-6 py-4 text-gray-600">
                  {{ duty.reason || 'No Reason Provided' }}
                </td>

                <td class="px-6 py-4 print:hidden">
                  <div class="flex items-center justify-end gap-2">
                    <button @click="openPayslip(duty)" class="text-indigo-500 hover:text-indigo-700" title="Payslip">
                      <i class="bi bi-receipt"></i>
                    </button>
                    <button @click="openEditModal(duty)" class="text-amber-500 hover:text-amber-700" title="Edit">
                      <i class="bi bi-pencil-square"></i>
                    </button>
                    <button @click="deleteDuty(duty)" class="text-red-500 hover:text-red-700" title="Delete">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!duties.length">
                <!-- ✅ FIXED: Changed colspan from 7 to 10 -->
                <td colspan="10" class="px-6 py-12 text-center text-gray-500">
                  No off-day duties recorded for this {{ filters.view_mode }}.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Create / Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 print:hidden">
      <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
        <h3 class="font-bold text-gray-800 text-lg mb-4">
          {{ isEditing ? 'Edit Duty Record' : 'Add Off Day Duty' }}
        </h3>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Employee</label>
            <select v-model="form.employee_id" required :disabled="isEditing" class="w-full px-3 py-2 border rounded-lg text-sm disabled:bg-gray-100">
              <option value="" disabled>Select employee...</option>
              <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                {{ emp.employee_id }} — {{ emp.first_name }} {{ emp.last_name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Duty Date</label>
            <input v-model="form.duty_date" type="date" required :disabled="isEditing" class="w-full px-3 py-2 border rounded-lg text-sm disabled:bg-gray-100" />
          </div>

          <!-- ✅ Auto-Fill Hours Section -->
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Hours Worked</label>
            <div class="flex gap-2">
              <input v-model.number="form.hours_worked" type="number" step="0.5" min="0.5" required class="w-full px-3 py-2 border rounded-lg text-sm" />
              <button
                type="button"
                @click="fetchHours"
                :disabled="!form.employee_id || !form.duty_date || fetchingHours"
                class="px-3 py-2 bg-indigo-50 text-indigo-700 text-xs font-semibold rounded-lg hover:bg-indigo-100 disabled:opacity-50 transition shrink-0 whitespace-nowrap flex items-center gap-1"
              >
                <i v-if="fetchingHours" class="bi bi-arrow-repeat animate-spin"></i>
                <i v-else class="bi bi-magic"></i>
                {{ fetchingHours ? 'Fetching...' : 'Auto-Fill' }}
              </button>
            </div>
            <p class="text-[10px] text-gray-500 mt-1">Click "Auto-Fill" to calculate hours from attendance logs.</p>
          </div>

          <!-- Rule 4.5 & 4.6 Helper -->
          <div class="p-3 bg-blue-50 text-blue-800 text-xs rounded-lg space-y-1">
            <p><strong>Rule 4.5 & 4.6:</strong></p>
            <p>• 8+ Hours: Pays 2x Basic Salary + 1 day Comp Leave.</p>
            <p>• &lt; 8 Hours: Paid Pro-rata + Fractional Comp Leave.</p>
          </div>

          <div v-if="isEditing">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
            <select v-model="form.status" class="w-full px-3 py-2 border rounded-lg text-sm">
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Reason / Notes</label>
            <input v-model="form.reason" type="text" placeholder="e.g. Friday Duty" class="w-full px-3 py-2 border rounded-lg text-sm" />
          </div>

          <div class="flex justify-end gap-2 pt-4">
            <button type="button" @click="showModal = false" class="px-4 py-2 text-sm border rounded-lg">Cancel</button>
            <button type="submit" :disabled="form.processing" class="px-4 py-2 text-sm text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50">
              Save Record
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ✅ Single Payslip Modal -->
    <div v-if="showPayslip" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 print:bg-white print:static print:p-0">
      <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden print:shadow-none print:rounded-none">
        <div class="bg-indigo-600 text-white p-6 flex justify-between items-start print:bg-white print:text-black print:border-b print:border-gray-300">
          <div>
            <h2 class="text-xl font-bold">Off Day Duty Payslip</h2>
            <p class="text-indigo-100 text-sm print:text-gray-600">{{ formatDate(payslipData.duty_date) }}</p>
          </div>
          <button @click="showPayslip = false" class="text-white/70 hover:text-white print:hidden">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div class="p-6 space-y-6">
          <div class="grid grid-cols-2 gap-4 text-sm border-b border-gray-100 pb-4">
            <div>
              <p class="text-gray-500 text-xs uppercase mb-1">Employee</p>
              <p class="font-bold text-gray-800">{{ payslipData.employee.first_name }} {{ payslipData.employee.last_name }}</p>
              <p class="text-xs text-gray-500">{{ payslipData.employee.employee_id }}</p>
            </div>
            <div class="text-right">
              <p class="text-gray-500 text-xs uppercase mb-1">Status</p>
              <span class="inline-block px-2 py-1 text-xs font-bold uppercase rounded-md" :class="payslipData.status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'">
                {{ payslipData.status }}
              </span>
            </div>
          </div>

          <table class="w-full text-sm">
            <tbody class="divide-y divide-gray-100">
              <tr>
                <td class="py-2 text-gray-600">Hours Worked</td>
                <td class="py-2 text-right font-mono font-semibold">{{ payslipData.hours_worked }} hrs</td>
              </tr>
              <tr>
                <td class="py-2 text-gray-600">Compensatory Leave</td>
                <td class="py-2 text-right font-mono font-semibold text-indigo-600">{{ payslipData.compensatory_leave_days }} days</td>
              </tr>
              <tr>
                <td class="py-2 text-gray-600">Rate Applied</td>
                <td class="py-2 text-right font-mono text-gray-500">{{ formatCurrency(payslipData.hourly_rate) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="border-t-2 border-gray-200">
                <td class="py-3 font-bold text-gray-800">Total Allowance</td>
                <td class="py-3 text-right font-bold text-emerald-600 text-lg">{{ formatCurrency(payslipData.total_amount) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2 print:hidden">
          <button @click="showPayslip = false" class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-100">Close</button>
          <button @click="printPayslip" class="px-4 py-2 text-sm text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow">
            <i class="bi bi-printer mr-1"></i> Print / PDF
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from "vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import axios from "axios";

const props = defineProps({
  duties: Array,
  employees: Array,
  totals: Object,
  filters: Object,
});

const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

const filters = ref({
  view_mode: props.filters.view_mode || 'monthly',
  date: props.filters.date || new Date().toISOString().split('T')[0],
  month: props.filters.month || new Date().getMonth() + 1,
  year: props.filters.year || new Date().getFullYear(),
});

const showModal = ref(false);
const isEditing = ref(false);
const selectedId = ref(null);

const showPayslip = ref(false);
const payslipData = ref({});
const fetchingHours = ref(false);

// ✅ State for Claim Form
const claimEmployee = ref('');

const form = useForm({
  employee_id: '',
  duty_date: '',
  hours_worked: 8,
  reason: '',
  status: 'pending'
});

const changeMode = (mode) => {
  filters.value.view_mode = mode;
  refresh();
};

const refresh = () => {
  router.get(route('admin.off-day-duties.index'), filters.value, { preserveState: true, replace: true });
};

// ✅ Open Claim Report in new tab
const openClaimReport = () => {
  if (!claimEmployee.value) return;

  // Explicitly build the query string to guarantee employee_id=...
  const query = new URLSearchParams({
    employee_id: claimEmployee.value,
    month: filters.value.month,
    year: filters.value.year
  }).toString();

  const url = route('admin.off-day-duties.report') + '?' + query;
  window.open(url, '_blank');
};

const openCreateModal = () => {
  isEditing.value = false;
  form.reset();
  form.clearErrors();
  form.duty_date = new Date().toISOString().split('T')[0];
  showModal.value = true;
};

const openEditModal = (duty) => {
  isEditing.value = true;
  selectedId.value = duty.id;
  form.employee_id = duty.employee_id;
  form.duty_date = duty.duty_date.split('T')[0];
  form.hours_worked = duty.hours_worked;
  form.reason = duty.reason;
  form.status = duty.status;
  form.clearErrors();
  showModal.value = true;
};

const submit = () => {
  if (isEditing.value) {
    form.put(route('admin.off-day-duties.update', selectedId.value), {
      onSuccess: () => { showModal.value = false; }
    });
  } else {
    form.post(route('admin.off-day-duties.store'), {
      onSuccess: () => { showModal.value = false; }
    });
  }
};

const deleteDuty = (duty) => {
  if (confirm(`Delete duty record for ${duty.employee.first_name} on ${duty.duty_date}?`)) {
    router.delete(route('admin.off-day-duties.destroy', duty.id));
  }
};

const fetchHours = async () => {
  if (!form.employee_id || !form.duty_date) return;

  fetchingHours.value = true;
  try {
    const { data } = await axios.get(route('admin.off-day-duties.hours'), {
      params: {
        employee_id: form.employee_id,
        date: form.duty_date
      }
    });

    if (data.hours > 0) {
      form.hours_worked = data.hours;
    } else {
      alert(data.message || "No attendance records found for this date.");
    }
  } catch (error) {
    console.error("Failed to fetch hours:", error);
    alert("Could not fetch hours from attendance.");
  } finally {
    fetchingHours.value = false;
  }
};

const openPayslip = (duty) => {
  payslipData.value = duty;
  showPayslip.value = true;
};

const printPayslip = () => {
  window.print();
};

const printSummary = () => {
  window.print();
};

const formatCurrency = (amt) => new Intl.NumberFormat('en-BD', { style: 'currency', currency: 'BDT' }).format(amt || 0);
const formatDate = (d) => new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
</script>

<style>
/* Print Optimizations */
@media print {
  body {
    background-color: white !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
  aside, nav, header, footer, .print\:hidden {
    display: none !important;
  }
  .main-content, .lg\:pl-64 {
    padding-left: 0 !important;
    margin-left: 0 !important;
  }
}
</style>
