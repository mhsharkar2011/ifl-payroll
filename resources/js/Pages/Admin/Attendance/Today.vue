<template>
  <AdminLayout page-title="Today's Attendance">
    <Head :title="`Attendance — ${todayDate}`" />

    <div class="space-y-6">
      <!-- ==================== HEADER ==================== -->
      <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
          <Link
            :href="route('admin.attendance.index')"
            class="w-10 h-10 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-700 transition"
          >
            <i class="bi bi-arrow-left text-gray-600 dark:text-gray-300"></i>
          </Link>
          <div>
            <div class="flex items-center gap-2">
              <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                Today's Attendance
              </h1>
              <!-- Live indicator -->
              <span
                class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide"
                :class="
                  isLive
                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                    : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'
                "
                :title="isLive ? 'Auto-refreshing every 60s' : 'Auto-refresh paused'"
              >
                <span
                  class="w-1.5 h-1.5 rounded-full"
                  :class="isLive ? 'bg-green-500 animate-pulse' : 'bg-gray-400'"
                ></span>
                {{ isLive ? "Live" : "Paused" }}
              </span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              {{ todayDate }}
              <span v-if="lastSyncedAt" class="ml-2 text-xs">
                • Last synced {{ lastSyncedAt }}
              </span>
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <!-- View toggle -->
          <div
            class="inline-flex items-center bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-1"
          >
            <button
              @click="viewMode = 'grid'"
              class="px-3 py-1.5 text-xs font-semibold rounded-md transition"
              :class="
                viewMode === 'grid'
                  ? 'bg-indigo-600 text-white'
                  : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
              "
            >
              <i class="bi bi-grid-3x3-gap-fill mr-1"></i> Grid
            </button>
            <button
              @click="viewMode = 'table'"
              class="px-3 py-1.5 text-xs font-semibold rounded-md transition"
              :class="
                viewMode === 'table'
                  ? 'bg-indigo-600 text-white'
                  : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
              "
            >
              <i class="bi bi-list-ul mr-1"></i> Table
            </button>
          </div>

          <!-- Refresh (pull from DB, no device push) -->
          <button
            @click="refreshData"
            :disabled="loading || syncing"
            class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition disabled:opacity-50"
            title="Reload from database"
          >
            <i
              class="bi bi-arrow-clockwise"
              :class="{ 'animate-spin': loading }"
            ></i>
            Refresh
          </button>

          <Link
            :href="route('admin.attendance.bulk-upload')"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition"
          >
            <i class="bi bi-cloud-upload"></i> Bulk Upload
          </Link>

          <!-- Sync from devices -->
          <button
            @click="syncFromDevices"
            :disabled="syncing"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition shadow-lg disabled:opacity-50"
          >
            <i
              class="bi bi-arrow-repeat"
              :class="{ 'animate-spin': syncing }"
            ></i>
            {{ syncButtonLabel }}
          </button>
        </div>
      </div>

      <!-- ==================== SYNC STATUS BAR ==================== -->
      <Transition
        enter-active-class="transition duration-200"
        enter-from-class="opacity-0 -translate-y-1"
        leave-active-class="transition duration-150"
        leave-to-class="opacity-0"
      >
        <div
          v-if="syncState !== 'idle'"
          class="rounded-xl border p-4 flex items-start gap-3"
          :class="syncStatusClass"
        >
          <i
            class="text-xl mt-0.5"
            :class="syncStatusIcon"
          ></i>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold">{{ syncStatusTitle }}</p>
            <p class="text-xs mt-0.5 opacity-90">{{ syncStatusMessage }}</p>
            <!-- Device breakdown -->
            <ul
              v-if="syncDevices.length"
              class="mt-2 space-y-0.5 text-xs opacity-90"
            >
              <li
                v-for="d in syncDevices"
                :key="d.id || d.name"
                class="flex items-center gap-1.5"
              >
                <i
                  class="bi"
                  :class="
                    d.ok
                      ? 'bi-check-circle-fill text-green-500'
                      : 'bi-x-circle-fill text-red-500'
                  "
                ></i>
                <span class="font-medium">{{ d.name || d.ip }}</span>
                <span v-if="d.message" class="opacity-75">— {{ d.message }}</span>
              </li>
            </ul>
            <div
              v-if="syncState === 'pushing'"
              class="mt-2 h-1 w-full bg-white/30 rounded-full overflow-hidden"
            >
              <div
                class="h-full bg-white/80 rounded-full transition-all duration-300"
                :style="{ width: syncProgress + '%' }"
              ></div>
            </div>
          </div>
          <button
            v-if="syncState === 'done' || syncState === 'error'"
            @click="dismissSyncStatus"
            class="text-current opacity-60 hover:opacity-100 transition"
          >
            <i class="bi bi-x-lg text-sm"></i>
          </button>
        </div>
      </Transition>

      <!-- ==================== FLASH ==================== -->
      <div
        v-if="$page.props.flash?.success"
        class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 flex items-center gap-3"
      >
        <i class="bi bi-check-circle-fill text-green-500 text-xl"></i>
        <p class="text-sm text-green-700 dark:text-green-300 font-medium">
          {{ $page.props.flash.success }}
        </p>
      </div>

      <!-- ==================== STATS ==================== -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div
          class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-5 text-white shadow-lg shadow-green-500/30"
        >
          <div class="flex items-center justify-between mb-3">
            <i class="bi bi-check-circle-fill text-3xl opacity-90"></i>
            <span class="text-xs font-semibold bg-white/20 px-2 py-1 rounded-full">
              Check In
            </span>
          </div>
          <p class="text-xs uppercase font-semibold opacity-90 mb-1">
            Total Check-Ins
          </p>
          <p class="text-3xl font-bold">{{ checkInCount }}</p>
        </div>

        <div
          class="bg-gradient-to-br from-red-500 to-rose-600 rounded-2xl p-5 text-white shadow-lg shadow-red-500/30"
        >
          <div class="flex items-center justify-between mb-3">
            <i class="bi bi-box-arrow-right text-3xl opacity-90"></i>
            <span class="text-xs font-semibold bg-white/20 px-2 py-1 rounded-full">
              Check Out
            </span>
          </div>
          <p class="text-xs uppercase font-semibold opacity-90 mb-1">
            Total Check-Outs
          </p>
          <p class="text-3xl font-bold">{{ checkOutCount }}</p>
        </div>

        <div
          class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-5 text-white shadow-lg shadow-indigo-500/30"
        >
          <div class="flex items-center justify-between mb-3">
            <i class="bi bi-people-fill text-3xl opacity-90"></i>
            <span class="text-xs font-semibold bg-white/20 px-2 py-1 rounded-full">
              Unique
            </span>
          </div>
          <p class="text-xs uppercase font-semibold opacity-90 mb-1">
            Employees Present
          </p>
          <p class="text-3xl font-bold">{{ uniqueEmployees }}</p>
        </div>
      </div>

      <!-- ==================== RECORDS ==================== -->
      <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden"
      >
        <!-- Toolbar -->
        <div
          class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between flex-wrap gap-3"
        >
          <div class="flex items-center gap-3">
            <div
              class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center"
            >
              <i class="bi bi-calendar-check text-indigo-600 dark:text-indigo-400"></i>
            </div>
            <div>
              <h3 class="font-bold text-gray-800 dark:text-gray-100">
                Today's Records
              </h3>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                Total: {{ attendances?.total || 0 }} records
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2 flex-wrap">
            <!-- Search -->
            <div class="relative">
              <i
                class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"
              ></i>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search employee..."
                class="pl-9 pr-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none w-56"
              />
            </div>

            <input
              ref="fileInput"
              type="file"
              accept=".txt,.csv,.xlsx,.xls"
              class="hidden"
              @change="handleUpload"
            />
            <button
              @click="fileInput.click()"
              class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition"
            >
              <i class="bi bi-upload"></i> Import Log File
            </button>
          </div>
        </div>

        <!-- GRID VIEW -->
        <div v-if="viewMode === 'grid'" class="p-4">
          <div
            class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3"
          >
            <div
              v-for="attendance in filteredAttendances"
              :key="attendance.id"
              class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-300"
            >
              <div
                class="h-14 relative"
                :class="getCardHeaderGradient(attendance.status)"
              >
                <span
                  class="absolute top-2 right-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold backdrop-blur-sm bg-white/30 text-white"
                >
                  <i
                    :class="getStatusIcon(attendance.status)"
                    class="text-[8px]"
                  ></i>
                  {{ formatStatus(attendance.status) }}
                </span>
              </div>

              <div class="flex justify-center -mt-8 relative z-10">
                <div class="relative">
                  <div
                    class="w-16 h-16 rounded-full overflow-hidden ring-3 ring-white dark:ring-gray-800 bg-gray-100 dark:bg-gray-700 shadow-md"
                  >
                    <!-- ✅ Use the actual avatar_url or fallback -->
                    <EmployeeAvatar
                      :employee="{
                        first_name: attendance.employee?.first_name,
                        last_name: attendance.employee?.last_name,
                        avatar_url: attendance.employee?.avatar_url
                      }"
                      size="lg"
                    />
                  </div>
                  <span
                    class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full ring-2 ring-white dark:ring-gray-800 flex items-center justify-center"
                    :class="getVerifyBg(attendance.verified_by)"
                    :title="getVerifyLabel(attendance.verified_by)"
                  >
                    <i
                      :class="getVerifyIcon(attendance.verified_by)"
                      class="text-[10px]"
                    ></i>
                  </span>
                </div>
              </div>

              <div class="px-3 pt-2 pb-3 text-center">
                <h3
                  class="font-bold text-gray-800 dark:text-gray-100 text-xs truncate"
                  :title="getEmployeeName(attendance)"
                >
                  {{ getEmployeeName(attendance) }}
                </h3>
                <p
                  class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5 truncate"
                >
                  {{ attendance.employee?.employee_id || "-" }}
                </p>

                <div
                  class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700"
                >
                  <p
                    class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-medium"
                  >
                    Time
                  </p>
                  <p
                    class="text-xs font-bold text-gray-800 dark:text-gray-100 mt-0.5"
                  >
                    {{ formatTime(attendance.timestamp) }}
                  </p>
                </div>
              </div>

              <div
                class="px-2 py-1.5 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex items-center justify-center gap-0.5"
              >
                <Link
                  :href="route('admin.attendance.show', attendance.id)"
                  class="flex-1 inline-flex items-center justify-center gap-1 px-1.5 py-1.5 text-[10px] font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 rounded hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 transition"
                  title="View"
                >
                  <i class="bi bi-eye text-[10px]"></i>
                  <span class="hidden xl:inline">View</span>
                </Link>
                <Link
                  :href="route('admin.attendance.edit', attendance.id)"
                  class="flex-1 inline-flex items-center justify-center gap-1 px-1.5 py-1.5 text-[10px] font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 rounded hover:bg-amber-50 dark:hover:bg-amber-900/30 hover:text-amber-600 transition"
                  title="Edit"
                >
                  <i class="bi bi-pencil text-[10px]"></i>
                  <span class="hidden xl:inline">Edit</span>
                </Link>
                <button
                  @click="confirmDelete(attendance)"
                  class="inline-flex items-center justify-center w-7 h-7 text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-700 rounded hover:bg-red-50 dark:hover:bg-red-900/30 hover:text-red-600 transition"
                  title="Delete"
                >
                  <i class="bi bi-trash text-[10px]"></i>
                </button>
              </div>
            </div>

            <div
              v-if="!filteredAttendances.length"
              class="col-span-full p-16 text-center"
            >
              <div class="flex flex-col items-center gap-3">
                <div
                  class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center"
                >
                  <i class="bi bi-inbox text-3xl text-gray-400"></i>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-700 dark:text-gray-200">
                    {{ searchQuery ? "No matching records" : "No attendance yet today" }}
                  </h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{
                      searchQuery
                        ? "Try a different search term"
                        : "Records will appear as employees check in"
                    }}
                  </p>
                </div>
                <button
                  v-if="!searchQuery"
                  @click="syncFromDevices"
                  class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition"
                >
                  <i class="bi bi-arrow-repeat"></i> Sync from ZKTeco
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- TABLE VIEW -->
        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead
              class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 uppercase text-xs"
            >
              <tr>
                <th class="px-6 py-3 text-left font-semibold">Employee</th>
                <th class="px-6 py-3 text-left font-semibold">Time</th>
                <th class="px-6 py-3 text-left font-semibold">Status</th>
                <th class="px-6 py-3 text-left font-semibold">Verified</th>
                <th class="px-6 py-3 text-right font-semibold">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-if="loading">
                <td colspan="5" class="px-6 py-12 text-center">
                  <div
                    class="inline-block w-8 h-8 border-3 border-indigo-600 border-t-transparent rounded-full animate-spin"
                  ></div>
                </td>
              </tr>

              <tr
                v-else
                v-for="attendance in filteredAttendances"
                :key="attendance.id"
                class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition"
              >
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="shrink-0">
                      <!-- ✅ Use the actual avatar_url or fallback -->
                      <EmployeeAvatar
                        :employee="{
                          first_name: attendance.employee?.first_name,
                          last_name: attendance.employee?.last_name,
                          avatar_url: attendance.employee?.avatar_url
                        }"
                        size="md"
                      />
                    </div>
                    <div class="min-w-0">
                      <p
                        class="font-semibold text-gray-800 dark:text-gray-100 truncate"
                      >
                        {{ getEmployeeName(attendance) }}
                      </p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ attendance.employee?.employee_id || "-" }}
                      </p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="font-medium text-gray-800 dark:text-gray-100">
                    {{ formatTime(attendance.timestamp) }}
                  </div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatDate(attendance.timestamp) }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold"
                    :class="getStatusClass(attendance.status)"
                  >
                    <i
                      :class="getStatusIcon(attendance.status)"
                      class="text-[10px]"
                    ></i>
                    {{ formatStatus(attendance.status) }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div
                    class="w-8 h-8 rounded-lg flex items-center justify-center"
                    :class="getVerifyBg(attendance.verified_by)"
                    :title="getVerifyLabel(attendance.verified_by)"
                  >
                    <i
                      :class="getVerifyIcon(attendance.verified_by)"
                      class="text-sm"
                    ></i>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center justify-end gap-1">
                    <Link
                      :href="route('admin.attendance.show', attendance.id)"
                      class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 transition"
                      title="View"
                    >
                      <i class="bi bi-eye"></i>
                    </Link>
                    <Link
                      :href="route('admin.attendance.edit', attendance.id)"
                      class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-amber-50 dark:hover:bg-amber-900/30 hover:text-amber-600 transition"
                      title="Edit"
                    >
                      <i class="bi bi-pencil"></i>
                    </Link>
                    <button
                      @click="confirmDelete(attendance)"
                      class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-red-50 dark:hover:bg-red-900/30 hover:text-red-600 transition"
                      title="Delete"
                    >
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="!loading && !filteredAttendances.length">
                <td colspan="5" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center gap-3">
                    <div
                      class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center"
                    >
                      <i class="bi bi-inbox text-3xl text-gray-400"></i>
                    </div>
                    <div>
                      <h3 class="font-semibold text-gray-700 dark:text-gray-200">
                        {{ searchQuery ? "No matching records" : "No attendance yet today" }}
                      </h3>
                      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{
                          searchQuery
                            ? "Try a different search term"
                            : "Records will appear as employees check in"
                        }}
                      </p>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div
          v-if="attendances?.links && attendances.links.length > 3"
          class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between flex-wrap gap-3"
        >
          <div class="text-sm text-gray-500 dark:text-gray-400">
            Showing
            <span class="font-semibold text-gray-700 dark:text-gray-200">{{
              attendances.from
            }}</span>
            to
            <span class="font-semibold text-gray-700 dark:text-gray-200">{{
              attendances.to
            }}</span>
            of
            <span class="font-semibold text-gray-700 dark:text-gray-200">{{
              attendances.total
            }}</span>
          </div>
          <div class="flex items-center gap-1 flex-wrap">
            <template v-for="(link, index) in attendances.links" :key="index">
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
      <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6"
      >
        <div class="flex items-start gap-4 mb-5">
          <div
            class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0"
          >
            <i
              class="bi bi-trash3-fill text-red-600 dark:text-red-400 text-xl"
            ></i>
          </div>
          <div class="flex-1">
            <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg">
              Delete Attendance Record?
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              Record for
              <strong>{{ getEmployeeName(selectedAttendance) }}</strong> at
              <strong>{{ formatTime(selectedAttendance?.timestamp) }}</strong>.
              This action cannot be undone.
            </p>
          </div>
        </div>
        <div class="flex justify-end gap-2">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition"
          >
            Cancel
          </button>
          <button
            @click="deleteAttendance"
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
  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import EmployeeAvatar from "@/Components/EmployeeAvatar.vue"; // ✅ Import the new component
