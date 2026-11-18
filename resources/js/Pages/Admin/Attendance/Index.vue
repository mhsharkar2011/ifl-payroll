<template>
  <AdminLayout page-title="Attendance">
    <Head title="Attendance" />

    <div class="space-y-6">
      <!-- API Error banner -->
      <div
        v-if="apiError"
        class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 flex items-start gap-3"
      >
        <i class="bi bi-exclamation-triangle-fill text-red-500 text-xl shrink-0"></i>
        <div class="flex-1 text-sm text-red-700 dark:text-red-300">
          <p class="font-semibold">Could not load attendance data</p>
          <p class="mt-1 font-mono text-xs break-all">{{ apiError }}</p>
        </div>
        <button
          @click="refreshData"
          class="px-3 py-1.5 text-xs font-semibold text-red-700 dark:text-red-300 bg-white dark:bg-gray-800 border border-red-300 dark:border-red-700 rounded-lg hover:bg-red-100 transition"
        >
          Retry
        </button>
      </div>

      <!-- Jump to latest hint -->
      <div
        v-if="!loading && !rows.length && latestDate && latestDate !== filters.date"
        class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4 flex items-center gap-3"
      >
        <i class="bi bi-info-circle-fill text-amber-500 text-xl shrink-0"></i>
        <div class="flex-1 text-sm text-amber-800 dark:text-amber-300">
          No records for <strong>{{ filters.date }}</strong>. The latest date with records is
          <button
            @click="jumpToDate(latestDate)"
            class="font-semibold underline hover:no-underline"
          >
            {{ latestDate }}
          </button>.
        </div>
        <button
          @click="jumpToDate(latestDate)"
          class="px-3 py-1.5 text-xs font-semibold text-amber-800 dark:text-amber-200 bg-white dark:bg-gray-800 border border-amber-300 dark:border-amber-700 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/40 transition"
        >
          Show latest
        </button>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div
          v-for="card in summaryCards"
          :key="card.key"
          class="group relative overflow-hidden bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300"
        >
          <div
            class="absolute top-0 right-0 w-24 h-24 rounded-full -mr-8 -mt-8 group-hover:scale-125 transition-transform"
            :class="card.blobClass"
          ></div>
          <div class="relative flex items-center justify-between mb-3">
            <div
              class="w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg"
              :class="card.iconBgClass"
            >
              <i :class="[card.icon, 'text-xl']"></i>
            </div>
            <span
              class="text-xs font-semibold px-2.5 py-1 rounded-full"
              :class="card.badgeClass"
            >
              {{ isToday ? "Today" : shortDate(filters.date) }}
            </span>
          </div>
          <p class="relative text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold tracking-wider mb-1">
            {{ card.label }}
          </p>
          <p class="relative text-3xl font-bold text-gray-800 dark:text-gray-100">
            {{ stats?.[card.key] ?? 0 }}
          </p>
        </div>
      </div>

      <!-- Filter Bar -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
          <div class="relative md:col-span-2">
            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input
              v-model="filters.search"
              @input="debouncedSearch"
              type="text"
              placeholder="Search employee..."
              class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none transition"
            />
          </div>

          <div>
            <input
              v-model="filters.date"
              @change="navigate"
              type="date"
              class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none transition"
            />
          </div>

          <select
            v-model="filters.department_id"
            @change="navigate"
            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none transition"
          >
            <option value="">All Departments</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>

          <!-- Manual Entry Button -->
          <button
            @click="openManualModal"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition shadow-lg shadow-emerald-500/30"
          >
            <i class="bi bi-plus-lg"></i>
            Manual Entry
          </button>

          <button
            @click="syncZKTeco"
            :disabled="syncing"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition shadow-lg shadow-indigo-500/30 disabled:opacity-50"
          >
            <i v-if="syncing" class="bi bi-arrow-repeat animate-spin"></i>
            <i v-else class="bi bi-arrow-repeat"></i>
            {{ syncing ? "Syncing..." : "Sync ZKTeco" }}
          </button>
        </div>

        <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between flex-wrap gap-3">
          <div class="flex items-center gap-3 flex-wrap">
            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
              <span>Per page:</span>
              <select
                v-model="perPage"
                @change="navigate"
                class="px-2 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md text-xs"
              >
                <option :value="10">10</option>
                <option :value="15">15</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
                <option :value="100">100</option>
              </select>
            </div>
            <div v-if="hasFilters" class="flex items-center gap-2 flex-wrap">
              <span class="text-xs text-gray-500 dark:text-gray-400">Active filters:</span>
              <button
                @click="clearFilters"
                class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium flex items-center gap-1"
              >
                <i class="bi bi-x-circle"></i> Clear all
              </button>
            </div>
          </div>

          <div class="flex items-center gap-3 flex-wrap">
            <Link
              :href="route('admin.attendance.monthly', { month: new Date().getMonth() + 1, year: new Date().getFullYear() })"
              class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition"
            >
              <i class="bi bi-calendar-month"></i> Monthly View
            </Link>
          </div>

          <div class="flex items-center gap-2">
            <Link
              :href="route('admin.attendance.bulk-upload')"
              class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition"
            >
              <i class="bi bi-cloud-upload"></i> Bulk Upload
            </Link>
            <Link
              :href="route('admin.attendance.today')"
              class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition"
            >
              <i class="bi bi-calendar-day"></i> Today
            </Link>
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
                Total: <span class="font-semibold text-gray-700 dark:text-gray-200">{{ pagination.total || 0 }}</span> records
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
                <th class="px-6 py-3 text-left font-semibold">Status</th>
                <th class="px-6 py-3 text-left font-semibold">Notes</th>
                <th class="px-6 py-3 text-right font-semibold">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-if="loading">
                <td colspan="8" class="px-6 py-12 text-center">
                  <div class="inline-block w-8 h-8 border-3 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                </td>
              </tr>

              <template v-else-if="rows.length">
                <tr v-for="row in rows" :key="row.id" class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">

                      <!-- ✅ FIXED: Use the real avatar with fallback -->
                      <EmployeeAvatar :employee="{
                        first_name: row.employee_name,
                        last_name: '',
                        avatar_url: row.employee_avatar
                      }" size="md" />

                      <div class="min-w-0">
                        <p class="font-semibold text-gray-800 dark:text-gray-100 truncate">
                          {{ row.employee_name || "N/A" }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                          {{ row.employee_code || "-" }}
                        </p>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                    {{ row.date || "-" }}
                  </td>

                  <!-- In Time -->
                  <td class="px-6 py-4">
                    <span v-if="row.in_time" class="inline-flex items-center gap-1 text-green-700 dark:text-green-400 font-medium">
                      <i class="bi bi-box-arrow-in-right"></i>
                      {{ row.in_time }}
                    </span>
                    <span v-else class="inline-flex items-center gap-1 text-red-500 text-[11px] font-semibold bg-red-50 dark:bg-red-900/30 px-2 py-1 rounded">
                      <i class="bi bi-exclamation-circle"></i> Missing
                    </span>
                  </td>

                  <!-- Out Time -->
                  <td class="px-6 py-4">
                    <span v-if="row.out_time" class="inline-flex items-center gap-1 text-amber-700 dark:text-amber-400 font-medium">
                      <i class="bi bi-box-arrow-right"></i>
                      {{ row.out_time }}
                    </span>
                    <span v-else class="inline-flex items-center gap-1 text-red-500 text-[11px] font-semibold bg-red-50 dark:bg-red-900/30 px-2 py-1 rounded">
                      <i class="bi bi-exclamation-circle"></i> Missing
                    </span>
                  </td>

                  <td class="px-6 py-4">
                    <div class="flex flex-col">
                      <span class="font-semibold text-gray-800 dark:text-gray-100">
                        {{ row.total_hours || "—" }}
                      </span>
                      <span v-if="row.punch_count" class="text-[10px] text-gray-400">
                        {{ row.punch_count }} punch{{ row.punch_count === 1 ? "" : "es" }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold" :class="getStatusClass(row.status)">
                      <i :class="getStatusIcon(row.status)" class="text-[10px]"></i>
                      {{ formatStatus(row.status) }}
                    </span>
                  </td>

                  <td class="px-6 py-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[150px]" :title="row.notes">
                      {{ row.notes || "-" }}
                    </p>
                  </td>

                  <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-1">
                      <Link :href="route('admin.attendance.show', row.id)" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 transition" title="View">
                        <i class="bi bi-eye"></i>
                      </Link>
                      <button @click="openEditModal(row)" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-amber-50 dark:hover:bg-amber-900/30 hover:text-amber-600 transition" title="Edit Manual">
                        <i class="bi bi-pencil"></i>
                      </button>
                      <button @click="confirmDelete(row)" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-red-50 dark:hover:bg-red-900/30 hover:text-red-600 transition" title="Delete">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </template>

              <tr v-else>
                <td colspan="8" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center gap-3">
                    <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                      <i class="bi bi-inbox text-3xl text-gray-400"></i>
                    </div>
                    <div>
                      <h3 class="font-semibold text-gray-700 dark:text-gray-200">No attendance records</h3>
                      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ emptyStateMessage }}</p>
                    </div>
                    <div class="flex gap-2">
                      <button v-if="!hasFilters" @click="syncZKTeco" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">
                        <i class="bi bi-arrow-repeat"></i> Sync Now
                      </button>
                      <Link v-if="!hasFilters" :href="route('admin.attendance.bulk-upload')" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition">
                        <i class="bi bi-cloud-upload"></i> Bulk Upload
                      </Link>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Inertia pagination -->
        <div
          v-if="attendances.links && attendances.links.length > 3"
          class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between flex-wrap gap-3"
        >
          <div class="text-sm text-gray-500 dark:text-gray-400">
            Showing
            <span class="font-semibold text-gray-700 dark:text-gray-200">{{ attendances.from }}</span>
            to
            <span class="font-semibold text-gray-700 dark:text-gray-200">{{ attendances.to }}</span>
            of
            <span class="font-semibold text-gray-700 dark:text-gray-200">{{ attendances.total }}</span>
            results
          </div>

          <div class="flex items-center gap-1 flex-wrap">
            <template v-for="(link, index) in attendances.links" :key="index">
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

    <!-- Delete Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
      @click.self="showDeleteModal = false"
    >
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
            <i class="bi bi-exclamation-triangle-fill text-red-600 dark:text-red-400 text-xl"></i>
          </div>
          <div class="flex-1">
            <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg">Delete Attendance Record?</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              Record for <strong>{{ selectedAttendance?.employee_name }}</strong> on
              <strong>{{ selectedAttendance?.date }}</strong>. This action cannot be undone.
            </p>
          </div>
        </div>
        <div class="flex justify-end gap-2 mt-6">
          <button @click="showDeleteModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
            Cancel
          </button>
          <button @click="deleteAttendance" :disabled="deleting" class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition disabled:opacity-50 flex items-center gap-2">
            <i v-if="deleting" class="bi bi-arrow-repeat animate-spin"></i>
            <i v-else class="bi bi-trash"></i>
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Manual Entry Modal -->
    <ManualAttendanceModal
      :show="showManualModal"
      :employees="modalEmployees"
      :attendance="selectedManualAttendance"
      @close="showManualModal = false"
    />
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import ManualAttendanceModal from "@/Components/ManualAttendanceModal.vue";
import EmployeeAvatar from "@/Components/EmployeeAvatar.vue"; // ✅ Import the new component
import axios from "axios";

