<template>
  <AdminLayout page-title="Payroll">
    <Head title="Payroll" />

    <div class="space-y-6">
      <!-- HEADER -->
      <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Payroll</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Manage monthly payroll runs · <strong>{{ monthLabel }} {{ filters.year }}</strong>
          </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
          <button
            @click="generateOpen = true"
            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition shadow-lg"
          >
            <i class="bi bi-lightning-charge" aria-hidden="true"></i>
            Generate Payroll
          </button>
        </div>
      </div>

      <!-- FLASH -->
      <div
        v-if="$page.props.flash?.success"
        class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 flex items-center gap-3"
        role="status"
      >
        <i class="bi bi-check-circle-fill text-green-500 text-xl" aria-hidden="true"></i>
        <p class="text-sm text-green-700 dark:text-green-300 font-medium">
          {{ $page.props.flash.success }}
        </p>
      </div>
      <div
        v-if="$page.props.flash?.error"
        class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 flex items-center gap-3"
        role="alert"
      >
        <i class="bi bi-exclamation-triangle-fill text-red-500 text-xl" aria-hidden="true"></i>
        <p class="text-sm text-red-700 dark:text-red-300 font-medium">
          {{ $page.props.flash.error }}
        </p>
      </div>

      <!-- STATS -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-5 text-white shadow-lg shadow-indigo-500/30">
          <div class="flex items-center justify-between mb-3">
            <i class="bi bi-people-fill text-3xl opacity-90" aria-hidden="true"></i>
          </div>
          <p class="text-xs uppercase font-semibold opacity-90 mb-1">Total Employees</p>
          <p class="text-3xl font-bold">{{ stats.total_employees }}</p>
        </div>
        <div class="bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl p-5 text-white shadow-lg shadow-emerald-500/30">
          <div class="flex items-center justify-between mb-3">
            <i class="bi bi-cash-stack text-3xl opacity-90" aria-hidden="true"></i>
          </div>
          <p class="text-xs uppercase font-semibold opacity-90 mb-1">Total Net Payroll</p>
          <p class="text-3xl font-bold">{{ formatCurrency(stats.total_net) }}</p>
        </div>
        <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-5 text-white shadow-lg shadow-amber-500/30">
          <div class="flex items-center justify-between mb-3">
            <i class="bi bi-hourglass-split text-3xl opacity-90" aria-hidden="true"></i>
          </div>
          <p class="text-xs uppercase font-semibold opacity-90 mb-1">Pending Approval</p>
          <p class="text-3xl font-bold">{{ stats.draft_count }}</p>
        </div>
        <div class="bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl p-5 text-white shadow-lg shadow-blue-500/30">
          <div class="flex items-center justify-between mb-3">
            <i class="bi bi-check2-circle text-3xl opacity-90" aria-hidden="true"></i>
          </div>
          <p class="text-xs uppercase font-semibold opacity-90 mb-1">Paid Out</p>
          <p class="text-3xl font-bold">{{ stats.paid_count }}</p>
        </div>
      </div>

      <!-- RECORDS -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <!-- Toolbar -->
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between flex-wrap gap-3">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center">
              <i class="bi bi-cash-coin text-indigo-600 dark:text-indigo-400" aria-hidden="true"></i>
            </div>
            <div>
              <h3 class="font-bold text-gray-800 dark:text-gray-100">Payroll Records</h3>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                Total: {{ payrolls?.total || 0 }} records
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2 flex-wrap">
            <!-- Month filter -->
            <select
              v-model.number="filters.month"
              @change="applyFilters"
              class="px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"
            >
              <option v-for="(name, i) in months" :key="i" :value="i + 1">{{ name }}</option>
            </select>

            <!-- Year filter -->
            <select
              v-model.number="filters.year"
              @change="applyFilters"
              class="px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"
            >
              <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
            </select>

            <!-- Status filter -->
            <select
              v-model="filters.status"
              @change="applyFilters"
              class="px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"
            >
              <option value="">All Status</option>
              <option value="draft">Draft</option>
              <option value="approved">Approved</option>
              <option value="paid">Paid</option>
            </select>

            <!-- Search -->
            <div class="relative">
              <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm" aria-hidden="true"></i>
              <input
                v-model="filters.search"
                @input="debouncedApplyFilters"
                type="text"
                placeholder="Search employee..."
                aria-label="Search employee"
                class="pl-9 pr-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none w-56"
              />
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 uppercase text-xs">
              <tr>
                <th class="px-5 py-3 text-left font-semibold">Employee</th>
                <th class="px-5 py-3 text-right font-semibold">Basic</th>
                <th class="px-5 py-3 text-right font-semibold">Hours</th>
                <th class="px-5 py-3 text-right font-semibold">OT</th>
                <th class="px-5 py-3 text-right font-semibold">Deductions</th>
                <th class="px-5 py-3 text-right font-semibold">Net Salary</th>
                <th class="px-5 py-3 text-left font-semibold">Status</th>
                <th class="px-5 py-3 text-right font-semibold">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-if="loading">
                <td colspan="8" class="px-6 py-12 text-center">
                  <div
                    class="inline-block w-8 h-8 border-3 border-indigo-600 border-t-transparent rounded-full animate-spin"
                    role="status"
                    aria-label="Loading"
                  ></div>
                </td>
              </tr>

              <tr
                v-else
                v-for="payroll in payrolls?.data || []"
                :key="payroll.id"
                class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition"
              >
                <td class="px-5 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-indigo-100 dark:ring-indigo-900/40 bg-gray-100 flex items-center justify-center shrink-0">
                      <img
                        v-if="payroll.employee?.avatar_url"
                        :src="payroll.employee.avatar_url"
                        :alt="employeeName(payroll)"
                        class="w-full h-full object-cover"
                        loading="lazy"
                      />
                      <img
                        v-else
                        :src="fallbackAvatar(payroll)"
                        :alt="employeeName(payroll)"
                        class="w-full h-full object-cover"
                        loading="lazy"
                      />
                    </div>
                    <div class="min-w-0">
                      <p class="font-semibold text-gray-800 dark:text-gray-100 truncate">
                        {{ employeeName(payroll) }}
                      </p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ payroll.employee?.employee_id || "-" }}
                      </p>
                    </div>
                  </div>
                </td>
                <td class="px-5 py-4 text-right font-mono text-gray-800 dark:text-gray-100">
                  {{ formatCurrency(payroll.basic_salary) }}
                </td>
                <td class="px-5 py-4 text-right font-mono text-gray-700 dark:text-gray-300">
                  {{ formatNumber(payroll.total_hours) }}h
                  <span class="block text-[10px] text-amber-600 dark:text-amber-400">
                    +{{ formatNumber(payroll.overtime_hours) }}h OT
                  </span>
                </td>
                <td class="px-5 py-4 text-right font-mono text-amber-600 dark:text-amber-400">
                  {{ formatCurrency(payroll.overtime_amount) }}
                </td>
                <td class="px-5 py-4 text-right font-mono text-red-600 dark:text-red-400">
                  -{{ formatCurrency(payroll.deductions) }}
                </td>
                <td class="px-5 py-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                  {{ formatCurrency(payroll.net_salary) }}
                </td>
                <td class="px-5 py-4">
                  <span
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold"
                    :class="statusClass(payroll.status)"
                  >
                    <i :class="statusIcon(payroll.status)" class="text-[10px]" aria-hidden="true"></i>
                    {{ statusLabel(payroll.status) }}
                  </span>
                </td>
                <td class="px-5 py-4">
                  <div class="flex items-center justify-end gap-1">
                    <Link
                      :href="route('admin.payroll.show', payroll.id)"
                      class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 transition"
                      title="View"
                    >
                      <i class="bi bi-eye" aria-hidden="true"></i>
                    </Link>
                    <button
                      v-if="payroll.status === 'draft'"
                      @click="approve(payroll)"
                      :disabled="actioning === payroll.id"
                      class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 transition disabled:opacity-40"
                      title="Approve"
                    >
                      <i
                        class="bi"
                        :class="actioning === payroll.id ? 'bi-arrow-repeat animate-spin' : 'bi-check2-circle'"
                        aria-hidden="true"
                      ></i>
                    </button>
                    <button
                      v-if="payroll.status === 'approved'"
                      @click="markPaid(payroll)"
                      :disabled="actioning === payroll.id"
                      class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-600 transition disabled:opacity-40"
                      title="Mark as paid"
                    >
                      <i
                        class="bi"
                        :class="actioning === payroll.id ? 'bi-arrow-repeat animate-spin' : 'bi-cash-coin'"
                        aria-hidden="true"
                      ></i>
                    </button>
                    <Link
                      :href="route('admin.payroll.edit', payroll.id)"
                      class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-amber-50 dark:hover:bg-amber-900/30 hover:text-amber-600 transition"
                      title="Edit"
                    >
                      <i class="bi bi-pencil" aria-hidden="true"></i>
                    </Link>
                    <button
                      @click="confirmDelete(payroll)"
                      class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-red-50 dark:hover:bg-red-900/30 hover:text-red-600 transition"
                      title="Delete"
                    >
                      <i class="bi bi-trash" aria-hidden="true"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="!loading && !(payrolls?.data || []).length">
                <td colspan="8" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center gap-3">
                    <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                      <i class="bi bi-cash-coin text-3xl text-gray-400" aria-hidden="true"></i>
                    </div>
                    <div>
                      <h3 class="font-semibold text-gray-700 dark:text-gray-200">
                        No payroll records
                      </h3>
                      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Generate payroll to get started
                      </p>
                    </div>
                    <button
                      @click="generateOpen = true"
                      class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition"
                    >
                      <i class="bi bi-lightning-charge" aria-hidden="true"></i>
                      Generate Payroll
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div
          v-if="payrolls?.links && payrolls.links.length > 3"
          class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between flex-wrap gap-3"
        >
          <div class="text-sm text-gray-500 dark:text-gray-400">
            Showing
            <span class="font-semibold text-gray-700 dark:text-gray-200">{{ payrolls.from }}</span>
            to
            <span class="font-semibold text-gray-700 dark:text-gray-200">{{ payrolls.to }}</span>
            of
            <span class="font-semibold text-gray-700 dark:text-gray-200">{{ payrolls.total }}</span>
          </div>
          <nav class="flex items-center gap-1 flex-wrap" aria-label="Pagination">
            <template v-for="(link, index) in payrolls.links" :key="index">
              <Link
                v-if="link.url"
                :href="link.url"
                v-html="link.label"
                class="px-3 py-1.5 text-sm rounded-lg transition"
                :class="
                  link.active
                    ? 'bg-indigo-600 text-white font-semibold'
                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                "
                :aria-current="link.active ? 'page' : undefined"
              />
              <span
                v-else
                v-html="link.label"
                class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600"
              />
            </template>
          </nav>
        </div>
      </div>
    </div>

    <!-- GENERATE MODAL -->
    <div
      v-if="generateOpen"
      class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
      @click.self="generateOpen = false"
      role="dialog"
      aria-modal="true"
      aria-labelledby="generate-modal-title"
    >
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
        <div class="flex items-start gap-3 mb-5">
          <div class="w-11 h-11 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
            <i class="bi bi-lightning-charge text-indigo-600 dark:text-indigo-400 text-xl" aria-hidden="true"></i>
          </div>
          <div class="flex-1">
            <h3 id="generate-modal-title" class="font-bold text-gray-800 dark:text-gray-100 text-lg">
              Generate Payroll
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
              Create payroll for all active employees for the selected period.
            </p>
          </div>
        </div>

        <form @submit.prevent="submitGenerate" class="space-y-4">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300 mb-1">
                Month
              </label>
              <select
                v-model.number="generate.month"
                class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"
              >
                <option v-for="(name, i) in months" :key="i" :value="i + 1">{{ name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300 mb-1">
                Year
              </label>
              <select
                v-model.number="generate.year"
                class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"
              >
                <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
              </select>
            </div>
          </div>

          <div
            class="px-3 py-2.5 rounded-lg text-xs bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300"
          >
            <i class="bi bi-info-circle mr-1" aria-hidden="true"></i>
            Existing records for this period will be <strong>skipped</strong>. Only new employees will be added.
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button
              type="button"
              @click="generateOpen = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="generating"
              class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition disabled:opacity-50 flex items-center gap-2"
            >
              <i v-if="generating" class="bi bi-arrow-repeat animate-spin" aria-hidden="true"></i>
              <i v-else class="bi bi-lightning-charge" aria-hidden="true"></i>
              {{ generating ? "Generating…" : "Generate" }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- DELETE MODAL -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
      @click.self="cancelDelete"
      role="dialog"
      aria-modal="true"
      aria-labelledby="delete-modal-title"
    >
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
        <div class="flex items-start gap-4 mb-5">
          <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
            <i class="bi bi-trash3-fill text-red-600 dark:text-red-400 text-xl" aria-hidden="true"></i>
          </div>
          <div class="flex-1">
            <h3 id="delete-modal-title" class="font-bold text-gray-800 dark:text-gray-100 text-lg">
              Delete Payroll?
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              Are you sure you want to delete the payroll for
              <strong>{{ selectedPayroll ? employeeName(selectedPayroll) : "" }}</strong>?
              This action cannot be undone.
            </p>
          </div>
        </div>
        <div class="flex justify-end gap-2">
          <button
            @click="cancelDelete"
            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="deletePayroll"
            :disabled="deleting"
            class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition disabled:opacity-50 flex items-center gap-2"
          >
            <i v-if="deleting" class="bi bi-arrow-repeat animate-spin" aria-hidden="true"></i>
            <i v-else class="bi bi-trash" aria-hidden="true"></i>
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- TOAST -->
    <Transition
      enter-active-class="transition duration-200"
      enter-from-class="opacity-0 translate-y-2"
      leave-active-class="transition duration-150"
      leave-to-class="opacity-0"
    >
      <div
        v-if="toast"
        class="fixed bottom-6 right-6 z-[60] max-w-sm px-4 py-3 rounded-xl shadow-lg border flex items-start gap-3"
        :class="
          toast.type === 'error'
            ? 'bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-800 text-red-700 dark:text-red-300'
            : 'bg-green-50 dark:bg-green-900/30 border-green-200 dark:border-green-800 text-green-700 dark:text-green-300'
        "
        :role="toast.type === 'error' ? 'alert' : 'status'"
      >
        <i
          class="bi text-lg mt-0.5"
          :class="toast.type === 'error' ? 'bi-x-circle-fill' : 'bi-check-circle-fill'"
          aria-hidden="true"
        ></i>
        <div class="text-sm font-medium">{{ toast.message }}</div>
        <button @click="toast = null" class="ml-2 opacity-60 hover:opacity-100" aria-label="Dismiss">
          <i class="bi bi-x-lg text-xs" aria-hidden="true"></i>
        </button>
      </div>
    </Transition>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import axios from "axios";

const props = defineProps({
  payrolls: { type: Object, default: () => ({ data: [], links: [] }) },
  stats:    { type: Object, default: () => ({}) },
  filters:  { type: Object, default: () => ({}) },
});

/* ========== Months / Years ========== */
const months = [
  "January","February","March","April","May","June",
  "July","August","September","October","November","December",
];
const years = (() => {
  const now = new Date().getFullYear();
  return [now - 1, now, now + 1];
})();

/* ========== Filters ========== */
const filters = reactive({
  month:  props.filters.month  ?? new Date().getMonth() + 1,
  year:   props.filters.year   ?? new Date().getFullYear(),
  status: props.filters.status ?? "",
  search: props.filters.search ?? "",
});

const monthLabel = computed(() => months[filters.month - 1]);

let searchTimer = null;
function debouncedApplyFilters() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(applyFilters, 350);
}

function applyFilters() {
  router.get(
    route("admin.payroll.index"),
    { ...filters },
    { preserveState: true, preserveScroll: true, replace: true }
  );
}

/* ========== Generate modal ========== */
const generateOpen = ref(false);
const generating = ref(false);
const generate = reactive({
  month: new Date().getMonth() + 1,
  year: new Date().getFullYear(),
});

function submitGenerate() {
  if (generating.value) return;
  generating.value = true;

  router.post(
    route("admin.payroll.generate"),
    { month: generate.month, year: generate.year },
    {
      preserveScroll: true,
      onSuccess: () => {
        generateOpen.value = false;
        showToast("Payroll generated successfully.");
      },
      onError: (e) => {
        showToast(e?.message || "Generate failed", "error");
      },
      onFinish: () => {
        generating.value = false;
      },
    }
  );
}

/* ========== Row actions ========== */
const actioning = ref(null);

function approve(payroll) {
  actioning.value = payroll.id;
  router.post(
    route("admin.payroll.approve", payroll.id),
    {},
    {
      preserveScroll: true,
      onSuccess: () => showToast("Payroll approved."),
      onError: (e) => showToast(e?.message || "Approval failed", "error"),
      onFinish: () => { actioning.value = null; },
    }
  );
}

function markPaid(payroll) {
  actioning.value = payroll.id;
  router.post(
    route("admin.payroll.mark-paid", payroll.id),
    {},
    {
      preserveScroll: true,
      onSuccess: () => showToast("Marked as paid."),
      onError: (e) => showToast(e?.message || "Update failed", "error"),
      onFinish: () => { actioning.value = null; },
    }
  );
}

/* ========== Delete ========== */
const showDeleteModal = ref(false);
const selectedPayroll = ref(null);
const deleting = ref(false);

function confirmDelete(payroll) {
  selectedPayroll.value = payroll;
  showDeleteModal.value = true;
}
function cancelDelete() {
  showDeleteModal.value = false;
  selectedPayroll.value = null;
}
function deletePayroll() {
  if (!selectedPayroll.value) return;
  deleting.value = true;
  router.delete(route("admin.payroll.destroy", selectedPayroll.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      cancelDelete();
      showToast("Payroll deleted.");
    },
    onError: (e) => showToast(e?.message || "Delete failed", "error"),
    onFinish: () => { deleting.value = false; },
  });
}

/* ========== Toast ========== */
const toast = ref(null);
let toastTimer = null;
function showToast(message, type = "success") {
  toast.value = { message, type };
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => { toast.value = null; }, 4000);
}