import axios from "axios";

/* ========== Props ========== */
const props = defineProps({
  attendances: {
    type: Object,
    default: () => ({ data: [], links: [], total: 0, from: 0, to: 0 }),
  },
  stats: {
    type: Object,
    default: () => ({ check_in: 0, check_out: 0, unique_employees: 0 }),
  },
});

/* ========== View Mode ========== */
const viewMode = ref(localStorage.getItem("attendanceViewMode") || "grid");
watch(viewMode, (val) => localStorage.setItem("attendanceViewMode", val));

/* ========== State ========== */
const syncing = ref(false);
const loading = ref(false);
const deleting = ref(false);
const fileInput = ref(null);
const showDeleteModal = ref(false);
const selectedAttendance = ref(null);
const searchQuery = ref("");
const lastSyncedAt = ref(null);
const isLive = ref(true);

/* Sync state machine: idle | syncing | pushing | pulling | done | error */
const syncState = ref("idle");
const syncProgress = ref(0);
const syncMessage = ref("");
const syncDevices = ref([]);

let autoRefreshTimer = null;
let pushPollTimer = null;

/* ========== Computed ========== */
const todayDate = computed(() =>
  new Date().toLocaleDateString("en-GB", {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
  })
);

const checkInCount = computed(() => props.stats?.check_in ?? 0);
const checkOutCount = computed(() => props.stats?.check_out ?? 0);
const uniqueEmployees = computed(() => props.stats?.unique_employees ?? 0);

