<template>
  <AdminLayout page-title="ZKTeco Devices">
    <Head title="Devices" />

    <div class="space-y-6">
      <!-- HEADER -->
      <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
            ZKTeco Devices
          </h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Manage biometric devices · <strong>Pull mode</strong> (TCP 4370)
          </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
          <button
            @click="refreshNow"
            :disabled="refreshing"
            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition disabled:opacity-50"
            title="Reload device list and re-check online status"
          >
            <i
              class="bi bi-arrow-clockwise"
              :class="{ 'animate-spin': refreshing || pinging }"
            ></i>
            <span class="hidden sm:inline">Refresh</span>
          </button>

          <button
            @click="toggleAutoRefresh"
            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-lg transition border"
            :class="
              autoRefresh
                ? 'text-green-700 dark:text-green-300 bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800'
                : 'text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600'
            "
          >
            <i
              :class="autoRefresh ? 'bi bi-arrow-clockwise' : 'bi bi-pause-circle'"
            ></i>
            <span class="hidden sm:inline">
              {{ autoRefresh ? "Auto: ON" : "Auto: OFF" }}
            </span>
          </button>

          <Link
            :href="route('admin.devices.create')"
            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition shadow-lg"
          >
            <i class="bi bi-plus-lg"></i>
            Add Device
          </Link>
        </div>
      </div>

      <!-- FLASH -->
      <div
        v-if="$page.props.flash?.success"
        class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 flex items-center gap-3"
      >
        <i class="bi bi-check-circle-fill text-green-500 text-xl"></i>
        <p class="text-sm text-green-700 dark:text-green-300 font-medium">
          {{ $page.props.flash.success }}
        </p>
      </div>

      <!-- INFO BANNER (Pull mode) -->
      <div
        v-if="devices.length > 0"
        class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-5 flex items-start gap-4"
      >
        <div
          class="w-11 h-11 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center shrink-0"
        >
          <i class="bi bi-cloud-download text-blue-600 dark:text-blue-400 text-xl"></i>
        </div>
        <div class="flex-1 min-w-0">
          <h3 class="font-bold text-blue-900 dark:text-blue-200">Pull mode</h3>
          <p class="text-sm text-blue-800 dark:text-blue-300 mt-1">
            This server connects to each device on TCP
            <code class="font-mono">4370</code> and downloads the attendance log.
            Click <strong>Pull Now</strong> on any device to fetch new records.
          </p>
          <p class="text-xs text-blue-700 dark:text-blue-400 mt-2">
            <i class="bi bi-info-circle mr-1"></i>
            The device does not need a Cloud Server / ADMS configuration.
            <strong>Do not</strong> enable Push SDK — it will not work on this firmware.
          </p>
        </div>
      </div>

      <!-- STATS -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
              <i class="bi bi-router text-indigo-600 dark:text-indigo-400 text-lg"></i>
            </div>
            <div>
              <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-medium">Total Devices</p>
              <p class="text-xl font-bold text-gray-800 dark:text-gray-100">
                {{ stats?.total || 0 }}
              </p>
            </div>
          </div>
        </div>

        <!-- ⭐ LIVE ONLINE COUNT -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
          <div class="flex items-center gap-3">
            <div
              class="w-11 h-11 rounded-lg flex items-center justify-center"
              :class="
                onlineCount > 0
                  ? 'bg-green-100 dark:bg-green-900/30'
                  : 'bg-gray-100 dark:bg-gray-700'
              "
            >
              <i
                class="bi bi-wifi text-lg"
                :class="
                  onlineCount > 0
                    ? 'text-green-600 dark:text-green-400'
                    : 'text-gray-500 dark:text-gray-400'
                "
              ></i>
            </div>
            <div>
              <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-medium">
                Online (live)
              </p>
              <p
                class="text-xl font-bold"
                :class="
                  onlineCount > 0
                    ? 'text-green-600 dark:text-green-400'
                    : 'text-gray-800 dark:text-gray-100'
                "
              >
                {{ onlineCount }}
                <span class="text-sm font-normal text-gray-400">
                  / {{ devices.length }}
                </span>
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
              <i class="bi bi-calendar-check text-blue-600 dark:text-blue-400 text-lg"></i>
            </div>
            <div>
              <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-medium">
                Records Today
              </p>
              <p class="text-xl font-bold text-gray-800 dark:text-gray-100">
                {{ stats?.synced_today || 0 }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- DEVICES GRID -->
      <div
        v-if="devices?.length"
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
      >
        <div
          v-for="device in devices"
          :key="device.id"
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border overflow-hidden hover:shadow-lg transition-all"
          :class="
            isOnline(device)
              ? 'border-green-200 dark:border-green-800 ring-1 ring-green-100 dark:ring-green-900/50'
              : isPulledRecently(device)
                ? 'border-amber-200 dark:border-amber-800'
                : 'border-gray-100 dark:border-gray-700'
          "
        >
          <!-- Header -->
          <div
            class="p-5 border-b border-gray-100 dark:border-gray-700"
            :class="
              isOnline(device)
                ? 'bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20'
                : 'bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20'
            "
          >
            <div class="flex items-start justify-between mb-3">
              <div
                class="w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg"
                :class="
                  isOnline(device)
                    ? 'bg-gradient-to-br from-green-500 to-emerald-600'
                    : 'bg-gradient-to-br from-indigo-500 to-purple-600'
                "
              >
                <i class="bi bi-router text-xl"></i>
              </div>

              <div class="flex flex-col items-end gap-1">
                <!-- Active -->
                <span
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold"
                  :class="
                    device.is_active
                      ? 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 ring-1 ring-green-200 dark:ring-green-800'
                      : 'bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 ring-1 ring-gray-200 dark:ring-gray-600'
                  "
                >
                  <span
                    class="w-1.5 h-1.5 rounded-full"
                    :class="device.is_active ? 'bg-green-500' : 'bg-gray-500'"
                  ></span>
                  {{ device.is_active ? "Active" : "Inactive" }}
                </span>

                <!-- ⭐ LIVE ONLINE STATUS -->
                <span
                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold"
                  :class="
                    isOnline(device)
                      ? 'bg-green-500 text-white'
                      : 'bg-red-500 text-white'
                  "
                  :title="
                    isOnline(device)
                      ? `Reachable · ${onlineLatency(device)}ms`
                      : onlineError(device) || 'Device unreachable'
                  "
                >
                  <i class="bi" :class="isOnline(device) ? 'bi-wifi' : 'bi-wifi-off'"></i>
                  {{ isOnline(device) ? "Online" : "Offline" }}
                </span>

                <!-- Pull freshness -->
                <span
                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold"
                  :class="pullStatusClass(device)"
                >
                  <i :class="pullStatusIcon(device)"></i>
                  {{ pullStatusLabel(device) }}
                </span>
              </div>
            </div>
            <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg truncate">
              {{ device.name }}
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
              {{ device.model || "ZKTeco Device" }}
            </p>
          </div>

          <!-- Info -->
          <div class="p-5 space-y-2">
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-500 dark:text-gray-400">IP Address</span>
              <span class="font-mono font-semibold text-gray-800 dark:text-gray-100">
                {{ device.ip_address }}
              </span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-500 dark:text-gray-400">Port</span>
              <span class="font-mono font-semibold text-gray-800 dark:text-gray-100">
                {{ device.port || 4370 }}
              </span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-500 dark:text-gray-400">Serial</span>
              <span
                class="font-mono text-xs text-gray-700 dark:text-gray-300 truncate max-w-[140px]"
                :title="device.serial_number"
              >
                {{ device.serial_number || "-" }}
              </span>
            </div>

            <!-- Latency (only when online) -->
            <div
              v-if="isOnline(device)"
              class="flex items-center justify-between text-sm"
            >
              <span class="text-gray-500 dark:text-gray-400">Latency</span>
              <span class="font-mono text-xs text-green-600 dark:text-green-400">
                {{ onlineLatency(device) }}ms
              </span>
            </div>

            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-500 dark:text-gray-400">Last Pulled</span>
              <span
                class="text-xs font-medium"
                :title="device.last_sync_at || 'Never pulled'"
                :class="{
                  'text-green-600 dark:text-green-400': isPulledRecently(device),
                  'text-gray-700 dark:text-gray-300': !isPulledRecently(device),
                }"
              >
                {{ device.last_sync_at ? formatTimeAgo(device.last_sync_at) : "Never" }}
              </span>
            </div>

            <div
              v-if="!isPulledRecently(device)"
              class="mt-2 px-2.5 py-2 rounded-lg text-[10px] leading-tight bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300"
            >
              <i class="bi bi-lightbulb-fill mr-1"></i>
              <strong>Not pulled recently.</strong> Click <strong>Pull Now</strong> to download new records.
            </div>

            <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
              <div class="text-center">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-medium">Today</p>
                <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                  {{ device.today_attendance || 0 }}
                </p>
              </div>
              <div class="text-center">
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-medium">Total</p>
                <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                  {{ device.total_attendance || 0 }}
                </p>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
            <div class="grid grid-cols-3 gap-1.5">
              <button
                @click="testConnection(device)"
                :disabled="testing === device.id"
                class="inline-flex items-center justify-center gap-1.5 px-2 py-2 text-xs font-semibold text-indigo-600 dark:text-indigo-400 bg-white dark:bg-gray-700 border border-indigo-200 dark:border-indigo-800 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition disabled:opacity-50"
                title="Test connectivity to TCP 4370"
              >
                <i v-if="testing === device.id" class="bi bi-arrow-repeat animate-spin"></i>
                <i v-else class="bi bi-plug-fill"></i>
                <span class="hidden xl:inline">Test</span>
              </button>

              <button
                @click="syncDevice(device)"
                :disabled="syncing === device.id"
                class="inline-flex items-center justify-center gap-1.5 px-2 py-2 text-xs font-semibold text-green-600 dark:text-green-400 bg-white dark:bg-gray-700 border border-green-200 dark:border-green-800 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/30 transition disabled:opacity-50"
                title="Pull attendance from device"
              >
                <i v-if="syncing === device.id" class="bi bi-arrow-repeat animate-spin"></i>
                <i v-else class="bi bi-cloud-download"></i>
                <span class="hidden xl:inline">{{ syncing === device.id ? "Pulling…" : "Pull Now" }}</span>
              </button>

              <Link
                :href="route('admin.devices.show', device.id)"
                class="inline-flex items-center justify-center gap-1.5 px-2 py-2 text-xs font-semibold text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition"
                title="View Details"
              >
                <i class="bi bi-eye"></i>
                <span class="hidden xl:inline">View</span>
              </Link>
            </div>

            <div class="grid grid-cols-2 gap-1.5 mt-1.5">
              <Link
                :href="route('admin.devices.edit', device.id)"
                class="inline-flex items-center justify-center gap-1.5 px-2 py-1.5 text-xs font-semibold text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition"
              >
                <i class="bi bi-pencil"></i>
                <span>Edit</span>
              </Link>

              <button
                @click="confirmDelete(device)"
                class="inline-flex items-center justify-center gap-1.5 px-2 py-1.5 text-xs font-semibold text-red-600 dark:text-red-400 bg-white dark:bg-gray-700 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 transition"
              >
                <i class="bi bi-trash"></i>
                <span>Delete</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- EMPTY STATE -->
      <div
        v-else
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center"
      >
        <div class="w-20 h-20 mx-auto rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-4">
          <i class="bi bi-router text-3xl text-gray-400"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-1">
          No devices configured
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
          Add your first ZKTeco device to start pulling attendance data
        </p>
        <Link
          :href="route('admin.devices.create')"
          class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition"
        >
          <i class="bi bi-plus-lg"></i> Add First Device
        </Link>
      </div>
    </div>

    <!-- TEST RESULT MODAL -->
    <div
      v-if="testResult"
      class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
      @click.self="testResult = null"
    >
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-lg w-full p-6">
        <div class="flex items-start gap-4 mb-5">
          <div
            class="w-12 h-12 rounded-full flex items-center justify-center shrink-0"
            :class="
              testResult.success
                ? 'bg-green-100 dark:bg-green-900/30'
                : 'bg-red-100 dark:bg-red-900/30'
            "
          >
            <i
              class="text-xl"
              :class="
                testResult.success
                  ? 'bi bi-check-circle-fill text-green-600 dark:text-green-400'
                  : 'bi bi-x-circle-fill text-red-600 dark:text-red-400'
              "
            ></i>
          </div>
          <div class="flex-1">
            <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg">
              {{ testResult.success ? "Connection Test: Success" : "Connection Test: Failed" }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
              {{ testResult.message }}
            </p>
          </div>
        </div>

        <div v-if="testResult.info" class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 space-y-2">
          <div
            v-for="(value, key) in testResult.info"
            :key="key"
            class="flex items-center justify-between text-sm"
          >
            <span class="text-gray-500 dark:text-gray-400 capitalize">
              {{ key.replace(/_/g, " ") }}
            </span>
            <span class="font-mono text-gray-800 dark:text-gray-100">
              {{ value || "-" }}
            </span>
          </div>
        </div>

        <div class="flex justify-end mt-6">
          <button
            @click="testResult = null"
            class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition"
          >
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- DELETE MODAL -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
      @click.self="showDeleteModal = false"
    >
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
        <div class="flex items-start gap-4 mb-5">
          <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
            <i class="bi bi-trash3-fill text-red-600 dark:text-red-400 text-xl"></i>
          </div>
          <div class="flex-1">
            <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg">Delete Device?</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              Are you sure you want to delete
              <strong>{{ selectedDevice?.name }}</strong>? This action cannot be undone.
            </p>
          </div>
        </div>
        <div class="flex justify-end gap-2">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="deleteDevice"
            :disabled="deleting"
            class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition disabled:opacity-50 flex items-center gap-2"
          >
            <i v-if="deleting" class="bi bi-arrow-repeat animate-spin"></i>
            <i v-else class="bi bi-trash"></i>
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
      >
        <i
          class="bi text-lg mt-0.5"
          :class="toast.type === 'error' ? 'bi-x-circle-fill' : 'bi-check-circle-fill'"
        ></i>
        <div class="text-sm font-medium">{{ toast.message }}</div>
        <button @click="toast = null" class="ml-2 opacity-60 hover:opacity-100 transition">
          <i class="bi bi-x-lg text-xs"></i>
        </button>
      </div>
    </Transition>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import axios from "axios";

const props = defineProps({
  devices: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
});

/* ========== State ========== */
const testing = ref(null);
const syncing = ref(null);
const deleting = ref(false);
const refreshing = ref(false);

const testResult = ref(null);
const showDeleteModal = ref(false);
const selectedDevice = ref(null);

const autoRefresh = ref(true);
let refreshInterval = null;

const toast = ref(null);
let toastTimer = null;

/* ========== ⭐ Live online detection ========== */
const onlineMap = ref({});
const pinging = ref(false);
let pingInterval = null;

const PING_INTERVAL_MS = 15_000;

const isOnline = (device) => {
  const entry = onlineMap.value[device.id];
  return !!entry?.is_online;
};

const onlineLatency = (device) => onlineMap.value[device.id]?.latency_ms ?? null;
const onlineError = (device) => onlineMap.value[device.id]?.error ?? null;

const onlineCount = computed(
  () => props.devices.filter(isOnline).length
);

const pingAllDevices = async () => {
  if (pinging.value || props.devices.length === 0) return;

  pinging.value = true;

  try {
    const { data } = await axios.get("/admin/devices/ping-all");

    const map = {};
    for (const d of data.devices || []) {
      map[d.id] = {
        is_online: !!d.is_online,
        latency_ms: d.latency_ms,
        error: d.error,
      };
    }
    onlineMap.value = map;
  } catch (e) {
    console.error("Ping failed:", e);
    const map = {};
    for (const d of props.devices) {
      map[d.id] = { is_online: false, latency_ms: null, error: "Ping failed" };
    }
    onlineMap.value = map;
  } finally {
    pinging.value = false;
  }
};

/* ========== Pull freshness ========== */
const RECENT_PULL_MS = 5 * 60 * 1000;

const isPulledRecently = (device) => {
  if (!device?.last_sync_at) return false;
  const elapsed = Date.now() - new Date(device.last_sync_at).getTime();
  return elapsed >= 0 && elapsed < RECENT_PULL_MS;
};

const devicesPulledRecently = computed(
  () => props.devices.filter(isPulledRecently).length
);

/* ========== Status badge ========== */
const pullStatusLabel = (device) => {
  if (!device?.last_sync_at) return "Never pulled";
  return isPulledRecently(device) ? "Fresh" : "Stale";
};

const pullStatusIcon = (device) => {
  if (!device?.last_sync_at) return "bi bi-dash-circle";
  return isPulledRecently(device)
    ? "bi bi-circle-fill text-green-500"
    : "bi bi-circle-fill text-amber-500";
};

const pullStatusClass = (device) => {
  if (!device?.last_sync_at) {
    return "bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-400 ring-1 ring-gray-200 dark:ring-gray-600";
  }
  return isPulledRecently(device)
    ? "bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 ring-1 ring-green-200 dark:ring-green-800"
    : "bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-800";
};

/* ========== Refresh ========== */
const refreshNow = async () => {
  if (refreshing.value) return;
  refreshing.value = true;

  try {
    // Ping and reload in parallel for speed
    await Promise.allSettled([
      pingAllDevices(),
      new Promise((resolve) => {
        router.reload({
          only: ["devices", "stats"],
          preserveScroll: true,
          preserveState: true,
          onFinish: resolve,
        });
      }),
    ]);
  } finally {
    refreshing.value = false;
  }
};

/* ========== Auto Refresh ========== */
const toggleAutoRefresh = () => {
  autoRefresh.value = !autoRefresh.value;
  localStorage.setItem("deviceAutoRefresh", autoRefresh.value ? "true" : "false");
  autoRefresh.value ? startAutoRefresh() : stopAutoRefresh();
};

const startAutoRefresh = () => {
  stopAutoRefresh();
  refreshInterval = setInterval(() => {
    if (document.hidden) return;
    router.reload({
      only: ["devices", "stats"],
      preserveScroll: true,
      preserveState: true,
    });
  }, 30000);
};

const stopAutoRefresh = () => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
    refreshInterval = null;
  }
};