/* ========== Props ========== */
const props = defineProps({
  attendances: {
    type: Object,
    default: () => ({ data: [], links: [], total: 0, current_page: 1, last_page: 1, from: 0, to: 0, per_page: 15 }),
  },
  stats: { type: Object, default: () => ({ present: 0, absent: 0, on_leave: 0, late: 0 }) },
  departments: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  latestDate: { type: String, default: "" },
  employees: { type: Array, default: () => [] },
});

/* ========== Local-date helper ========== */
const LOCAL_TODAY = (() => {
  const d = new Date();
  d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
  return d.toISOString().split("T")[0];
})();

/* ========== latestDate ========== */
const latestDate = ref(props.latestDate || "");

/* ========== Rows from paginator ========== */
const rows = computed(() => props.attendances?.data || []);

/* ========== Pagination mirrors paginator ========== */
const pagination = computed(() => ({
  current_page: props.attendances?.current_page ?? 1,
  last_page: props.attendances?.last_page ?? 1,
  per_page: props.attendances?.per_page ?? 15,
  total: props.attendances?.total ?? 0,
  from: props.attendances?.from ?? 0,
  to: props.attendances?.to ?? 0,
}));

/* ========== Per-page (mirrors server) ========== */
const perPage = ref(props.filters?.per_page ?? props.attendances?.per_page ?? 15);