const syncButtonLabel = computed(() => {
  switch (syncState.value) {
    case "syncing":
      return "Syncing…";
    case "pushing":
      return "Waiting for devices…";
    case "pulling":
      return "Loading records…";
    default:
      return "Sync Now";
  }
});

const syncStatusClass = computed(() => {
  switch (syncState.value) {
    case "error":
      return "bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-300";
    case "done":
      return "bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-700 dark:text-green-300";
    default:
      return "bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300";
  }
});

const syncStatusIcon = computed(() => {
  switch (syncState.value) {
    case "error":
      return "bi bi-exclamation-triangle-fill";
    case "done":
      return "bi bi-check-circle-fill";
    case "syncing":
    case "pushing":
    case "pulling":
      return "bi bi-arrow-repeat animate-spin";
    default:
      return "bi bi-info-circle-fill";
  }
});

const syncStatusTitle = computed(() => {
  switch (syncState.value) {
    case "syncing":
      return "Sending sync command to devices…";
    case "pushing":
      return "Waiting for devices to push attendance…";
    case "pulling":
      return "Loading latest records…";
    case "done":
      return "Sync complete";
    case "error":
      return "Sync failed";
    default:
      return "";
  }
});

const syncStatusMessage = computed(() => syncMessage.value);

/* Client-side search filter (applies to current page) */
const filteredAttendances = computed(() => {
  const list = props.attendances?.data || [];
  const q = searchQuery.value.trim().toLowerCase();
  if (!q) return list;

  return list.filter((a) => {
    const name = getEmployeeName(a).toLowerCase();
    const empId = (a.employee?.employee_id || "").toLowerCase();
    const dept = (a.employee?.department?.name || "").toLowerCase();
    return name.includes(q) || empId.includes(q) || dept.includes(q);
  });
});

