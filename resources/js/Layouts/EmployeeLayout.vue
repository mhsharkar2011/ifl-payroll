<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Sidebar -->
        <aside
            class="fixed top-0 left-0 h-screen bg-gray-900 text-white transition-all duration-300 z-50 flex flex-col"
            :class="[
                sidebarCollapsed ? 'w-20' : 'w-64',
                mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
        >
            <!-- Brand -->
            <div class="flex items-center justify-between px-4 h-16 border-b border-gray-800 shrink-0">
                <Link
                    :href="route('employee.dashboard')"
                    class="flex items-center gap-2 text-white font-bold text-lg whitespace-nowrap overflow-hidden"
                >
                    <i class="bi bi-person-badge text-indigo-400 text-2xl"></i>
                    <span v-if="!sidebarCollapsed">Employee</span>
                </Link>
                <button
                    @click="toggleSidebar"
                    class="hidden lg:flex items-center justify-center w-8 h-8 rounded-full bg-gray-800 hover:bg-gray-700 transition"
                >
                    <i :class="sidebarCollapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-left'" class="text-xs"></i>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-1 px-2">
                    <li>
                        <Link
                            :href="route('employee.dashboard')"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition"
                            :class="isActive('employee.dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'"
                        >
                            <i class="bi bi-speedometer2 text-lg w-5 text-center"></i>
                            <span v-if="!sidebarCollapsed" class="text-sm font-medium">Dashboard</span>
                        </Link>
                    </li>
                    <li>
                        <Link
                            :href="route('employee.attendance')"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition"
                            :class="isActive('employee.attendance') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'"
                        >
                            <i class="bi bi-calendar-check text-lg w-5 text-center"></i>
                            <span v-if="!sidebarCollapsed" class="text-sm font-medium">My Attendance</span>
                        </Link>
                    </li>
                    <li>
                        <Link
                            :href="route('employee.payroll')"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition"
                            :class="isActive('employee.payroll') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'"
                        >
                            <i class="bi bi-cash-coin text-lg w-5 text-center"></i>
                            <span v-if="!sidebarCollapsed" class="text-sm font-medium">My Payslips</span>
                        </Link>
                    </li>
                    <li>
                        <Link
                            :href="route('employee.profile')"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition"
                            :class="isActive('employee.profile') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'"
                        >
                            <i class="bi bi-person text-lg w-5 text-center"></i>
                            <span v-if="!sidebarCollapsed" class="text-sm font-medium">My Profile</span>
                        </Link>
                    </li>
                </ul>
            </nav>

            <!-- Logout -->
            <div class="border-t border-gray-800 p-3 shrink-0">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-red-400 hover:bg-red-500/10 hover:text-red-300 transition"
                >
                    <i class="bi bi-box-arrow-right text-lg w-5 text-center"></i>
                    <span v-if="!sidebarCollapsed" class="text-sm font-medium">Logout</span>
                </Link>
            </div>
        </aside>

        <!-- Mobile Overlay -->
        <div
            v-if="mobileOpen"
            class="fixed inset-0 bg-black/50 z-40 lg:hidden"
            @click="mobileOpen = false"
        ></div>

        <!-- Main Content -->
        <main
            class="transition-all duration-300 min-h-screen flex flex-col"
            :class="sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64'"
        >
            <!-- Top Navbar -->
            <header class="bg-white shadow-sm sticky top-0 z-30">
                <div class="flex items-center justify-between px-4 sm:px-6 h-16">
                    <div class="flex items-center gap-3">
                        <button
                            @click="mobileOpen = !mobileOpen"
                            class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition"
                        >
                            <i class="bi bi-list text-2xl text-gray-700"></i>
                        </button>
                        <h1 class="text-lg font-semibold text-gray-800">{{ pageTitle }}</h1>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="relative" v-click-outside="() => userMenuOpen = false">
                            <button
                                @click="userMenuOpen = !userMenuOpen"
                                class="flex items-center gap-2 p-1 pr-3 rounded-lg hover:bg-gray-100 transition"
                            >
                                <img
                                    :src="userAvatar"
                                    alt="Avatar"
                                    class="w-8 h-8 rounded-full ring-2 ring-indigo-100"
                                />
                                <span class="hidden sm:block text-sm font-medium text-gray-700">
                                    {{ $page.props.auth?.user?.name || 'Employee' }}
                                </span>
                                <i class="bi bi-chevron-down text-xs text-gray-500"></i>
                            </button>

                            <transition
                                enter-active-class="transition ease-out duration-200"
                                enter-from-class="opacity-0 scale-95"
                                enter-to-class="opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-150"
                                leave-from-class="opacity-100 scale-100"
                                leave-to-class="opacity-0 scale-95"
                            >
                                <div
                                    v-if="userMenuOpen"
                                    class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50"
                                >
                                    <div class="px-4 py-3 border-b border-gray-100">
                                        <p class="text-sm font-semibold text-gray-800">
                                            {{ $page.props.auth?.user?.name }}
                                        </p>
                                        <p class="text-xs text-gray-500 truncate">
                                            {{ $page.props.auth?.user?.email }}
                                        </p>
                                    </div>
                                    <div class="py-1">
                                        <Link
                                            :href="route('employee.profile')"
                                            class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                        >
                                            <i class="bi bi-person text-gray-400"></i>
                                            My Profile
                                        </Link>
                                    </div>
                                    <div class="border-t border-gray-100 py-1">
                                        <Link
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                            class="flex items-center gap-3 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                                        >
                                            <i class="bi bi-box-arrow-right"></i>
                                            Logout
                                        </Link>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="flex-1 p-4 sm:p-6">
                <slot />
            </div>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 px-4 sm:px-6 py-4 text-center text-sm text-gray-500">
                &copy; {{ currentYear }} Payroll System. All rights reserved.
            </footer>
        </main>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    pageTitle: {
        type: String,
        default: 'Dashboard',
    },
});

const page = usePage();
const sidebarCollapsed = ref(false);
const mobileOpen = ref(false);
const userMenuOpen = ref(false);

onMounted(() => {
    const saved = localStorage.getItem('empSidebarCollapsed');
    if (saved !== null) {
        sidebarCollapsed.value = saved === 'true';
    }
});

const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    localStorage.setItem('empSidebarCollapsed', sidebarCollapsed.value);
};

const isActive = (routeName) => {
    const url = page.url;
    const prefix = routeName.replace('employee.', '/employee/');
    return url.startsWith(prefix);
};

const userAvatar = computed(() => {
    const name = page.props.auth?.user?.name || 'Employee';
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=6366f1&color=fff`;
});

const currentYear = new Date().getFullYear();

const vClickOutside = {
    mounted(el, binding) {
        el.__clickOutsideHandler__ = (event) => {
            if (!el.contains(event.target)) {
                binding.value(event);
            }
        };
        document.addEventListener('click', el.__clickOutsideHandler__);
    },
    unmounted(el) {
        document.removeEventListener('click', el.__clickOutsideHandler__);
    },
};
</script>