/* ========== Filters ========== */
const filters = ref({
  search: props.filters?.search || "",
  date: props.filters?.date || latestDate.value || LOCAL_TODAY,
  department_id: props.filters?.department_id || "",
});

/* ========== UI state ========== */
const loading = ref(false);
const syncing = ref(false);
const deleting = ref(false);
const showDeleteModal = ref(false);
const selectedAttendance = ref(null);
const apiError = ref(null);

/* ========== Manual Modal State ========== */
const showManualModal = ref(false);
const selectedManualAttendance = ref(null);

// ✅ FIX: Ensure the selected employee exists in the dropdown
const modalEmployees = computed(() => {
  let list = [...props.employees];
  const att = selectedManualAttendance.value;

  if (att && att.employee_id) {
    // Check if employee is missing from the list (e.g. inactive or paginated out)
    const exists = list.some(e => String(e.id) === String(att.employee_id));
    if (!exists) {
      list.push({
        id: att.employee_id,
        employee_id: att.employee_code || 'N/A', // Fallback
        first_name: att.employee_name || 'Unknown',
        last_name: ''
      });
    }
  }
  return list;
});

const openManualModal = () => {
  selectedManualAttendance.value = null; // Clear to trigger "Create" mode
  showManualModal.value = true;
};

const openEditModal = (row) => {
  // Pass the row data to the modal to pre-fill in/out times
  selectedManualAttendance.value = row;
  showManualModal.value = true;
};