const handleVisibility = () => {
  if (!document.hidden) {
    pingAllDevices();
    if (autoRefresh.value) {
      router.reload({
        only: ["devices", "stats"],
        preserveScroll: true,
        preserveState: true,
      });
    }
  }
};

/* ========== Toast ========== */
const showToast = (message, type = "success") => {
  toast.value = { message, type };
  if (toastTimer) clearTimeout(toastTimer);
  toastTimer = setTimeout(() => {
    toast.value = null;
  }, 4000);
};

/* ========== Test Connection ========== */
const testConnection = async (device) => {
  testing.value = device.id;
  testResult.value = null;

  try {
    const response = await axios.post(`/admin/devices/${device.id}/test`);
    testResult.value = response.data;

    // ⭐ Sync live status with what the test just learned
    onlineMap.value = {
      ...onlineMap.value,
      [device.id]: {
        is_online: !!response.data.success,
        latency_ms: response.data.info?.latency_ms ?? null,
        error: response.data.success ? null : response.data.message,
      },
    };

    if (response.data.success) refreshNow();
  } catch (error) {
    testResult.value = {
      success: false,
      message: error.response?.data?.message || "Connection failed",
      info: error.response?.data?.info || null,
    };

    onlineMap.value = {
      ...onlineMap.value,
      [device.id]: {
        is_online: false,
        latency_ms: null,
        error: "Connection failed",
      },
    };
  } finally {
    testing.value = null;
  }
};

