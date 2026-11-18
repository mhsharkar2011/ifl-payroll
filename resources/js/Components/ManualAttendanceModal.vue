<template>
  <div v-if="show" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="$emit('close')">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">

      <div class="flex items-start gap-3 mb-5">
        <div class="w-11 h-11 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
          <i class="bi bi-clock-history text-indigo-600 dark:text-indigo-400 text-xl"></i>
        </div>
        <div class="flex-1">
          <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg">
            {{ isEditing ? 'Edit Manual Attendance' : 'Add Manual Attendance' }}
          </h3>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
            Manually record a check-in or check-out.
          </p>
        </div>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <!-- Employee Select -->
        <div>
          <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300 mb-1">Employee</label>
          <select v-model="form.employee_id" required :disabled="isEditing"
            class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none disabled:opacity-60 disabled:cursor-not-allowed">
            <option value="" disabled>Select employee...</option>
            <!-- ✅ Clean binding: emp.id is used directly -->
            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
              {{ emp.employee_id }} — {{ emp.first_name }} {{ emp.last_name }}
            </option>
          </select>
          <p v-if="form.errors.employee_id" class="text-xs text-red-500 mt-1">{{ form.errors.employee_id }}</p>
        </div>

        <!-- Date -->
        <div>
          <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300 mb-1">Date</label>
          <input v-model="form.date" type="date" required :disabled="isEditing"
            class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none disabled:opacity-60 disabled:cursor-not-allowed" />
          <p v-if="form.errors.date" class="text-xs text-red-500 mt-1">{{ form.errors.date }}</p>
        </div>

        <!-- In / Out Times -->
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300 mb-1">In Time</label>
            <input v-model="form.in_time" type="time"
              class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
            <p v-if="form.errors.in_time" class="text-xs text-red-500 mt-1">{{ form.errors.in_time }}</p>
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300 mb-1">Out Time</label>
            <input v-model="form.out_time" type="time"
              class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
            <p v-if="form.errors.out_time" class="text-xs text-red-500 mt-1">{{ form.errors.out_time }}</p>
          </div>
        </div>
        <p class="text-[10px] text-gray-500">Leave blank if the punch is missing.</p>

        <!-- Note -->
        <div>
          <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300 mb-1">Reason / Note</label>
          <input v-model="form.notes" type="text" placeholder="e.g. Forgot to punch out"
            class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
          <p v-if="form.errors.notes" class="text-xs text-red-500 mt-1">{{ form.errors.notes }}</p>
        </div>

        <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
          <button type="button" @click="$emit('close')"
            class="px-4 py-2 text-sm font-medium bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50">
            Cancel
          </button>
          <button type="submit" :disabled="form.processing"
            class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50 flex items-center gap-2">
            <i v-if="form.processing" class="bi bi-arrow-repeat animate-spin"></i>
            Save Record
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  show: { type: Boolean, default: false },
  employees: { type: Array, default: () => [] },
  attendance: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const isEditing = computed(() => !!props.attendance?.id);

const getFormattedDate = () => {
  if (!props.attendance?.timestamp) return new Date().toISOString().split('T')[0];
  const d = new Date(props.attendance.timestamp);
  return isNaN(d.getTime())
    ? props.attendance.timestamp.split(' ')[0]
    : d.toISOString().split('T')[0];
};

const getFormattedTime = (timeString) => {
  if (!timeString) return '';
  if (/^\d{2}:\d{2}$/.test(timeString)) return timeString;
  if (/^\d{2}:\d{2}:\d{2}$/.test(timeString)) return timeString.slice(0, 5);
  try {
    const d = new Date(timeString);
    if (!isNaN(d.getTime())) {
      return d.toTimeString().slice(0, 5);
    }
  } catch (e) {}
  return '';
};

const form = useForm({
  employee_id: '',
  date: '',
  in_time: '',
  out_time: '',
  notes: '',
});

watch(() => props.show, (val) => {
  if (val) {
    if (props.attendance) {
      // ✅ EDIT MODE: Assign the raw ID (e.g., 1, 2, 3)
      form.employee_id = props.attendance.employee_id;
      form.date = getFormattedDate();
      form.in_time = getFormattedTime(props.attendance.in_time);
      form.out_time = getFormattedTime(props.attendance.out_time);
      form.notes = props.attendance.notes || '';
    } else {
      // CREATE MODE
      form.reset();
      form.date = new Date().toISOString().split('T')[0];
    }
    form.clearErrors();
  }
});

const submit = () => {
  if (isEditing.value) {
    form.put(route('admin.attendance.update', props.attendance.id), {
      preserveScroll: true,
      onSuccess: () => emit('close'),
    });
  } else {
    form.post(route('admin.attendance.store'), {
      preserveScroll: true,
      onSuccess: () => emit('close'),
    });
  }
};
</script>
