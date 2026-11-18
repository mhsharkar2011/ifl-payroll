<template>
  <AdminLayout page-title="Bulk Upload Attendance">
    <Head title="Bulk Upload Attendance" />

    <div class="max-w-4xl mx-auto space-y-6">
      <!-- ===================== Header ===================== -->
      <div class="flex items-center gap-4">
        <Link
          :href="route('admin.attendance.index')"
          class="w-10 h-10 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-700 transition"
        >
          <i class="bi bi-arrow-left text-gray-600 dark:text-gray-300"></i>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
            Bulk Upload Attendance
          </h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Import ZKTeco logs via Excel / CSV / TXT
          </p>
        </div>
      </div>

      <!-- ===================== Success ===================== -->
      <div
        v-if="$page.props.flash?.success"
        class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 flex items-start gap-3"
      >
        <i class="bi bi-check-circle-fill text-green-500 text-xl shrink-0"></i>
        <p class="text-sm text-green-700 dark:text-green-300 font-medium">
          {{ $page.props.flash.success }}
        </p>
      </div>

      <!-- ===================== Import summary ===================== -->
      <div
        v-if="$page.props.flash?.import_summary"
        class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-xl p-4"
      >
        <h4 class="font-semibold text-indigo-800 dark:text-indigo-300 mb-2">
          <i class="bi bi-clipboard-data me-1"></i> Import Summary
        </h4>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
          <div class="bg-white dark:bg-gray-800 rounded-lg p-2 text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400">Total Rows</p>
            <p class="font-bold text-gray-800 dark:text-gray-100">
              {{ $page.props.flash.import_summary.total }}
            </p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg p-2 text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400">Imported</p>
            <p class="font-bold text-green-600 dark:text-green-400">
              {{ $page.props.flash.import_summary.imported }}
            </p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg p-2 text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400">Duplicates</p>
            <p class="font-bold text-amber-600 dark:text-amber-400">
              {{ $page.props.flash.import_summary.duplicates }}
            </p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg p-2 text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400">Unknown IDs</p>
            <p class="font-bold text-red-600 dark:text-red-400">
              {{ $page.props.flash.import_summary.unknown }}
            </p>
          </div>
        </div>

        <!-- Unknown employee list -->
        <div
          v-if="$page.props.flash.import_summary.unknown_ids?.length"
          class="mt-3 p-2 bg-white dark:bg-gray-800 rounded-lg border border-red-200 dark:border-red-800"
        >
          <p class="text-xs font-semibold text-red-700 dark:text-red-300 mb-1">
            Unmatched Ac-No values (create these employees first):
          </p>
          <p class="text-xs font-mono text-red-600 dark:text-red-400 break-all">
            {{ $page.props.flash.import_summary.unknown_ids.join(", ") }}
          </p>
        </div>
      </div>

      <!-- ===================== Errors ===================== -->
      <div
        v-if="hasErrors"
        class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4"
      >
        <div class="flex items-start gap-3">
          <i class="bi bi-exclamation-triangle-fill text-red-500 text-xl shrink-0"></i>
          <div class="flex-1">
            <h4 class="font-semibold text-red-800 dark:text-red-300 mb-2">
              ❌ Import Failed — Please fix:
            </h4>

            <div
              v-if="form.errors?.file"
              class="mb-3 p-3 bg-white dark:bg-gray-800 rounded-lg border border-red-200 dark:border-red-700"
            >
              <p class="text-sm text-red-700 dark:text-red-300 font-medium whitespace-pre-line">
                <i class="bi bi-file-earmark-excel me-1"></i>
                {{ form.errors.file }}
              </p>
            </div>

            <ul class="text-sm text-red-700 dark:text-red-300 space-y-1">
              <li
                v-for="(error, field) in form.errors"
                :key="field"
                class="flex items-start gap-2"
              >
                <i class="bi bi-dot"></i>
                <span>
                  <strong>{{ formatFieldName(field) }}:</strong> {{ error }}
                </span>
              </li>
            </ul>

            <button
              @click="clearFile"
              class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-700 dark:text-red-300 bg-white dark:bg-gray-800 border border-red-300 dark:border-red-700 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition"
            >
              <i class="bi bi-arrow-clockwise"></i>
              Clear & Try Again
            </button>
          </div>
        </div>
      </div>

      <!-- ===================== Step 1: Template ===================== -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold">
            1
          </div>
          <div>
            <h3 class="font-bold text-gray-800 dark:text-gray-100">
              Download Template
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              Standard 2-column format: <code>Ac-No</code> + <code>sTime</code>
            </p>
          </div>
        </div>
        <div class="p-6">
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Get a ready-to-fill template that matches your MB460's
            <code class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded text-xs font-mono">Ac-No</code>
            and
            <code class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded text-xs font-mono">sTime</code>
            columns. (You can also just upload the raw `.txt` file directly from the device).
          </p>
          <a
            :href="route('admin.attendance.download-template')"
            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-green-600 to-emerald-600 rounded-lg hover:from-green-700 hover:to-emerald-700 transition shadow-lg"
          >
            <i class="bi bi-download"></i> Download Template (.xlsx)
          </a>
        </div>
      </div>

      <!-- ===================== Step 2: Upload ===================== -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-amber-600 flex items-center justify-center text-white font-bold">
            2
          </div>
          <div>
            <h3 class="font-bold text-gray-800 dark:text-gray-100">
              Upload Filled File
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              .xlsx / .xls / .csv / .txt
            </p>
          </div>
        </div>

        <form @submit.prevent="submit" class="p-6 space-y-4">
          <!-- Options row -->
          <div class="flex items-center gap-4 flex-wrap text-sm">
            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
              <input
                type="checkbox"
                v-model="hasHeaderRow"
                @change="reparseIfPossible"
                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
              />
              <span class="text-gray-700 dark:text-gray-300">
                File has a header row
              </span>
            </label>
            <span v-if="preview" class="text-xs text-gray-500 dark:text-gray-400">
              Detected delimiter:
              <code class="font-mono">{{ preview.delimiterName }}</code>
            </span>
          </div>

          <!-- Dropzone -->
          <div
            @dragenter.prevent="onDragEnter"
            @dragover.prevent
            @dragleave.prevent="onDragLeave"
            @drop.prevent="handleDrop"
            @click="$refs.fileInput.click()"
            class="border-2 border-dashed rounded-2xl p-8 text-center cursor-pointer transition-all"
            :class="
              isDragging
                ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 scale-[1.01]'
                : selectedFile
                ? 'border-green-400 bg-green-50 dark:bg-green-900/20'
                : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/30 hover:border-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20'
            "
          >
            <input
              ref="fileInput"
              type="file"
              @change="handleFileSelect"
              accept=".xlsx,.xls,.csv,.txt"
              class="hidden"
            />

            <template v-if="!selectedFile">
              <div class="w-16 h-16 mx-auto rounded-full bg-white dark:bg-gray-800 flex items-center justify-center mb-4">
                <i class="bi bi-cloud-arrow-up text-3xl text-indigo-600 dark:text-indigo-400"></i>
              </div>
              <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-1">
                Drop file here, or click to browse
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                Supports .xlsx, .xls, .csv, .txt (max 20MB)
              </p>
            </template>

            <template v-else>
              <div class="w-16 h-16 mx-auto rounded-full bg-white dark:bg-gray-800 flex items-center justify-center mb-4">
                <i class="bi bi-file-earmark-excel text-3xl text-green-600 dark:text-green-400"></i>
              </div>
              <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-1">
                {{ selectedFile.name }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                {{ formatSize(selectedFile.size) }}
              </p>
              <button
                type="button"
                @click.stop="clearFile"
                class="text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium"
              >
                <i class="bi bi-x-circle"></i> Remove file
              </button>
            </template>
          </div>

          <!-- Preview / analysis -->
          <div
            v-if="preview"
            class="rounded-xl border p-4 text-sm"
            :class="
              preview.ok
                ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800'
                : 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800'
            "
          >
            <div class="flex items-start gap-3">
              <i
                class="text-lg shrink-0 mt-0.5"
                :class="
                  preview.ok
                    ? 'bi bi-check-circle-fill text-green-600 dark:text-green-400'
                    : 'bi bi-exclamation-triangle-fill text-amber-600 dark:text-amber-400'
                "
              ></i>
              <div class="flex-1 min-w-0">
                <p
                  class="font-semibold mb-2"
                  :class="
                    preview.ok
                      ? 'text-green-800 dark:text-green-300'
                      : 'text-amber-800 dark:text-amber-300'
                  "
                >
                  {{ preview.ok ? "File looks good" : "Something looks off" }}
                </p>

                <!-- Column mapping -->
                <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                  <div>
                    <span class="text-gray-500 dark:text-gray-400">Employee ID:</span>
                    <code
                      class="ml-1 px-1.5 py-0.5 rounded font-mono"
                      :class="
                        preview.pinColumn
                          ? 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100'
                          : 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300'
                      "
                    >
                      {{ preview.pinColumn || "NOT FOUND" }}
                    </code>
                  </div>
                  <div>
                    <span class="text-gray-500 dark:text-gray-400">Timestamp:</span>
                    <code
                      class="ml-1 px-1.5 py-0.5 rounded font-mono"
                      :class="
                        preview.timeColumn
                          ? 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100'
                          : 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300'
                      "
                    >
                      {{ preview.timeColumn || "NOT FOUND" }}
                    </code>
                  </div>
                </div>

                <!-- Stats -->
                <div class="flex flex-wrap gap-3 text-xs">
                  <span class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400">
                    <i class="bi bi-list-ol"></i>
                    {{ preview.rows }} row{{ preview.rows === 1 ? "" : "s" }}
                  </span>
                  <span
                    v-if="preview.validRows != null"
                    class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400"
                  >
                    <i class="bi bi-check2-all"></i>
                    {{ preview.validRows }} valid
                  </span>
                  <span
                    v-if="preview.invalidRows"
                    class="inline-flex items-center gap-1 text-red-600 dark:text-red-400"
                  >
                    <i class="bi bi-x-octagon"></i>
                    {{ preview.invalidRows }} invalid
                  </span>
                </div>

                <!-- Sample rows -->
                <div v-if="preview.sample?.length" class="mt-3">
                  <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                    First few rows:
                  </p>
                  <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table class="w-full text-xs font-mono">
                      <tbody>
                        <tr
                          v-for="(row, i) in preview.sample"
                          :key="i"
                          class="border-b border-gray-100 dark:border-gray-700 last:border-0"
                        >
                          <td class="px-2 py-1 text-gray-500 dark:text-gray-400 w-8">
                            {{ i + 1 }}
                          </td>
                          <td class="px-2 py-1 text-gray-800 dark:text-gray-100">
                            {{ row }}
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Warnings -->
                <ul
                  v-if="preview.warnings?.length"
                  class="mt-3 space-y-1 text-xs text-amber-700 dark:text-amber-300"
                >
                  <li
                    v-for="(w, i) in preview.warnings"
                    :key="i"
                    class="flex items-start gap-1.5"
                  >
                    <i class="bi bi-info-circle mt-0.5"></i>
                    <span>{{ w }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Upload progress -->
          <div
            v-if="uploading"
            class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-xl p-4"
          >
            <div class="flex items-center gap-3 mb-2">
              <i class="bi bi-arrow-repeat animate-spin text-indigo-600 dark:text-indigo-400 text-xl"></i>
              <div class="flex-1">
                <p class="text-sm font-semibold text-indigo-800 dark:text-indigo-300">
                  Uploading & Processing…
                </p>
                <p class="text-xs text-indigo-600 dark:text-indigo-400">
                  Please wait, importing attendance records
                </p>
              </div>
            </div>
            <div class="h-1.5 w-full bg-indigo-200 dark:bg-indigo-900 rounded-full overflow-hidden">
              <div
                class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 transition-all duration-300"
                :style="{ width: uploadProgress + '%' }"
              ></div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end gap-3 pt-2">
            <Link
              :href="route('admin.attendance.index')"
              class="px-5 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition"
            >
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="!selectedFile || uploading || (preview && !preview.ok)"
              class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 shadow-lg"
            >
              <i v-if="uploading" class="bi bi-arrow-repeat animate-spin"></i>
              <i v-else class="bi bi-cloud-upload"></i>
              {{ uploading ? "Importing…" : "Upload & Import" }}
            </button>
          </div>
        </form>
      </div>

      <!-- ===================== Instructions ===================== -->
      <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-6">
        <h3 class="font-bold text-blue-900 dark:text-blue-300 mb-3 flex items-center gap-2">
          <i class="bi bi-info-circle-fill"></i>
          MB460 Export Instructions
        </h3>
        <ol class="space-y-2 text-sm text-blue-800 dark:text-blue-300 list-decimal list-inside">
          <li>ZKTime.Net / BioTime / the device's own export tool চালু করুন।</li>
          <li>Attendance log export করুন।</li>
          <li>
            File-এ minimum এই দুইটা column থাকতে হবে:
            <strong><code>Ac-No</code></strong> এবং <strong><code>sTime</code></strong>।
          </li>
          <li>
            <code>sTime</code> ফরম্যাট:
            <strong><code>2026-10-07 09:15:42</code></strong>
            (24-hour, hyphen separated)। Raw `.txt` accept করবে `2026-10-07 09:15` ও।
          </li>
          <li>File আপলোড করুন — system auto-detect করবে।</li>
        </ol>

        <div class="mt-4 p-3 bg-white dark:bg-gray-800 rounded-lg border border-blue-200 dark:border-blue-800">
          <p class="text-xs font-semibold text-blue-900 dark:text-blue-300 mb-2">
            Accepted Header Names (auto-detected):
          </p>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
            <div>
              <p class="font-semibold text-blue-800 dark:text-blue-300 mb-1">
                Employee ID column:
              </p>
              <p class="font-mono text-blue-700 dark:text-blue-400 leading-relaxed">
                Ac-No, AcNo, ac_no, AC-No, EnrollNumber, Enroll Number,
                employee_id, employee_code, enroll_number, user_id, pin,
                ssn, id
              </p>
            </div>
            <div>
              <p class="font-semibold text-blue-800 dark:text-blue-300 mb-1">
                Timestamp column:
              </p>
              <p class="font-mono text-blue-700 dark:text-blue-400 leading-relaxed">
                sTime, stime, s_time, timestamp, checktime, check_time,
                date_time, datetime, punch_time, time, Date/Time
              </p>
            </div>
          </div>
        </div>

        <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
          <p class="text-xs font-semibold text-amber-900 dark:text-amber-300 mb-1">
            <i class="bi bi-exclamation-triangle"></i> Important:
          </p>
          <ul class="text-xs text-amber-800 dark:text-amber-400 space-y-1 ml-4 list-disc">
            <li>
              <code>Ac-No</code> must match
              <code>employee_id</code> in your database (e.g. <code>150026</code>).
            </li>
            <li>
              Timestamp format:
              <code>YYYY-MM-DD HH:MM:SS</code> — the parser also accepts
              <code>DD-MMM-YY h:mm:ss A</code> and <code>YYYY-MM-DD HH:MM</code> just in case.
            </li>
            <li>Duplicates (same employee + timestamp) are skipped automatically.</li>
            <li>
              If a row's <code>Ac-No</code> doesn't match any employee, it's
              counted as <em>Unknown</em> and listed in the import summary.
            </li>
          </ul>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import * as XLSX from "xlsx";

/* ========== State ========== */
const selectedFile   = ref(null);
const isDragging     = ref(false);
const uploading      = ref(false);
const uploadProgress = ref(0);
const hasHeaderRow   = ref(false); // Default to false for raw TXT logs
const preview        = ref(null);

const form = useForm({ file: null });

let dragCounter = 0;

/* ========== Computed ========== */
const hasErrors = computed(() => Object.keys(form.errors).length > 0);

/* ========== Alias maps ========== */
const PIN_ALIASES = [
  "Ac-No", "acno", "ac_no", "ac no", "ac-no.", "acno.",
  "enrollnumber", "enroll_number", "enroll no", "enrollno",
  "employee_id", "employeeid", "employee_code", "employee code",
  "user_id", "userid", "pin", "ssn", "id",
];

const TIME_ALIASES = [
  "sTime", "s_time", "s time", "s-time",
  "timestamp", "checktime", "check_time", "check time",
  "datetime", "date_time", "date time", "date/time",
  "punch_time", "punchtime", "punch time",
  "time",
];

/* ========== Drag handlers ========== */
const onDragEnter = () => {
  dragCounter++;
  isDragging.value = true;
};

const onDragLeave = () => {
  dragCounter--;
  if (dragCounter <= 0) {
    dragCounter = 0;
    isDragging.value = false;
  }
};

/* ========== File handlers ========== */
const handleFileSelect = (e) => {
  const f = e.target.files[0];
  if (f) validateAndSetFile(f);
};

const handleDrop = (e) => {
  dragCounter = 0;
  isDragging.value = false;
  const f = e.dataTransfer.files?.[0];
  if (f) validateAndSetFile(f);
};

const validateAndSetFile = async (file) => {
  form.clearErrors();
  preview.value = null;

  if (file.size > 20 * 1024 * 1024) {
    alert("❌ File size exceeds 20MB limit");
    return;
  }

  const ext = file.name.split(".").pop().toLowerCase();
  if (!["xlsx", "xls", "csv", "txt"].includes(ext)) {
    alert("❌ Only .xlsx, .xls, .csv, .txt files are allowed");
    return;
  }

  selectedFile.value = file;
  form.file = file;

  try {
    if (ext === "xlsx" || ext === "xls") {
      preview.value = await analyzeExcelFile(file, hasHeaderRow.value);
    } else {
      const text = await file.text();
      preview.value = analyzeTextFile(text, hasHeaderRow.value);
    }
  } catch (e) {
    console.warn("Preview parse failed:", e);
    preview.value = {
      ok: true,
      rows: null,
      pinColumn: "auto-detected on server",
      timeColumn: "auto-detected on server",
      delimiterName: "unknown",
      warnings: [`Could not preview this file: ${e.message}. The server will validate on import.`],
    };
  }
};

const reparseIfPossible = async () => {
  if (!selectedFile.value) return;
  const ext = selectedFile.value.name.split(".").pop().toLowerCase();

  try {
    if (ext === "xlsx" || ext === "xls") {
      preview.value = await analyzeExcelFile(selectedFile.value, hasHeaderRow.value);
    } else if (ext === "csv" || ext === "txt") {
      const text = await selectedFile.value.text();
      preview.value = analyzeTextFile(text, hasHeaderRow.value);
    }
  } catch (e) {
    console.warn("Reparse failed:", e);
  }
};

const clearFile = () => {
  selectedFile.value = null;
  form.file = null;
  form.clearErrors();
  preview.value = null;
  uploadProgress.value = 0;

  if (document.querySelector("input[type=file]")) {
    document.querySelector("input[type=file]").value = "";
  }
};

/* ========== Excel file analysis ========== */
const analyzeExcelFile = async (file, hasHeader) => {
  const buffer = await file.arrayBuffer();
  const wb = XLSX.read(buffer, { type: "array", cellDates: false });

  const sheetName = wb.SheetNames.find((n) => {
    const ws = wb.Sheets[n];
    return ws && ws["!ref"] && ws["!ref"] !== "A1:A1";
  });

  if (!sheetName) {
    return { ok: false, rows: 0, warnings: ["Workbook has no data."] };
  }

  const ws = wb.Sheets[sheetName];

  const rows = XLSX.utils.sheet_to_json(ws, {
    header: 1,
    raw: false,
    defval: "",
    blankrows: false,
  });

  if (!rows.length) {
    return { ok: false, rows: 0, warnings: [`Sheet "${sheetName}" is empty.`] };
  }

  return analyzeRows(rows, hasHeader, `Sheet "${sheetName}" (Excel)`);
};

/* ========== Text file analysis ========== */
const analyzeTextFile = (rawText, hasHeader) => {
  const lines = rawText
    .split(/\r\n|\r|\n/)
    .map((l) => l.trimEnd())
    .filter((l) => l.length > 0);

  if (lines.length === 0) {
    return { ok: false, rows: 0, warnings: ["File is empty."] };
  }

  const sample = lines[0];
  let delimiter;
  let delimiterName;

  // ✅ FIX: Handles `150023\t\t2026-11-01 08:17` (multiple tabs) and multiple spaces
  if (sample.includes("\t")) {
    delimiter = /\t+/;
    delimiterName = "Tab";
  } else if (sample.includes(",")) {
    delimiter = ",";
    delimiterName = "Comma";
  } else if (sample.includes(";")) {
    delimiter = ";";
    delimiterName = "Semicolon";
  } else if (/\s{2,}/.test(sample)) {
    delimiter = /\s{2,}/;
    delimiterName = "2+ spaces";
  } else {
    delimiter = /\s+/;
    delimiterName = "Single space (best effort)";
  }

  const splitRow = (line) =>
    typeof delimiter === "string"
      ? line.split(delimiter).map((c) => c.trim().replace(/^"|"$/g, ""))
      : line.split(delimiter).map((c) => c.trim());

  const rows = lines.map(splitRow);

  return analyzeRows(rows, hasHeader, delimiterName);
};

/* ========== Shared row analysis (text + Excel) ========== */
const analyzeRows = (rows, hasHeader, sourceLabel) => {
  const header = hasHeader ? rows[0].map((c) => String(c ?? "").trim()) : null;
  const dataRows = hasHeader ? rows.slice(1) : rows;

  let pinIdx = null;
  let timeIdx = null;
  let pinColumnName = null;
  let timeColumnName = null;

  if (header) {
    const normalized = header.map((h) =>
      String(h).toLowerCase().replace(/\s+/g, " ").trim()
    );

    pinIdx  = findColumn(normalized, PIN_ALIASES);
    timeIdx = findColumn(normalized, TIME_ALIASES);

    if (pinIdx !== null)  pinColumnName  = header[pinIdx];
    if (timeIdx !== null) timeColumnName = header[timeIdx];
  } else {
    // No header → assume first column is PIN, second is time
    pinIdx = 0;
    timeIdx = 1;
    pinColumnName = "Column 1";
    timeColumnName = "Column 2";
  }

  let validRows = 0;
  let invalidRows = 0;
  const warnings = [];
  const sampleRows = [];

  for (const row of dataRows) {
    if (!row || !row[pinIdx] || !row[timeIdx]) {
      invalidRows++;
      continue;
    }

    const pin = String(row[pinIdx]).trim();
    const time = String(row[timeIdx]).trim();

    if (pin === "" || time === "") {
      invalidRows++;
      continue;
    }

    // ✅ FIX: Passes `2026-11-01 08:17` (without seconds)
    if (!looksLikeTimestamp(time)) {
      invalidRows++;
      continue;
    }

    validRows++;

    if (sampleRows.length < 3) {
      sampleRows.push(`${pin}  →  ${time}`);
    }
  }

  if (pinIdx === null) {
    warnings.push("Employee ID column not detected. The file will fail server-side validation.");
  }
  if (timeIdx === null) {
    warnings.push("Timestamp column not detected. The file will fail server-side validation.");
  }
  if (invalidRows > 0) {
    warnings.push(`${invalidRows} row(s) could not be parsed — they'll be skipped.`);
  }
  if (!hasHeader && dataRows.length > 0) {
    warnings.push("No header row detected. Assuming column 1 = employee ID, column 2 = timestamp.");
  }

  const ok = pinIdx !== null && timeIdx !== null && validRows > 0;

  return {
    ok,
    rows: dataRows.length,
    validRows,
    invalidRows,
    pinColumn: pinColumnName,
    timeColumn: timeColumnName,
    delimiterName: sourceLabel,
    sample: sampleRows,
    warnings,
  };
};

const findColumn = (headerRow, aliases) => {
  for (let i = 0; i < headerRow.length; i++) {
    if (aliases.includes(headerRow[i])) return i;
  }
  for (let i = 0; i < headerRow.length; i++) {
    for (const a of aliases) {
      if (headerRow[i].includes(a)) return i;
    }
  }
  return null;
};

const looksLikeTimestamp = (s) => {
  // ✅ FIX: Allow "YYYY-MM-DD HH:MM" (without seconds)
  if (/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}(:\d{2})?$/.test(s)) return true;

  if (/^\d{2}-[A-Za-z]{3}-\d{2,4} \d{1,2}:\d{2}(:\d{2})?( [AP]M)?$/.test(s)) return true;
  if (/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}(:\d{2})?$/.test(s)) return true;
  if (/^\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}(:\d{2})?$/.test(s)) return true;

  if (/^4[5-9]\d{3}(\.\d+)?$/.test(s)) return true;

  return false;
};