/* ========== Summary card config ========== */
const summaryCards = [
  {
    key: "present",
    label: "Present",
    icon: "bi bi-check-circle-fill",
    iconBgClass: "bg-gradient-to-br from-green-500 to-emerald-600 shadow-green-500/30",
    blobClass: "bg-gradient-to-br from-green-500/10 to-emerald-500/0",
    badgeClass: "text-green-700 dark:text-green-300 bg-green-50 dark:bg-green-900/30",
  },
  {
    key: "absent",
    label: "Absent",
    icon: "bi bi-x-circle-fill",
    iconBgClass: "bg-gradient-to-br from-red-500 to-rose-600 shadow-red-500/30",
    blobClass: "bg-gradient-to-br from-red-500/10 to-rose-500/0",
    badgeClass: "text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/30",
  },
  {
    key: "on_leave",
    label: "On Leave",
    icon: "bi bi-calendar-x-fill",
    iconBgClass: "bg-gradient-to-br from-amber-500 to-orange-500 shadow-amber-500/30",
    blobClass: "bg-gradient-to-br from-amber-500/10 to-orange-500/0",
    badgeClass: "text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/30",
  },
  {
    key: "late",
    label: "Late Entry",
    icon: "bi bi-clock-history",
    iconBgClass: "bg-gradient-to-br from-indigo-500 to-purple-600 shadow-indigo-500/30",
    blobClass: "bg-gradient-to-br from-indigo-500/10 to-purple-500/0",
    badgeClass: "text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/30",
  },
];

/* ========== Computed ========== */
const isToday = computed(() => filters.value.date === LOCAL_TODAY);

const hasFilters = computed(
  () =>
    !!filters.value.search ||
    !!filters.value.department_id ||
    (filters.value.date && filters.value.date !== LOCAL_TODAY)
);

const emptyStateMessage = computed(() => {
  if (apiError.value) return "The server could not return attendance records.";
  if (hasFilters.value) return "Try adjusting your filters.";
  return "Sync from ZKTeco or bulk upload to fetch records.";
});

/* ========== Navigate (Inertia) ========== */
const navigate = () => {
  loading.value = true;

  router.get(
    route("admin.attendance.index"),
    {
      search: filters.value.search || undefined,
      date: filters.value.date || undefined,
      department_id: filters.value.department_id || undefined,
      per_page: perPage.value,
    },
    {
      preserveState: true,
      preserveScroll: true,
      only: ["attendances", "stats", "departments", "filters", "latestDate"],
      onError: (errors) => {
        apiError.value = errors?.message || "Failed to load attendance";
      },
      onFinish: () => {
        loading.value = false;
      },
    }
  );
};

/* ========== Refresh (same filters, same page) ========== */
const refreshData = () => {
  loading.value = true;
  router.reload({
    only: ["attendances", "stats", "latestDate"],
    preserveScroll: true,
    preserveState: true,
    onFinish: () => {
      loading.value = false;
    },
  });
};

/* ========== Filter handlers ========== */
let searchTimeout = null;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(navigate, 400);
};