/* ========== Auto Refresh ========== */
const startAutoRefresh = () => {
  stopAutoRefresh();
  autoRefreshTimer = setInterval(() => {
    if (!isLive.value) return;
    if (document.hidden) return;
    refreshData({ silent: true });
  }, 60_000); // every 60s
};

const stopAutoRefresh = () => {
  if (autoRefreshTimer) {
    clearInterval(autoRefreshTimer);
    autoRefreshTimer = null;
  }
};

const handleVisibilityChange = () => {
  isLive.value = !document.hidden;
  if (!document.hidden) {
    // Tab just became active — refresh once
    refreshData({ silent: true });
  }
};

onMounted(() => {
  startAutoRefresh();
  document.addEventListener("visibilitychange", handleVisibilityChange);
});

onBeforeUnmount(() => {
  stopAutoRefresh();
  if (pushPollTimer) clearTimeout(pushPollTimer);
  document.removeEventListener("visibilitychange", handleVisibilityChange);
});

/* ========== Refresh (DB only, no device push) ========== */
const refreshData = ({ silent = false } = {}) => {
  if (loading.value) return;

  loading.value = true;

  router.reload({
    only: ["attendances", "stats"],
    preserveScroll: true,
    preserveState: true,
    onFinish: () => {
      loading.value = false;
      lastSyncedAt.value = new Date().toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
      });
      if (!silent) {
        // brief visual feedback
      }
    },
  });
};

