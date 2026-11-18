<template>
  <AdminLayout page-title="Database Backup">
    <Head title="Database Backup" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Database Backup</h1>
          <p class="text-sm text-gray-500 mt-1">
            Manage your database backups and restore points
          </p>
        </div>
        <button
          @click="showCreateModal = true"
          :disabled="creating"
          class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition shadow-lg disabled:opacity-50"
        >
          <i v-if="creating" class="bi bi-arrow-repeat animate-spin"></i>
          <i v-else class="bi bi-cloud-plus"></i>
          {{ creating ? "Creating..." : "Create Backup" }}
        </button>
      </div>

      <!-- Success Message -->
      <div
        v-if="$page.props.flash?.success"
        class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3"
      >
        <i class="bi bi-check-circle-fill text-green-500 text-xl"></i>
        <p class="text-sm text-green-700 font-medium">
          {{ $page.props.flash.success }}
        </p>
      </div>

      <!-- Error Message -->
      <div
        v-if="$page.props.errors?.error"
        class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3"
      >
        <i class="bi bi-exclamation-triangle-fill text-red-500 text-xl"></i>
        <p class="text-sm text-red-700">{{ $page.props.errors.error }}</p>
      </div>

      <!-- Database Info Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Database Name -->
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
          <div class="flex items-center gap-3 mb-2">
            <div
              class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center"
            >
              <i class="bi bi-database-fill text-indigo-600"></i>
            </div>
            <p class="text-xs text-gray-500 uppercase font-semibold">
              Database
            </p>
          </div>
          <p class="text-lg font-bold text-gray-800 truncate">
            {{ dbInfo?.database || "N/A" }}
          </p>
          <p class="text-xs text-gray-500 mt-1">
            Driver: {{ dbInfo?.driver || "N/A" }}
          </p>
        </div>

        <!-- Tables -->
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
          <div class="flex items-center gap-3 mb-2">
            <div
              class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center"
            >
              <i class="bi bi-table text-green-600"></i>
            </div>
            <p class="text-xs text-gray-500 uppercase font-semibold">Tables</p>
          </div>
          <p class="text-2xl font-bold text-gray-800">
            {{ dbInfo?.table_count || 0 }}
          </p>
          <p class="text-xs text-gray-500 mt-1">Active tables</p>
        </div>

        <!-- Size -->
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
          <div class="flex items-center gap-3 mb-2">
            <div
              class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center"
            >
              <i class="bi bi-hdd-fill text-amber-600"></i>
            </div>
            <p class="text-xs text-gray-500 uppercase font-semibold">Size</p>
          </div>
          <p class="text-2xl font-bold text-gray-800">
            {{ dbInfo?.total_size || "0 MB" }}
          </p>
          <p class="text-xs text-gray-500 mt-1">Database size</p>
        </div>

        <!-- Total Backups -->
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
          <div class="flex items-center gap-3 mb-2">
            <div
              class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center"
            >
              <i class="bi bi-cloud-check-fill text-purple-600"></i>
            </div>
            <p class="text-xs text-gray-500 uppercase font-semibold">Backups</p>
          </div>
          <p class="text-2xl font-bold text-gray-800">
            {{ stats?.completed || 0 }}
          </p>
          <p class="text-xs text-gray-500 mt-1">
            Total: {{ stats?.total || 0 }}
          </p>
        </div>
      </div>

      <!-- Clean Old Backups -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between flex-wrap gap-3">
          <div class="flex items-center gap-3">
            <div
              class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center"
            >
              <i class="bi bi-trash3-fill text-red-600"></i>
            </div>
            <div>
              <p class="font-semibold text-gray-800 text-sm">
                Clean Old Backups
              </p>
              <p class="text-xs text-gray-500">
                Delete backups older than specified days
              </p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <input
              v-model="cleanDays"
              type="number"
              min="1"
              max="365"
              class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
            />
            <span class="text-sm text-gray-500">days</span>
            <button
              @click="cleanOld"
              :disabled="cleaning"
              class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition disabled:opacity-50"
            >
              <i v-if="cleaning" class="bi bi-arrow-repeat animate-spin"></i>
              <i v-else class="bi bi-trash"></i>
              Clean
            </button>
          </div>
        </div>
      </div>

      <!-- Backups Table -->
      <div
        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
      >
        <div
          class="px-6 py-4 border-b border-gray-100 flex items-center justify-between"
        >
          <div class="flex items-center gap-3">
            <div
              class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center"
            >
              <i class="bi bi-archive-fill text-indigo-600"></i>
            </div>
            <div>
              <h3 class="font-bold text-gray-800">Backup History</h3>
              <p class="text-xs text-gray-500">
                Total: {{ backups?.total || 0 }} backups
              </p>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
              <tr>
                <th class="px-6 py-3 text-left font-semibold">Filename</th>
                <th class="px-6 py-3 text-left font-semibold">Size</th>
                <th class="px-6 py-3 text-left font-semibold">Type</th>
                <th class="px-6 py-3 text-left font-semibold">Status</th>
                <th class="px-6 py-3 text-left font-semibold">Created</th>
                <th class="px-6 py-3 text-right font-semibold">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr
                v-for="backup in backups?.data || []"
                :key="backup.id"
                class="hover:bg-gray-50/70 transition"
              >
                <!-- Filename -->
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div
                      class="w-9 h-9 rounded-lg bg-indigo-100 flex items-center justify-center"
                    >
                      <i class="bi bi-file-earmark-code text-indigo-600"></i>
                    </div>
                    <div>
                      <p class="font-semibold text-gray-800 font-mono text-xs">
                        {{ backup.filename }}
                      </p>
                      <p
                        v-if="backup.notes"
                        class="text-xs text-gray-500 mt-0.5"
                      >
                        {{ backup.notes }}
                      </p>
                    </div>
                  </div>
                </td>

                <!-- Size -->
                <td class="px-6 py-4 text-gray-700 font-medium">
                  {{ formatBytes(backup.size) }}
                </td>

                <!-- Type -->
                <td class="px-6 py-4">
                  <span
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold"
                    :class="
                      backup.type === 'manual'
                        ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-200'
                        : 'bg-purple-50 text-purple-700 ring-1 ring-purple-200'
                    "
                  >
                    <i
                      :class="
                        backup.type === 'manual'
                          ? 'bi bi-hand-index'
                          : 'bi bi-clock'
                      "
                    ></i>
                    {{ backup.type }}
                  </span>
                </td>

                <!-- Status -->
                <td class="px-6 py-4">
                  <span
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold"
                    :class="getStatusClass(backup.status)"
                  >
                    <span
                      class="w-1.5 h-1.5 rounded-full"
                      :class="getStatusDot(backup.status)"
                    ></span>
                    {{ backup.status_message || backup.status }}
                  </span>
                  <div
                    v-if="backup.last_action_at"
                    class="text-xs text-gray-400 mt-1"
                  >
                    {{
                      backup.last_action === "restore"
                        ? "Restored"
                        : "Backed up"
                    }}
                    {{ new Date(backup.last_action_at).toLocaleString() }}
                  </div>
                </td>

                <!-- Created -->
                <td class="px-6 py-4">
                  <div class="text-gray-700">
                    {{ formatDate(backup.created_at) }}
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ formatTime(backup.created_at) }}
                  </div>
                </td>

                <!-- Actions -->
                <td class="px-6 py-4">
                  <div class="flex items-center justify-end gap-1">
                    <a
                      :href="route('admin.backups.download', backup.id)"
                      class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-green-50 hover:text-green-600 transition"
                      title="Download"
                    >
                      <i class="bi bi-download"></i>
                    </a>
                    <button
                      @click="confirmRestore(backup)"
                      class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-amber-50 hover:text-amber-600 transition"
                      title="Restore"
                    >
                      <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                    <button
                      @click="confirmDelete(backup)"
                      class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-red-50 hover:text-red-600 transition"
                      title="Delete"
                    >
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="!backups?.data?.length">
                <td colspan="6" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center gap-3">
                    <div
                      class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center"
                    >
                      <i class="bi bi-archive text-3xl text-gray-400"></i>
                    </div>
                    <div>
                      <h3 class="font-semibold text-gray-700">
                        No backups yet
                      </h3>
                      <p class="text-sm text-gray-500 mt-1">
                        Create your first backup to secure your data
                      </p>
                    </div>
                    <button
                      @click="showCreateModal = true"
                      class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition"
                    >
                      <i class="bi bi-cloud-plus"></i> Create Backup
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Create Backup Modal -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
      @click.self="showCreateModal = false"
    >
      <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
        <div class="flex items-start gap-4 mb-5">
          <div
            class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center shrink-0"
          >
            <i class="bi bi-cloud-plus-fill text-indigo-600 text-xl"></i>
          </div>
          <div class="flex-1">
            <h3 class="font-bold text-gray-800 text-lg">Create Backup?</h3>
            <p class="text-sm text-gray-500 mt-1">
              A new backup of your database will be created. This may take a few
              moments.
            </p>
          </div>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-semibold text-gray-700 mb-2"
            >Notes (optional)</label
          >
          <textarea
            v-model="createForm.notes"
            rows="2"
            placeholder="e.g., Before major update"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
          ></textarea>
        </div>

        <div class="flex justify-end gap-2">
          <button
            @click="showCreateModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="createBackup"
            :disabled="createForm.processing"
            class="px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 flex items-center gap-2"
          >
            <i
              v-if="createForm.processing"
              class="bi bi-arrow-repeat animate-spin"
            ></i>
            <i v-else class="bi bi-check-lg"></i>
            Create Backup
          </button>
        </div>
      </div>
    </div>

    <!-- Restore Confirmation Modal -->
    <div
      v-if="showRestoreModal"
      class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
      @click.self="showRestoreModal = false"
    >
      <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
        <div class="flex items-start gap-4 mb-5">
          <div
            class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center shrink-0"
          >
            <i
              class="bi bi-exclamation-triangle-fill text-amber-600 text-xl"
            ></i>
          </div>
          <div class="flex-1">
            <h3 class="font-bold text-gray-800 text-lg">Restore Database?</h3>
            <p class="text-sm text-gray-500 mt-1">
              This will <strong class="text-red-600">overwrite</strong> your
              current database with data from:
            </p>
            <p class="text-xs font-mono bg-gray-100 p-2 rounded mt-2 break-all">
              {{ selectedBackup?.filename }}
            </p>
            <p class="text-xs text-red-600 mt-2 font-semibold">
              ⚠️ This action cannot be undone!
            </p>
          </div>
        </div>

        <label class="flex items-center gap-2 mb-4">
          <input type="checkbox" v-model="restoreConfirmed" class="rounded" />
          <span class="text-sm text-gray-700"
            >I understand and want to proceed</span
          >
        </label>

        <div class="flex justify-end gap-2">
          <button
            @click="showRestoreModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="restoreBackup"
            :disabled="!restoreConfirmed || restoring"
            class="px-4 py-2 text-sm font-semibold text-white bg-amber-600 rounded-lg hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
          >
            <i v-if="restoring" class="bi bi-arrow-repeat animate-spin"></i>
            <i v-else class="bi bi-arrow-counterclockwise"></i>
            Restore
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
      @click.self="showDeleteModal = false"
    >
      <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
        <div class="flex items-start gap-4 mb-5">
          <div
            class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center shrink-0"
          >
            <i class="bi bi-trash3-fill text-red-600 text-xl"></i>
          </div>
          <div class="flex-1">
            <h3 class="font-bold text-gray-800 text-lg">Delete Backup?</h3>
            <p class="text-sm text-gray-500 mt-1">
              This will permanently delete:
            </p>
            <p class="text-xs font-mono bg-gray-100 p-2 rounded mt-2 break-all">
              {{ selectedBackup?.filename }}
            </p>
          </div>
        </div>
        <div class="flex justify-end gap-2">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="deleteBackup"
            :disabled="deleting"
            class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 flex items-center gap-2"
          >
            <i v-if="deleting" class="bi bi-arrow-repeat animate-spin"></i>
            <i v-else class="bi bi-trash"></i>
            Delete
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

