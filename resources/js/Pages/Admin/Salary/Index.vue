<template>
  <AdminLayout page-title="Salary Structure">
    <Head title="Salary Structure" />

    <div class="space-y-6">
      <!-- HEADER -->
      <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
            Salary Structure
          </h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Bangladesh Labor Act 2025 · Basic + 50% House Rent + Allowances
          </p>
        </div>
        <div class="flex items-center gap-2">
          <Link
            :href="route('admin.salary.create')"
            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 shadow-lg"
          >
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
            New Structure
          </Link>
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

      <!-- STATS -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700">
          <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-medium">Total Structures</p>
          <p class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ stats.total }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700">
          <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-medium">Avg Gross</p>
          <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ fmt(stats.avg_gross) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700">
          <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-medium">Avg Total Gross</p>
          <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ fmt(stats.avg_net_gross) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700">
          <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-medium">Total Bonus</p>
          <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ fmt(stats.total_bonus) }}</p>
        </div>
      </div>

      <!-- TABLE -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between flex-wrap gap-3">
          <h3 class="font-bold text-gray-800 dark:text-gray-100">All Structures</h3>
          <div class="relative">
            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm" aria-hidden="true"></i>
            <input
              v-model="search"
              @input="debouncedSearch"
              type="text"
              placeholder="Search employee..."
              class="pl-9 pr-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none w-56"
            />
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 uppercase text-[10px]">
              <tr>
                <th class="px-4 py-3 text-left font-semibold">Months / Employee</th>
                <th class="px-4 py-3 text-right font-semibold">Gross Salary</th>
                <th class="px-4 py-3 text-right font-semibold">
                  Basic Salary
                  <span class="block text-[9px] font-normal normal-case opacity-70">(Gross − (Med+Conv+Food)) / 1.5</span>
                </th>
                <th class="px-4 py-3 text-right font-semibold">
                  House Rent
                  <span class="block text-[9px] font-normal normal-case opacity-70">50% of Basic</span>
                </th>
                <th class="px-4 py-3 text-right font-semibold">
                  Medical
                  <span class="block text-[9px] font-normal normal-case opacity-70">Gadged 2025 (৳750)</span>
                </th>
                <th class="px-4 py-3 text-right font-semibold">
                  Conveyance
                  <span class="block text-[9px] font-normal normal-case opacity-70">Gadged 2025 (৳450)</span>
                </th>
                <th class="px-4 py-3 text-right font-semibold">
                  Food
                  <span class="block text-[9px] font-normal normal-case opacity-70">Gadged 2025 (৳1250)</span>
                </th>
                <th class="px-4 py-3 text-right font-semibold">Total Gross</th>
                <th class="px-4 py-3 text-right font-semibold">Bonus</th>
                <th class="px-4 py-3 text-right font-semibold">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-if="!structures.data.length">
                <td colspan="10" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center gap-3">
                    <i class="bi bi-cash-stack text-4xl text-gray-300" aria-hidden="true"></i>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No salary structures yet</p>
                    <Link :href="route('admin.salary.create')"
                      class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                      <i class="bi bi-plus-lg" aria-hidden="true"></i> Create first
                    </Link>
                  </div>
                </td>
              </tr>

              <tr v-for="row in structures.data" :key="row.id"
                class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition">
                <td class="px-4 py-3">
                  <p class="font-semibold text-gray-800 dark:text-gray-100">
                    {{ row.name || employeeName(row) || '—' }}
                  </p>
                  <p v-if="row.employee" class="text-xs text-gray-500 dark:text-gray-400">
                    {{ row.employee.employee_id }}
                  </p>
                </td>
                <td class="px-4 py-3 text-right font-mono text-gray-800 dark:text-gray-100">{{ fmt(row.gross_salary) }}</td>
                <td class="px-4 py-3 text-right font-mono font-semibold text-indigo-600 dark:text-indigo-400">{{ fmt(row.basic_salary) }}</td>
                <td class="px-4 py-3 text-right font-mono text-emerald-600 dark:text-emerald-400">{{ fmt(row.house_rent) }}</td>
                <td class="px-4 py-3 text-right font-mono">{{ fmt(row.medical_allowance) }}</td>
                <td class="px-4 py-3 text-right font-mono">{{ fmt(row.conveyance) }}</td>
                <td class="px-4 py-3 text-right font-mono">{{ fmt(row.food_allowance) }}</td>
                <td class="px-4 py-3 text-right font-mono font-bold text-gray-800 dark:text-gray-100">{{ fmt(row.total_gross_salary) }}</td>
                <td class="px-4 py-3 text-right font-mono text-amber-600 dark:text-amber-400">
                  {{ fmt(row.bonus) }}
                  <span v-if="row.bonus_note" class="block text-[9px] text-gray-500 dark:text-gray-400 normal-case">{{ row.bonus_note }}</span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-end gap-1">
                    <Link :href="route('admin.salary.edit', row.id)"
                      class="w-8 h-8 flex items-center justify-center text-gray-500 hover:bg-amber-50 dark:hover:bg-amber-900/30 hover:text-amber-600 rounded-lg transition"
                      title="Edit">
                      <i class="bi bi-pencil" aria-hidden="true"></i>
                    </Link>
                    <button @click="confirmDelete(row)"
                      class="w-8 h-8 flex items-center justify-center text-gray-500 hover:bg-red-50 dark:hover:bg-red-900/30 hover:text-red-600 rounded-lg transition"
                      title="Delete">
                      <i class="bi bi-trash" aria-hidden="true"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="structures.links?.length > 3"
          class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between flex-wrap gap-3">
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Showing {{ structures.from }}–{{ structures.to }} of {{ structures.total }}
          </p>
          <nav class="flex items-center gap-1 flex-wrap">
            <template v-for="(l, i) in structures.links" :key="i">
              <Link v-if="l.url" :href="l.url" v-html="l.label"
                class="px-3 py-1.5 text-sm rounded-lg"
                :class="l.active ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'" />
              <span v-else v-html="l.label" class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600" />
            </template>
          </nav>
        </div>
      </div>
    </div>

    <!-- DELETE MODAL -->
    <div v-if="deleteTarget"
      class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
      @click.self="deleteTarget = null"
      role="dialog" aria-modal="true">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
        <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg mb-2">Delete Salary Structure?</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Delete <strong>{{ deleteTarget.name || employeeName(deleteTarget) }}</strong>?
        </p>
        <div class="flex justify-end gap-2 mt-6">
          <button @click="deleteTarget = null"
            class="px-4 py-2 text-sm font-medium bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50">
            Cancel
          </button>
          <button @click="doDelete" :disabled="deleting"
            class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 flex items-center gap-2">
            <i v-if="deleting" class="bi bi-arrow-repeat animate-spin" aria-hidden="true"></i>
            <i v-else class="bi bi-trash" aria-hidden="true"></i>
            Delete
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  structures: { type: Object, default: () => ({ data: [], links: [] }) },
  stats:      { type: Object, default: () => ({}) },
  filters:    { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');
let searchTimer = null;
const debouncedSearch = () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    router.get(route('admin.salary.index'), { search: search.value },
      { preserveState: true, preserveScroll: true, replace: true });
  }, 350);
};

/* Delete */
const deleteTarget = ref(null);
const deleting = ref(false);
const confirmDelete = (row) => { deleteTarget.value = row; };
const doDelete = () => {
  if (!deleteTarget.value) return;
  deleting.value = true;
  router.delete(route('admin.salary.destroy', deleteTarget.value.id), {
    preserveScroll: true,
    onSuccess: () => { deleteTarget.value = null; },
    onFinish:  () => { deleting.value = false; },
  });
};

/* Helpers */
const fmt = (v) => new Intl.NumberFormat('en-BD', {
  style: 'currency', currency: 'BDT', maximumFractionDigits: 0,
}).format(Number(v) || 0);

const employeeName = (row) => {
  if (!row?.employee) return '';
  return [row.employee.first_name, row.employee.last_name].filter(Boolean).join(' ');
};
</script>