/* ========== Sync From Devices ========== */
const syncFromDevices = async () => {
  if (syncing.value) return;

  syncing.value = true;
  syncState.value = "syncing";
  syncProgress.value = 10;
  syncMessage.value = "Contacting attendance devices…";
  syncDevices.value = [];

  try {
    // 1) Send sync command
    const { data } = await axios.post("/admin/attendance/sync");

    if (!data.success) {
      syncState.value = "error";
      syncMessage.value = data.message || "Device sync command failed.";
      return;
    }

    syncDevices.value = (data.devices || []).map((d) => ({
      id: d.id,
      name: d.name || d.ip,
      ip: d.ip,
      ok: d.ok !== false,
      message: d.message || "",
    }));

    syncProgress.value = 35;
    syncState.value = "pushing";
    syncMessage.value =
      "Device received the command and will push new records shortly.";

    // 2) Poll for pushed records
    await pollForPushedRecords();

    // 3) Reload data
    syncState.value = "pulling";
    syncProgress.value = 90;
    syncMessage.value = "Loading latest records from server…";

    await new Promise((resolve) => {
      router.reload({
        only: ["attendances", "stats"],
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
          lastSyncedAt.value = new Date().toLocaleTimeString("en-US", {
            hour: "2-digit",
            minute: "2-digit",
          });
          resolve();
        },
      });
    });

    syncProgress.value = 100;
    syncState.value = "done";
    syncMessage.value = "Attendance records are up to date.";
  } catch (error) {
    console.error("Sync error:", error);
    syncState.value = "error";
    syncMessage.value =
      error.response?.data?.message ||
      error.message ||
      "Unknown error occurred while syncing.";
  } finally {
    syncing.value = false;
  }
};