const clearFilters = () => {
  filters.value = {
    search: "",
    date: latestDate.value || LOCAL_TODAY,
    department_id: "",
  };
  navigate();
};

const jumpToDate = (date) => {
  if (!date) return;
  filters.value.date = date;
  navigate();
};

/* ========== Sync ========== */
const syncZKTeco = async () => {
  if (syncing.value) return;
  syncing.value = true;
  apiError.value = null;
  try {
    const { data } = await axios.post(route("admin.attendance.sync"));
    refreshData();
    alert(`✅ Attendance synced successfully! (${data?.synced ?? 0} records)`);
  } catch (error) {
    console.error("Sync error:", error);
    alert("❌ Sync failed: " + (error.response?.data?.message || error.message));
  } finally {
    syncing.value = false;
  }
};

/* ========== Delete ========== */
const confirmDelete = (row) => {
  selectedAttendance.value = row;
  showDeleteModal.value = true;
};

const deleteAttendance = () => {
  if (!selectedAttendance.value) return;
  deleting.value = true;
  router.post(
    route("admin.attendance.destroy", selectedAttendance.value.id),
    { _method: "DELETE" },
    {
      preserveScroll: true,
      onSuccess: () => {
        showDeleteModal.value = false;
        selectedAttendance.value = null;
        refreshData();
      },
      onError: (errors) => {
        console.error("Delete failed:", errors);
        alert("❌ Delete failed: " + (errors?.message || "Unknown error"));
      },
      onFinish: () => {
        deleting.value = false;
      },
    }
  );
};

/* ========== Helpers ========== */
const formatStatus = (status) => {
  const map = {
    check_in: "Check In", check_out: "Check Out",
    break_in: "Break In", break_out: "Break Out",
    overtime_in: "OT In", overtime_out: "OT Out",
    present: "Present", absent: "Absent", late: "Late", leave: "Leave",
  };
  return map[status] || status || "—";
};

const shortDate = (iso) => {
  if (!iso) return "";
  const d = new Date(iso);
  if (isNaN(d)) return iso;
  return d.toLocaleDateString("en-GB", { day: "2-digit", month: "short" });
};

const getStatusClass = (status) => {
  const map = {
    check_in: "bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 ring-1 ring-green-200 dark:ring-green-800",
    check_out: "bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 ring-1 ring-red-200 dark:ring-red-800",
    break_in: "bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-800",
    break_out: "bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-800",
    overtime_in: "bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 ring-1 ring-indigo-200 dark:ring-indigo-800",
    overtime_out: "bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 ring-1 ring-indigo-200 dark:ring-indigo-800",
    present: "bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 ring-1 ring-green-200 dark:ring-green-800",
    absent: "bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 ring-1 ring-red-200 dark:ring-red-800",
    late: "bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-800",
    leave: "bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 ring-1 ring-blue-200 dark:ring-blue-800",
  };
  return map[status] || "bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 ring-1 ring-gray-200 dark:ring-gray-600";
};

const getStatusIcon = (status) => {
  const map = {
    check_in: "bi bi-box-arrow-in-right", check_out: "bi bi-box-arrow-right",
    break_in: "bi bi-cup-hot", break_out: "bi bi-cup-hot-fill",
    overtime_in: "bi bi-clock-history", overtime_out: "bi bi-clock-fill",
    present: "bi bi-check-circle-fill", absent: "bi bi-x-circle-fill",
    late: "bi bi-clock-history", leave: "bi bi-calendar-x-fill",
  };
  return map[status] || "bi bi-circle";
};

/* ========== Watch server-driven filters ========== */
watch(
  () => props.filters,
  (next) => {
    if (!next) return;
    filters.value = {
      search: next.search ?? "",
      date: next.date ?? (latestDate.value || LOCAL_TODAY),
      department_id: next.department_id ?? "",
    };
    if (next.per_page) {
      perPage.value = next.per_page;
    }
  },
  { deep: true }
);

watch(
  () => props.latestDate,
  (v) => {
    if (v) latestDate.value = v;
  }
);

/* ========== On mount ========== */
onMounted(() => {
  const isDefaultDate =
    !props.filters?.date || props.filters.date === LOCAL_TODAY;

  if (isDefaultDate && latestDate.value && latestDate.value !== filters.date) {
    jumpToDate(latestDate.value);
  }
});
</script>
