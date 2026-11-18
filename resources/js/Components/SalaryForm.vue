<template>
  <form @submit.prevent="submit" class="space-y-5">
    <!-- Employee / name -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300 mb-1">
          Employee (optional)
        </label>
        <select v-model="form.employee_id"
          class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
          <option :value="null">— No specific employee (template) —</option>
          <option v-for="e in employees" :key="e.id" :value="e.id">
            {{ e.employee_id }} — {{ e.first_name }} {{ e.last_name }}
          </option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300 mb-1">
          Structure name (optional)
        </label>
        <input v-model="form.name" type="text" placeholder="e.g. Grade-1 default"
          class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
      </div>
    </div>

    <!-- Gross salary -->
    <div>
      <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300 mb-1">
        Gross Salary (৳)
      </label>
      <input v-model.number="form.gross_salary" type="number" step="0.01" min="0" required
        class="w-full px-3 py-2.5 text-lg font-bold bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
    </div>

    <!-- Allowances -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div>
        <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300 mb-1">
          Medical (৳)
        </label>
        <input v-model.number="form.medical_allowance" type="number" step="0.01" min="0"
          class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300 mb-1">
          Conveyance (৳)
        </label>
        <input v-model.number="form.conveyance" type="number" step="0.01" min="0"
          class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300 mb-1">
          Food Allowance (৳)
        </label>
        <input v-model.number="form.food_allowance" type="number" step="0.01" min="0"
          class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
      </div>
    </div>

    <!-- Live computed preview -->
    <div class="rounded-xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/20 p-4">
      <p class="text-xs font-semibold uppercase text-indigo-700 dark:text-indigo-300 mb-3">
        Live Preview (auto-computed)
      </p>
      <dl class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
        <div>
          <dt class="text-indigo-600 dark:text-indigo-400 text-xs">Basic Salary</dt>
          <dd class="font-mono font-bold text-indigo-900 dark:text-indigo-100">
            {{ fmt(preview.basic_salary) }}
          </dd>
        </div>
        <div>
          <dt class="text-indigo-600 dark:text-indigo-400 text-xs">House Rent (50%)</dt>
          <dd class="font-mono font-bold text-indigo-900 dark:text-indigo-100">
            {{ fmt(preview.house_rent) }}
          </dd>
        </div>
        <div>
          <dt class="text-indigo-600 dark:text-indigo-400 text-xs">Total Gross</dt>
          <dd class="font-mono font-bold text-emerald-700 dark:text-emerald-300">
            {{ fmt(preview.total_gross_salary) }}
          </dd>
        </div>
      </dl>
    </div>

    <!-- Bonus -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div>
        <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300 mb-1">
          Bonus (৳)
        </label>
        <input v-model.number="form.bonus" type="number" step="0.01" min="0"
          class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
      </div>
      <div class="sm:col-span-2">
        <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300 mb-1">
          Bonus note
        </label>
        <!-- ⚠️ NOT v-model.number — this is a text field! -->
        <input v-model="form.bonus_note" type="text" placeholder="e.g. Festival bonus 2025"
          class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
      </div>
    </div>

    <!-- Submit -->
    <div class="flex justify-end gap-2 pt-2">
      <Link :href="route('admin.salary.index')"
        class="px-4 py-2 text-sm font-medium bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50">
        Cancel
      </Link>
      <button type="submit" :disabled="form.processing"
        class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50 flex items-center gap-2">
        <i v-if="form.processing" class="bi bi-arrow-repeat animate-spin" aria-hidden="true"></i>
        {{ submitLabel }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, watch } from "vue";
import { Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
  employees:   { type: Array,  default: () => [] },
  structure:   { type: Object, default: null },
  submitLabel: { type: String, default: "Save" },
  action:      { type: String, required: true },
  method:      { type: String, default: "post" },
});

const form = useForm({
  employee_id:       props.structure?.employee_id ?? null,
  name:              props.structure?.name ?? "",
  gross_salary:      props.structure?.gross_salary ?? 0,
  medical_allowance: props.structure?.medical_allowance ?? 750,
  conveyance:        props.structure?.conveyance ?? 450,
  food_allowance:    props.structure?.food_allowance ?? 1250,
  bonus:             props.structure?.bonus ?? 0,
  bonus_note:        props.structure?.bonus_note ?? "",
});

/* ============================================================
 |  Live preview
 * ============================================================ */

// 1. Create a reactive object to hold the computed values
const preview = ref({
  basic_salary:       0,
  house_rent:         0,
  total_gross_salary: 0,
});

// 2. Function that performs the math
const recalculate = () => {
  const gross   = Number(form.gross_salary) || 0;
  const medical = Number(form.medical_allowance) || 0;
  const conv    = Number(form.conveyance) || 0;
  const food    = Number(form.food_allowance) || 0;

  const allowanceSum = medical + conv + food;
  const basic        = Math.max(0, (gross - allowanceSum) / 1.5);
  const house        = basic * 0.5;
  const totalGross   = basic + house + medical + conv + food;

  preview.value = {
    basic_salary:       basic,
    house_rent:         house,
    total_gross_salary: totalGross,
  };
};

// 3. Run it once on mount (so Edit page pre-fills correctly)
recalculate();

// 4. Watch the form fields and re-run whenever any of them change
//    Note: Inertia's useForm is a Proxy — watching form.* works in Vue 3
watch(
  () => [
    form.gross_salary,
    form.medical_allowance,
    form.conveyance,
    form.food_allowance,
  ],
  recalculate
);

/* ============================================================
 |  Helpers
 * ============================================================ */
const fmt = (v) =>
  new Intl.NumberFormat("en-BD", {
    style: "currency",
    currency: "BDT",
    maximumFractionDigits: 0,
  }).format(Number(v) || 0);

/* ============================================================
 |  Submit
 * ============================================================ */
const submit = () => {
  form[props.method](props.action, { preserveScroll: true });
};
</script>