/* Poll for newly pushed records (max ~15s) */
const pollForPushedRecords = () => {
  return new Promise((resolve) => {
    const startedAt = Date.now();
    const maxMs = 15_000;
    let attempts = 0;

    const tick = async () => {
      attempts++;
      syncProgress.value = Math.min(35 + attempts * 5, 80);
      syncMessage.value = `Waiting for devices… (${attempts})`;

      try {
        const { data } = await axios.get("/admin/attendance/sync-status", {
          params: { since: startedAt },
        });

        if (data.recent_attendance > 0) {
          return resolve();
        }
      } catch (e) {
        // ignore — keep polling
      }

      if (Date.now() - startedAt >= maxMs) {
        return resolve(); // timeout — proceed with reload
      }

      pushPollTimer = setTimeout(tick, 2500);
    };

    tick();
  });
};

const dismissSyncStatus = () => {
  syncState.value = "idle";
  syncProgress.value = 0;
  syncMessage.value = "";
  syncDevices.value = [];
};

/* ========== Delete ========== */
const confirmDelete = (attendance) => {
  selectedAttendance.value = attendance;
  showDeleteModal.value = true;
};

const deleteAttendance = () => {
  if (!selectedAttendance.value) return;

  deleting.value = true;

  router.post(
    route("admin.attendance.destroy", selectedAttendance.value.id),
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        showDeleteModal.value = false;
        selectedAttendance.value = null;
        router.reload({ only: ["attendances", "stats"] });
      },
      onFinish: () => {
        deleting.value = false;
      },
    }
  );
};