const props = defineProps({
  backups: {
    type: Object,
    default: () => ({ data: [], total: 0 }),
  },
  dbInfo: {
    type: Object,
    default: () => ({}),
  },
  stats: {
    type: Object,
    default: () => ({}),
  },
});

/* ========== State ========== */
const creating = ref(false);
const cleaning = ref(false);
const restoring = ref(false);
const deleting = ref(false);
const cleanDays = ref(30);
const restoreConfirmed = ref(false);

const showCreateModal = ref(false);
const showRestoreModal = ref(false);
const showDeleteModal = ref(false);

const selectedBackup = ref(null);

const createForm = useForm({
  notes: "",
});

/* ========== Actions ========== */
const createBackup = () => {
  createForm.post(route("admin.backups.store"), {
    preserveScroll: true,
    onSuccess: () => {
      showCreateModal.value = false;
      createForm.reset();
    },
  });
};

const cleanOld = () => {
  if (!confirm(`Delete backups older than ${cleanDays.value} days?`)) return;

  cleaning.value = true;
  router.post(
    route("admin.backups.clean"),
    {
      days: cleanDays.value,
    },
    {
      preserveScroll: true,
      onFinish: () => {
        cleaning.value = false;
      },
    }
  );
};

const confirmRestore = (backup) => {
  selectedBackup.value = backup;
  restoreConfirmed.value = false;
  showRestoreModal.value = true;
};