/* ========== Helpers ========== */
function employeeName(payroll) {
  if (!payroll?.employee) return "Unknown";
  return (
    [payroll.employee.first_name, payroll.employee.last_name]
      .filter(Boolean).join(" ") || payroll.employee.employee_id || "—"
  );
}
function fallbackAvatar(payroll) {
  const seed = encodeURIComponent(employeeName(payroll) || "?");
  return `https://ui-avatars.com/api/?background=6366f1&color=fff&name=${seed}`;
}
function formatCurrency(v) {
  return new Intl.NumberFormat("en-US", {
    style: "currency", currency: "USD", maximumFractionDigits: 2,
  }).format(Number(v) || 0);
}
function formatNumber(v) {
  return new Intl.NumberFormat("en-US", { maximumFractionDigits: 2 }).format(Number(v) || 0);
}
function statusLabel(s) {
  return { draft: "Draft", approved: "Approved", paid: "Paid" }[s] || s;
}
function statusIcon(s) {
  return {
    draft:    "bi-hourglass-split",
    approved: "bi-check2-circle",
    paid:     "bi-check-circle-fill",
  }[s] || "bi-circle";
}
function statusClass(s) {
  return {
    draft:    "bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300",
    approved: "bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300",
    paid:     "bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300",
  }[s] || "bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300";
}

/* ========== Keyboard ========== */
const onKeydown = (e) => {
  if (e.key === "Escape") {
    if (generateOpen.value) generateOpen.value = false;
    if (showDeleteModal.value) cancelDelete();
  }
};
onMounted(() => window.addEventListener("keydown", onKeydown));
onUnmounted(() => {
  window.removeEventListener("keydown", onKeydown);
  clearTimeout(toastTimer);
  clearTimeout(searchTimer);
});
</script>