/* ========== Helpers ========== */
const getEmployeeName = (attendance) => {
  if (!attendance?.employee) return "Unknown";
  return (
    `${attendance.employee.first_name || ""} ${
      attendance.employee.last_name || ""
    }`.trim() || "Unknown"
  );
};

const formatTime = (timestamp) => {
  if (!timestamp) return "-";
  return new Date(timestamp).toLocaleTimeString("en-US", {
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  });
};

const formatDate = (timestamp) => {
  if (!timestamp) return "-";
  return new Date(timestamp).toLocaleDateString("en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
};

const formatStatus = (status) => {
  const map = {
    check_in: "Check In",
    check_out: "Check Out",
    break_in: "Break In",
    break_out: "Break Out",
    overtime_in: "OT In",
    overtime_out: "OT Out",
  };
  return map[status] || status;
};

const getCardHeaderGradient = (status) => {
  const map = {
    check_in: "bg-gradient-to-r from-green-500 to-emerald-600",
    check_out: "bg-gradient-to-r from-red-500 to-rose-600",
    break_in: "bg-gradient-to-r from-amber-500 to-orange-500",
    break_out: "bg-gradient-to-r from-amber-500 to-orange-500",
    overtime_in: "bg-gradient-to-r from-indigo-500 to-purple-600",
    overtime_out: "bg-gradient-to-r from-indigo-500 to-purple-600",
  };
  return map[status] || "bg-gradient-to-r from-gray-500 to-gray-600";
};

const getStatusClass = (status) => {
  const map = {
    check_in:
      "bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 ring-1 ring-green-200 dark:ring-green-800",
    check_out:
      "bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 ring-1 ring-red-200 dark:ring-red-800",
    break_in:
      "bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-800",
    break_out:
      "bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-800",
    overtime_in:
      "bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 ring-1 ring-indigo-200 dark:ring-indigo-800",
    overtime_out:
      "bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 ring-1 ring-indigo-200 dark:ring-indigo-800",
  };
  return (
    map[status] ||
    "bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 ring-1 ring-gray-200 dark:ring-gray-600"
  );
};

const getStatusIcon = (status) => {
  const map = {
    check_in: "bi bi-box-arrow-in-right",
    check_out: "bi bi-box-arrow-right",
    break_in: "bi bi-cup-hot",
    break_out: "bi bi-cup-hot-fill",
    overtime_in: "bi bi-clock-history",
    overtime_out: "bi bi-clock-fill",
  };
  return map[status] || "bi bi-circle";
};

const getVerifyBg = (type) => {
  const map = {
    0: "bg-gray-500 text-white",
    1: "bg-indigo-500 text-white",
    2: "bg-purple-500 text-white",
    3: "bg-blue-500 text-white",
  };
  return map[type] || "bg-gray-500 text-white";
};

const getVerifyIcon = (type) => {
  const map = {
    0: "bi bi-person-badge",
    1: "bi bi-fingerprint",
    2: "bi bi-person-bounding-box",
    3: "bi bi-credit-card-2-front",
  };
  return map[type] || "bi bi-question-circle";
};

const getVerifyLabel = (type) => {
  const map = {
    0: "Manual/Password",
    1: "Fingerprint",
    2: "Face Recognition",
    3: "Card",
  };
  return map[type] || "Unknown";
};

/* ========== File Upload ========== */
const handleUpload = (e) => {
  const file = e.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append("file", file);

  router.post(route("admin.attendance.import"), formData, {
    onSuccess: () => {
      if (fileInput.value) fileInput.value.value = "";
      router.reload({ only: ["attendances", "stats"] });
    },
    onError: (errors) => {
      alert(errors.error || errors.file || "Import failed");
    },
  });
};
</script>