const restoreBackup = () => {
  if (!selectedBackup.value) return;

  restoring.value = true;
  router.post(
    route("admin.backups.restore", selectedBackup.value.id),
    {
      confirm: true,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        showRestoreModal.value = false;
        selectedBackup.value = null;
      },
      onFinish: () => {
        restoring.value = false;
      },
    }
  );
};

const confirmDelete = (backup) => {
  selectedBackup.value = backup;
  showDeleteModal.value = true;
};

const deleteBackup = () => {
  if (!selectedBackup.value) return;

  deleting.value = true;
  router.post(
    route("admin.backups.destroy", selectedBackup.value.id),
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        showDeleteModal.value = false;
        selectedBackup.value = null;
      },
      onFinish: () => {
        deleting.value = false;
      },
    }
  );
};

/* ========== Helpers ========== */
const formatBytes = (bytes) => {
  if (!bytes) return "0 B";
  if (bytes < 1024) return bytes + " B";
  if (bytes < 1048576) return (bytes / 1024).toFixed(2) + " KB";
  if (bytes < 1073741824) return (bytes / 1048576).toFixed(2) + " MB";
  return (bytes / 1073741824).toFixed(2) + " GB";
};

const formatDate = (date) => {
  if (!date) return "-";
  return new Date(date).toLocaleDateString("en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
};

const formatTime = (date) => {
  if (!date) return "-";
  return new Date(date).toLocaleTimeString("en-US", {
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  });
};

const getStatusClass = (status) => {
  const map = {
    pending: "bg-amber-50 text-amber-700 ring-1 ring-amber-200",
    completed: "bg-green-50 text-green-700 ring-1 ring-green-200",
    failed: "bg-red-50 text-red-700 ring-1 ring-red-200",
  };
  return map[status] || "bg-gray-50 text-gray-700 ring-1 ring-gray-200";
};

const getStatusDot = (status) => {
  const map = {
    pending: "bg-amber-500",
    completed: "bg-green-500",
    failed: "bg-red-500",
  };
  return map[status] || "bg-gray-500";
};
</script>
