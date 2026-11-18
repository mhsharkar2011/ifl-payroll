<template>
  <AdminLayout page-title="Monthly Attendance">
    <Head :title="`Monthly Attendance — ${monthLabel}`" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
          <Link
            :href="route('admin.attendance.index')"
            class="w-10 h-10 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-700 transition"
          >
            <i class="bi bi-arrow-left text-gray-600 dark:text-gray-300"></i>
          </Link>
          <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
              Monthly Attendance
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              {{ monthLabel }}
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <a
            :href="route('admin.attendance.monthly.export', filters)"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition"
          >
            <i class="bi bi-download"></i> Export CSV
          </a>
          <Link
            :href="route('admin.attendance.index')"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition"
          >
            <i class="bi bi-calendar-day"></i> Daily View
          </Link>
        </div>
      </div>

      <!-- Month Picker + Filters -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          <div>
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">
              Month
            </label>
            <select
              v-model="filters.month"
              @change="navigate"
              class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg text-sm"
            >
              <option v-for="(name, idx) in monthNames" :key="idx" :value="idx + 1">
                {{ name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">
              Year
            </label>
            <select
              v-model="filters.year"
              @change="navigate"
              class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg text-sm"
            >
              <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">
              Department
            </label>
            <select
              v-model="filters.department_id"
              @change="navigate"
              class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg text-sm"
            >
              <option value="">All Departments</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                {{ dept.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">
              Search
            </label>
            <input
              v-model="filters.search"
              @input="debouncedSearch"
              type="text"
              placeholder="Employee name or ID..."
              class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg text-sm"
            />
          </div>
        </div>

        <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between flex-wrap gap-3">
          <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
            <span>Per page:</span>
            <select
              v-model="filters.per_page"
              @change="navigate"
              class="px-2 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md text-xs"
            >
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
          </div>

          <button
            @click="clearFilters"
            v-if="hasFilters"
            class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 font-medium flex items-center gap-1"
          >
            <i class="bi bi-x-circle"></i> Clear filters
          </button>
        </div>
      </div>

      <!-- Summary Stats -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-5 text-white shadow-lg shadow-indigo-500/30">
          <div class="flex items-center justify-between mb-3">
            <i class="bi bi-calendar-month text-3xl opacity-90"></i>
            <span class="text-xs font-semibold bg-white/20 px-2 py-1 rounded-full">Days</span>
          </div>
          <p class="text-xs uppercase font-semibold opacity-90 mb-1">Days in {{ monthLabel }}</p>
          <p class="text-3xl font-bold">{{ stats.working_days }}</p>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-5 text-white shadow-lg shadow-green-500/30">
          <div class="flex items-center justify-between mb-3">
            <i class="bi bi-fingerprint text-3xl opacity-90"></i>
            <span class="text-xs font-semibold bg-white/20 px-2 py-1 rounded-full">Punches</span>
          </div>
          <p class="text-xs uppercase font-semibold opacity-90 mb-1">Total Punches</p>
          <p class="text-3xl font-bold">{{ stats.total_punches }}</p>
        </div>

        <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-5 text-white shadow-lg shadow-amber-500/30">
          <div class="flex items-center justify-between mb-3">
            <i class="bi bi-people-fill text-3xl opacity-90"></i>
            <span class="text-xs font-semibold bg-white/20 px-2 py-1 rounded-full">Unique</span>
          </div>
          <p class="text-xs uppercase font-semibold opacity-90 mb-1">Employees Present</p>
          <p class="text-3xl font-bold">{{ stats.unique_employees }}</p>
        </div>

        <div class="bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl p-5 text-white shadow-lg shadow-blue-500/30">
          <div class="flex items-center justify-between mb-3">
            <i class="bi bi-graph-up text-3xl opacity-90"></i>
            <span class="text-xs font-semibold bg-white/20 px-2 py-1 rounded-full">Avg</span>
          </div>
          <p class="text-xs uppercase font-semibold opacity-90 mb-1">Avg Punches / Day</p>
          <p class="text-3xl font-bold">{{ stats.avg_punches_per_day }}</p>
        </div>
      </div>

      <!-- Top Employees -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
            <i class="bi bi-trophy-fill text-amber-500 text-lg"></i>
          </div>
          <div>
            <h3 class="font-bold text-gray-800 dark:text-gray-100">Top Attendance</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Employees with the most days present this month</p>
          </div>
        </div>
        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
          <div
            v-for="(emp, i) in employees"
            :key="emp.employee_id"
            class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-700/30"
          >
            <div
              class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold shrink-0"
              :class="i === 0 ? 'bg-amber-500' : i === 1 ? 'bg-gray-400' : i === 2 ? 'bg-amber-700' : 'bg-indigo-500'"
            >
              {{ i + 1 }}
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-xs font-semibold text-gray-800 dark:text-gray-100 truncate">
                {{ emp.employee_name }}
              </p>
              <p class="text-[10px] text-gray-500 dark:text-gray-400">
                {{ emp.days_present }} day{{ emp.days_present === 1 ? "" : "s" }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center">
              <i class="bi bi-calendar-check text-indigo-600 dark:text-indigo-400"></i>
            </div>
            <div>
              <h3 class="font-bold text-gray-800 dark:text-gray-100">Attendance Records</h3>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                Total: <span class="font-semibold text-gray-700 dark:text-gray-200">{{ records.total || 0 }}</span> daily records
              </p>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 uppercase text-xs">
              <tr>
                <th class="px-6 py-3 text-left font-semibold">Employee</th>
                <th class="px-6 py-3 text-left font-semibold">Date</th>
                <th class="px-6 py-3 text-left font-semibold">Check In</th>
                <th class="px-6 py-3 text-left font-semibold">Check Out</th>
                <th class="px-6 py-3 text-left font-semibold">Hours</th>
                <th class="px-6 py-3 text-left font-semibold">Punches</th>
                <th class="px-6 py-3 text-left font-semibold">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-if="records.data.length === 0">
                <td colspan="7" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center gap-3">
                    <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                      <i class="bi bi-inbox text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="font-semibold text-gray-700 dark:text-gray-200">No attendance records</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      No records found for {{ monthLabel }}
                    </p>
                  </div>
                </td>
              </tr>

              <tr
                v-for="row in records.data"
                :key="row.id"
                class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition"
              >
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <img
                      :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(row.employee_name || 'N/A')}&background=6366f1&color=fff&bold=true`"
                      class="w-9 h-9 rounded-full ring-2 ring-indigo-100 dark:ring-indigo-900/40"
                    />
                    <div class="min-w-0">
                      <p class="font-semibold text-gray-800 dark:text-gray-100 truncate">
                        {{ row.employee_name }}
                      </p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ row.employee_id || "-" }}
                      </p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                  {{ row.date }}
                </td>
                <td class="px-6 py-4">
                  <span v-if="row.check_in" class="text-green-700 dark:text-green-400 font-medium">
                    {{ row.check_in }}
                  </span>
                  <span v-else class="text-gray-400">-</span>
                </td>
                <td class="px-6 py-4">
                  <span v-if="row.check_out" class="text-red-700 dark:text-red-400 font-medium">
                    {{ row.check_out }}
                  </span>
                  <span v-else class="text-gray-400">-</span>
                </td>
                <td class="px-6 py-4 font-semibold text-gray-800 dark:text-gray-100">
                  {{ row.total_hours || "—" }}
                </td>
                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                  {{ row.punch_count }}
                </td>
                <td class="px-6 py-4">
                  <span
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold"
                    :class="getStatusClass(row.status)"
                  >
                    <i :class="getStatusIcon(row.status)" class="text-[10px]"></i>
                    {{ formatStatus(row.status) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div
          v-if="records.links && records.links.length > 3"
          class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between flex-wrap gap-3"
        >
          <div class="text-sm text-gray-500 dark:text-gray-400">
            Showing {{ records.from }} to {{ records.to }} of {{ records.total }} records
          </div>
          <div class="flex items-center gap-1 flex-wrap">
            <template v-for="(link, index) in records.links" :key="index">
              <Link
                v-if="link.url"
                :href="link.url"
                v-html="link.label"
                preserve-scroll
                preserve-state
                class="px-3 py-1.5 text-sm rounded-lg transition"
                :class="
                  link.active
                    ? 'bg-indigo-600 text-white font-semibold pointer-events-none'
                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                "
              />
              <span
                v-else
                v-html="link.label"
                class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600"
              />
            </template>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

/* ========== Props ========== */
const props = defineProps({
  records: {
    type: Object,
    default: () => ({ data: [], links: [], total: 0, from: 0, to: 0 }),
  },
  stats: {
    type: Object,
    default: () => ({
      working_days: 0,
      total_punches: 0,
      unique_employees: 0,
      avg_punches_per_day: 0,
    }),
  },
  employees: { type: Array, default: () => [] },
  departments: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
});

/* ========== Local state mirrored from server ========== */
const filters = ref({
  month: props.filters?.month ?? new Date().getMonth() + 1,
  year: props.filters?.year ?? new Date().getFullYear(),
  per_page: props.filters?.per_page ?? 25,
  search: props.filters?.search ?? "",
  department_id: props.filters?.department_id ?? "",
});

const monthNames = [
  "January", "February", "March", "April", "May", "June",
  "July", "August", "September", "October", "November", "December",
];

const yearOptions = computed(() => {
  const current = new Date().getFullYear();
  return Array.from({ length: 6 }, (_, i) => current - 3 + i);
});

const monthLabel = computed(
  () => `${monthNames[filters.value.month - 1]} ${filters.value.year}`
);

const hasFilters = computed(
  () => !!filters.value.search || !!filters.value.department_id
);

/* ========== Navigate ========== */
const navigate = () => {
  router.get(
    route("admin.attendance.monthly"),
    {
      month: filters.value.month,
      year: filters.value.year,
      per_page: filters.value.per_page,
      search: filters.value.search || undefined,
      department_id: filters.value.department_id || undefined,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      only: ["records", "stats", "employees", "filters"],
    }
  );
};

let searchTimeout = null;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(navigate, 400);
};

const clearFilters = () => {
  filters.value.search = "";
  filters.value.department_id = "";
  navigate();
};

/* ========== Helpers ========== */
const formatStatus = (status) => {
  const map = {
    check_in: "Check In",
    check_out: "Check Out",
    break_in: "Break In",
    break_out: "Break Out",
    overtime_in: "OT In",
    overtime_out: "OT Out",
    present: "Present",
    absent: "Absent",
    late: "Late",
    leave: "Leave",
  };
  return map[status] || status || "—";
};

const getStatusClass = (status) => {
  const map = {
    check_in: "bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300",
    check_out: "bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300",
    present: "bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300",
    late: "bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300",
    break_in: "bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300",
    break_out: "bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300",
    overtime_in: "bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300",
    overtime_out: "bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300",
  };
  return map[status] || "bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300";
};

const getStatusIcon = (status) => {
  const map = {
    check_in: "bi bi-box-arrow-in-right",
    check_out: "bi bi-box-arrow-right",
    break_in: "bi bi-cup-hot",
    break_out: "bi bi-cup-hot-fill",
    overtime_in: "bi bi-clock-history",
    overtime_out: "bi bi-clock-fill",
    present: "bi bi-check-circle-fill",
    late: "bi bi-clock-history",
  };
  return map[status] || "bi bi-circle";
};

/* ========== Sync filters from server on navigation ========== */
watch(
  () => props.filters,
  (next) => {
    if (!next) return;
    filters.value = {
      month: next.month ?? filters.value.month,
      year: next.year ?? filters.value.year,
      per_page: next.per_page ?? filters.value.per_page,
      search: next.search ?? "",
      department_id: next.department_id ?? "",
    };
  },
  { deep: true }
);
</script>
