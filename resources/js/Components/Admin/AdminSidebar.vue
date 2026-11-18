<template>
  <aside
    class="fixed top-0 left-0 h-screen bg-gradient-to-b from-slate-800 to-slate-900 text-white transition-all duration-300 z-50 flex flex-col"
    :class="[
      collapsed ? 'w-20' : 'w-64',
      mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
    ]"
  >
    <!-- Brand -->
    <div
      class="flex items-center justify-between px-4 h-16 border-b border-white/10 shrink-0"
    >
      <Link
        :href="route('admin.dashboard')"
        class="flex items-center gap-2 text-white font-bold text-lg whitespace-nowrap overflow-hidden"
      >
        <i
          class="bi bi-fingerprint text-indigo-400 text-2xl"
          aria-hidden="true"
        ></i>
        <span v-if="!collapsed">Attendance</span>
      </Link>
      <button
        @click="$emit('toggle-collapse')"
        class="hidden lg:flex items-center justify-center w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 transition shrink-0"
        :title="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
        :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
        :aria-expanded="!collapsed"
      >
        <i
          :class="collapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-left'"
          class="text-xs"
          aria-hidden="true"
        ></i>
      </button>
    </div>

    <!-- ==================== User Panel ==================== -->
    <div
      v-if="!collapsed"
      class="flex items-center gap-3 px-4 py-3 border-b border-white/10 shrink-0"
    >
      <div class="relative shrink-0">
        <div
          class="w-10 h-10 rounded-full overflow-hidden bg-white/10 flex items-center justify-center ring-2 ring-indigo-500/30"
        >
          <i
            v-if="imageFailed"
            class="bi bi-person-circle text-2xl text-slate-400"
            aria-hidden="true"
          ></i>
          <img
            v-else
            :src="avatarUrl"
            :alt="userName"
            class="w-full h-full object-cover"
            @error="onImageError"
          />
        </div>
        <span
          class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-slate-900 rounded-full"
          title="Online"
        ></span>
      </div>

      <div class="flex flex-col overflow-hidden">
        <span class="text-white font-semibold text-sm truncate">
          {{ userName }}
        </span>
        <span class="text-slate-400 text-xs truncate">
          {{ userRole }}
        </span>
      </div>
    </div>

    <!-- Collapsed: Show only avatar -->
    <div
      v-else
      class="flex justify-center py-3 border-b border-white/10 shrink-0"
    >
      <div class="relative">
        <div
          class="w-10 h-10 rounded-full overflow-hidden bg-white/10 flex items-center justify-center ring-2 ring-indigo-500/30"
        >
          <i
            v-if="imageFailed"
            class="bi bi-person-circle text-2xl text-slate-400"
            aria-hidden="true"
          ></i>
          <img
            v-else
            :src="avatarUrl"
            :alt="userName"
            class="w-full h-full object-cover"
            @error="onImageError"
          />
        </div>
        <span
          class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-slate-900 rounded-full"
          title="Online"
        ></span>
      </div>
    </div>

    <!-- Navigation -->
    <nav
      class="flex-1 overflow-y-auto py-4 sidebar-scroll"
      aria-label="Main navigation"
    >
      <ul class="space-y-1 px-2">
        <li v-if="!collapsed" class="nav-header">MAIN NAVIGATION</li>

        <SidebarMenuItem
          v-for="item in mainMenu"
          :key="item.key || item.route"
          :item="item"
          :collapsed="collapsed"
          :open="!!openMenus[item.key]"
          @toggle="toggleMenu"
        />

        <template v-if="systemMenu.length">
          <li v-if="!collapsed" class="nav-header mt-4">SYSTEM</li>
          <SidebarMenuItem
            v-for="item in systemMenu"
            :key="item.key || item.route"
            :item="item"
            :collapsed="collapsed"
            :open="!!openMenus[item.key]"
            @toggle="toggleMenu"
          />
        </template>
      </ul>
    </nav>

    <!-- Logout -->
    <div class="border-t border-white/10 p-3 shrink-0">
      <Link
        :href="route('logout')"
        method="post"
        as="button"
        class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-red-400 hover:bg-red-500/10 hover:text-red-300 transition justify-center lg:justify-start"
      >
        <i
          class="bi bi-box-arrow-right text-lg w-5 text-center"
          aria-hidden="true"
        ></i>
        <span v-if="!collapsed" class="text-sm font-medium">Logout</span>
      </Link>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import SidebarMenuItem from "./SidebarMenuItem.vue";

const props = defineProps({
  collapsed: { type: Boolean, default: false },
  mobileOpen: { type: Boolean, default: false },
});

defineEmits(["toggle-collapse", "close-mobile"]);

const page = usePage();

/* ============================================================
 |  User Info
 * ============================================================ */
const userName = computed(() => {
  return page.props.auth?.user?.name || "Admin";
});

const userRole = computed(() => {
  const user = page.props.auth?.user;

  if (user?.roles?.length > 0) {
    return user.roles[0].name || user.roles[0];
  }
  if (user?.designation) return user.designation;
  if (user?.role) return user.role;
  if (user?.email?.includes("admin")) return "Administrator";
  return "Employee";
});

/* ============================================================
 |  Avatar
 * ============================================================ */
const imageFailed = ref(false);

