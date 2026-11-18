<template>
  <AdminLayout page-title="Edit Attendance">
    <Head
      :title="`Edit Attendance - ${attendance.employee?.first_name || ''} ${
        attendance.employee?.last_name || ''
      }`"
    />

    <div class="max-w-3xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link
          :href="route('admin.attendance.index')"
          class="w-10 h-10 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-700 transition"
        >
          <i class="bi bi-arrow-left text-gray-600 dark:text-gray-300"></i>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
            Edit Attendance
          </h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Updating record for
            <span class="font-semibold text-indigo-600 dark:text-indigo-400">
              {{ attendance.employee?.first_name }}
              {{ attendance.employee?.last_name }}
            </span>
          </p>
        </div>
      </div>

      <!-- Flash -->
      <div
        v-if="$page.props.flash?.success"
        class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 flex items-center gap-3"
      >
        <i class="bi bi-check-circle-fill text-green-500 text-xl"></i>
        <p class="text-sm text-green-700 dark:text-green-300 font-medium">
          {{ $page.props.flash.success }}
        </p>
      </div>

      <!-- Error summary -->
      <div
        v-if="Object.keys(form.errors).length > 0"
        class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4"
      >
        <div class="flex items-start gap-3">
          <i
            class="bi bi-exclamation-triangle-fill text-red-500 text-xl shrink-0"
          ></i>
          <div class="flex-1">
            <h4 class="font-semibold text-red-800 dark:text-red-300 mb-2">
              Please fix the following errors:
            </h4>
            <ul class="text-sm text-red-700 dark:text-red-300 space-y-1">
              <li
                v-for="(error, field) in form.errors"
                :key="field"
                class="flex items-start gap-2"
              >
                <i class="bi bi-dot"></i>
                <span
                  ><strong>{{ formatFieldName(field) }}:</strong>
                  {{ error }}</span
                >
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Form -->
      <form
        @submit.prevent="submit"
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden"
      >
        <div
          class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20"
        >
          <div class="flex items-center gap-3">
            <div
              class="w-10 h-10 rounded-lg bg-indigo-600 flex items-center justify-center text-white"
            >
              <i class="bi bi-calendar-check-fill"></i>
            </div>
            <div>
              <h3 class="font-bold text-gray-800 dark:text-gray-100">
                Attendance Details
              </h3>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                Update attendance record
              </p>
            </div>
          </div>
        </div>

        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
          <!-- Employee -->
          <div class="sm:col-span-2">
            <label
              class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2"
            >
              Employee <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.employee_id"
              class="w-full px-4 py-2.5 border rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none transition bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100"
              :class="
                form.errors.employee_id
                  ? 'border-red-400 bg-red-50 dark:bg-red-900/20'
                  : 'border-gray-300 dark:border-gray-600'
              "
            >
              <option value="">Select Employee</option>
              <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                {{ emp.employee_id }} — {{ emp.first_name }} {{ emp.last_name }}
              </option>
            </select>
            <p
              v-if="form.errors.employee_id"
              class="mt-1.5 text-xs text-red-600 dark:text-red-400"
            >
              {{ form.errors.employee_id }}
            </p>
          </div>

          <!-- Timestamp -->
          <div>
            <label
              class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2"
            >
              Timestamp <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.timestamp"
              type="datetime-local"
              class="w-full px-4 py-2.5 border rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none transition bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100"
              :class="
                form.errors.timestamp
                  ? 'border-red-400 bg-red-50 dark:bg-red-900/20'
                  : 'border-gray-300 dark:border-gray-600'
              "
            />
            <p
              v-if="form.errors.timestamp"
              class="mt-1.5 text-xs text-red-600 dark:text-red-400"
            >
              {{ form.errors.timestamp }}
            </p>
          </div>

          <!-- Status -->
          <div>
            <label
              class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2"
            >
              Status <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.status"
              class="w-full px-4 py-2.5 border rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none transition bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100"
              :class="
                form.errors.status
                  ? 'border-red-400 bg-red-50 dark:bg-red-900/20'
                  : 'border-gray-300 dark:border-gray-600'
              "
            >
              <option value="check_in">Check In</option>
              <option value="check_out">Check Out</option>
              <option value="break_in">Break In</option>
              <option value="break_out">Break Out</option>
              <option value="overtime_in">Overtime In</option>
              <option value="overtime_out">Overtime Out</option>
            </select>
            <p
              v-if="form.errors.status"
              class="mt-1.5 text-xs text-red-600 dark:text-red-400"
            >
              {{ form.errors.status }}
            </p>
          </div>

          <!-- Verified By -->
          <div class="sm:col-span-2">
            <label
              class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2"
            >
              Verified By
            </label>
            <select
              v-model="form.verified_by"
              class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none transition bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100"
            >
              <option :value="null">— Not specified —</option>
              <option :value="0">Manual / Password</option>
              <option :value="1">Fingerprint</option>
              <option :value="2">Face Recognition</option>
              <option :value="3">Card</option>
            </select>
            <p
              v-if="form.errors.verified_by"
              class="mt-1.5 text-xs text-red-600 dark:text-red-400"
            >
              {{ form.errors.verified_by }}
            </p>
          </div>
        </div>

        <!-- Actions -->
        <div
          class="flex items-center justify-between gap-3 p-6 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 flex-wrap"
        >
          <div
            class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400"
          >
            <i class="bi bi-clock-history"></i>
            <span>Created: {{ formatDate(attendance.created_at) }}</span>
          </div>
          <div class="flex items-center gap-3">
            <Link
              :href="route(props.backTo)"
              class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center gap-2"
            >
              <i class="bi bi-x-lg"></i> Cancel
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-indigo-500/30"
            >
              <i
                v-if="form.processing"
                class="bi bi-arrow-repeat animate-spin"
              ></i>
              <i v-else class="bi bi-check-lg"></i>
              {{ form.processing ? "Updating..." : "Update Attendance" }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
  attendance: { type: Object, required: true },
  employees: { type: Array, default: () => [] },
  backTo: { type: String, default: "admin.attendance.index" }, // ← new
});

const form = useForm({
  employee_id: props.attendance.employee_id,
  timestamp: toDateTimeLocal(props.attendance.timestamp),
  status: props.attendance.status,
  verified_by: props.attendance.verified_by,
});

const submit = () => {
  form
    .transform((data) => ({
      ...data,
      back_to: props.backTo, // pass to backend
    }))
    .post(route("admin.attendance.update", props.attendance.id), {
      preserveScroll: true,
    });
};

/* ========== Helpers ========== */
function toDateTimeLocal(value) {
  if (!value) return "";
  try {
    const d = new Date(value);
    if (isNaN(d.getTime())) return "";

    // Format as "YYYY-MM-DDTHH:mm" for datetime-local
    const pad = (n) => String(n).padStart(2, "0");
    return (
      d.getFullYear() +
      "-" +
      pad(d.getMonth() + 1) +
      "-" +
      pad(d.getDate()) +
      "T" +
      pad(d.getHours()) +
      ":" +
      pad(d.getMinutes())
    );
  } catch {
    return "";
  }
}

const formatDate = (date) => {
  if (!date) return "-";
  try {
    return new Date(date).toLocaleString("en-GB", {
      day: "2-digit",
      month: "short",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  } catch {
    return "-";
  }
};

const formatFieldName = (field) => {
  return field
    .replace(/_/g, " ")
    .replace(/\b\w/g, (char) => char.toUpperCase());
};
</script>