/* ========== Pull (sync) ========== */
const syncDevice = async (device) => {
  syncing.value = device.id;

  try {
    const response = await axios.post(`/admin/devices/${device.id}/sync`);
    const synced = response.data.synced ?? 0;
    showToast(
      response.data.message ||
        `Pulled ${synced} new record${synced === 1 ? "" : "s"} from ${device.name}.`
    );

    // Refresh both the list and the online status
    refreshNow();
  } catch (error) {
    showToast(error.response?.data?.message || "Pull failed", "error");
  } finally {
    syncing.value = null;
  }
};

/* ========== Delete ========== */
const confirmDelete = (device) => {
  selectedDevice.value = device;
  showDeleteModal.value = true;
};

const deleteDevice = async () => {
  if (!selectedDevice.value) return;
  deleting.value = true;

  try {
    router.post(
      `/admin/devices/${selectedDevice.value.id}/delete`,
      {},
      {
        preserveScroll: true,
        onSuccess: () => {
          showDeleteModal.value = false;
          selectedDevice.value = null;
          showToast("Device deleted.");
        },
        onFinish: () => {
          deleting.value = false;
        },
      }
    );
  } catch (e) {
    deleting.value = false;
  }
};

/* ========== Helpers ========== */
const formatTimeAgo = (date) => {
  if (!date) return "Never";
  const seconds = Math.floor((Date.now() - new Date(date).getTime()) / 1000);
  if (seconds < 0) return "just now";
  if (seconds < 5) return "just now";
  if (seconds < 60) return seconds + "s ago";
  if (seconds < 3600) return Math.floor(seconds / 60) + "m ago";
  if (seconds < 86400) return Math.floor(seconds / 3600) + "h ago";
  return Math.floor(seconds / 86400) + "d ago";
};

/* ========== Lifecycle ========== */
onMounted(() => {
  const saved = localStorage.getItem("deviceAutoRefresh");
  autoRefresh.value = saved !== "false";

  // Kick off initial ping
  pingAllDevices();

  // Poll live online status every 15s
  pingInterval = setInterval(() => {
    if (document.hidden) return;
    pingAllDevices();
  }, PING_INTERVAL_MS);

  if (autoRefresh.value) startAutoRefresh();
  document.addEventListener("visibilitychange", handleVisibility);
});

onUnmounted(() => {
  stopAutoRefresh();
  if (pingInterval) clearInterval(pingInterval);
  if (toastTimer) clearTimeout(toastTimer);
  document.removeEventListener("visibilitychange", handleVisibility);
});
</script>