const getAvatarUrl = (name) => {
  const safeName = encodeURIComponent(name || "Admin");
  return `https://ui-avatars.com/api/?name=${safeName}&background=6366f1&color=fff&bold=true&size=128`;
};

const avatarUrl = computed(() => {
  const user = page.props.auth?.user;
  if (user?.avatar_url) return user.avatar_url;
  return getAvatarUrl(userName.value);
});

const onImageError = (event) => {
  if (event.target.src.includes("ui-avatars.com")) {
    imageFailed.value = true;
    return;
  }
  event.target.src = getAvatarUrl(userName.value);
};

/* ============================================================
 |  Route helpers
 * ============================================================ */
const hasRoute = (name) => {
  try {
    route(name);
    return true;
  } catch {
    return false;
  }
};

/* ============================================================
 |  Dropdown state
 * ============================================================ */
const openMenus = ref({
  employees: false,
  organization: false,
  attendance: false,
  payroll: false,
  reports: false,
});

/**
 * Auto-open the group that contains the current route.
 */
const syncOpenMenusWithUrl = () => {
  const url = page.url || "";

  if (url.includes("/admin/employees")) openMenus.value.employees = true;
  if (url.includes("/admin/departments") || url.includes("/admin/designations"))
    openMenus.value.organization = true;
  if (url.includes("/admin/attendance")) openMenus.value.attendance = true;

  // ✅ Ensure Payroll stays open when visiting Off Day Duties
  if (url.includes("/admin/payroll") || url.includes("/admin/off-day-duties")) {
    openMenus.value.payroll = true;
  }

  if (url.includes("/admin/reports")) openMenus.value.reports = true;
};

syncOpenMenusWithUrl();

/* Re-run when navigating between Inertia pages (SPA-style) */
watch(() => page.url, syncOpenMenusWithUrl);

const toggleMenu = (key) => {
  openMenus.value[key] = !openMenus.value[key];
};

/* ============================================================
 |  Menu definition
 * ============================================================ */
const mainMenu = computed(() => [
  {
    key: "dashboard",
    label: "Dashboard",
    icon: "bi bi-speedometer2",
    route: "admin.dashboard",
  },
  {
    key: "employees",
    label: "Employees",
    icon: "bi bi-people",
    route: "admin.employees.index",
    children: [
      { label: "All Employees", route: "admin.employees.index" },
      { label: "Add Employee", route: "admin.employees.create" },
      { label: "Bulk Upload", route: "admin.employees.bulk-upload" },
      { label: "Analytics", route: "admin.employees.analytics" },
    ],
  },
  {
    key: "salary",
    label: "Salary Structure",
    icon: "bi bi-cash-stack",
    route: "admin.salary.index",
  },
  {
    key: "organization",
    label: "Organization",
    icon: "bi bi-diagram-3",
    route: "admin.departments.index",
    children: [
      { label: "Departments", route: "admin.departments.index" },
      { label: "Designations", route: "admin.designations.index" },
    ],
  },
  {
    key: "attendance",
    label: "Attendance",
    icon: "bi bi-calendar-check",
    route: "admin.attendance.index",
    children: [
      { label: "All Records", route: "admin.attendance.index" },
      { label: "Today's Attendance", route: "admin.attendance.today" },
      { label: "Bulk Upload", route: "admin.attendance.bulk-upload" },
    ],
  },
  {
    key: "payroll",
    label: "Payroll",
    icon: "bi bi-cash-coin",
    route: "admin.payroll.index",
    children: [
      { label: "All Payrolls", route: "admin.payroll.index" },
      { label: "Off Day Entry", route: "admin.off-day-duties.index" },
    ],
  },
  {
    key: "devices",
    label: "ZKTeco Devices",
    icon: "bi bi-router",
    route: "admin.devices.index",
  },
  {
    key: "reports",
    label: "Reports",
    icon: "bi bi-file-earmark-bar-graph",
    route: "admin.reports.attendance",
    children: [
      { label: "Attendance Report", route: "admin.reports.attendance" },
      { label: "Payroll Report", route: "admin.reports.payroll" },
      { label: "Employee Report", route: "admin.reports.employees" },
    ],
  },
]);

/* ============================================================
 |  SYSTEM section
 * ============================================================ */
const systemMenu = computed(() => {
  const items = [];

  if (hasRoute("admin.settings.user.index")) {
    items.push({
      key: "user-settings",
      label: "User Settings",
      icon: "bi bi-person-gear",
      route: "admin.settings.user.index",
    });
  }

  if (hasRoute("admin.backups.index")) {
    items.push({
      key: "backups",
      label: "Backups",
      icon: "bi bi-archive-fill",
      route: "admin.backups.index",
    });
  }

  if (
    hasRoute("admin.settings.index") &&
    page.props.auth?.user?.roles?.includes("Admin")
  ) {
    items.push({
      key: "settings",
      label: "Settings",
      icon: "bi bi-gear",
      route: "admin.settings.index",
    });
  }

  return items;
});
</script>

<style scoped>
.nav-header {
  padding: 0.75rem 0.75rem 0.25rem;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #64748b;
  letter-spacing: 1px;
  list-style: none;
}

.sidebar-scroll::-webkit-scrollbar {
  width: 6px;
}

.sidebar-scroll::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.03);
}

.sidebar-scroll::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.15);
  border-radius: 3px;
}

.sidebar-scroll::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.25);
}
</style>