/* ========== Submit ========== */
const submit = () => {
  if (!selectedFile.value) {
    alert("⚠️ Please select a file first");
    return;
  }

  uploading.value = true;
  uploadProgress.value = 0;
  form.clearErrors();

  form.post(route("admin.attendance.import"), {
    forceFormData: true,
    preserveScroll: true,

    onProgress: (event) => {
      if (event?.lengthComputable && event.total) {
        uploadProgress.value = Math.round((event.loaded / event.total) * 100);
      }
    },

    onSuccess: () => {
      uploading.value = false;
      uploadProgress.value = 100;
      clearFile();
    },

    onError: (errors) => {
      let message = "❌ Import Failed:\n\n";
      Object.entries(errors).forEach(([key, val]) => {
        message += `• ${formatFieldName(key)}:\n  ${val}\n\n`;
      });
      alert(message);
      uploading.value = false;
    },

    onFinish: () => {
      uploading.value = false;
    },
  });
};

/* ========== Helpers ========== */
const formatSize = (bytes) => {
  if (bytes < 1024) return bytes + " B";
  if (bytes < 1048576) return (bytes / 1024).toFixed(2) + " KB";
  return (bytes / 1048576).toFixed(2) + " MB";
};

const formatFieldName = (field) =>
  field.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
</script>
