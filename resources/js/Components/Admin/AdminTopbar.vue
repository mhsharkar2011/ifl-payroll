<template>
    <header class="bg-white shadow-sm sticky top-0 z-30">
        <div class="flex items-center justify-between px-4 sm:px-6 h-16">
            <!-- Left -->
            <div class="flex items-center gap-3">
                <button
                    @click="$emit('toggle-mobile')"
                    class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition"
                >
                    <i class="bi bi-list text-2xl text-gray-700"></i>
                </button>
                <h1 class="text-lg font-semibold text-gray-800">{{ title }}</h1>
            </div>

            <!-- Right -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Search -->
                <div class="hidden md:block relative">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input
                        type="text"
                        placeholder="Search..."
                        class="pl-10 pr-4 py-2 w-64 bg-gray-100 border border-transparent rounded-lg text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
                    />
                </div>

                <!-- Notifications -->
                <div class="relative" ref="notificationsRef">
                    <button
                        @click="notificationsOpen = !notificationsOpen"
                        class="relative p-2 rounded-lg hover:bg-gray-100 transition"
                    >
                        <i class="bi bi-bell text-xl text-gray-700"></i>
                        <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
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
                            v-if="notificationsOpen"
                            class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50"
                        >
                            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                                <h6 class="font-semibold text-gray-800">Notifications</h6>
                                <button class="text-xs text-indigo-600 hover:text-indigo-800">Mark all read</button>
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                <a
                                    v-for="(n, i) in notifications"
                                    :key="i"
                                    href="#"
                                    class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition"
                                >
                                    <div :class="['w-9 h-9 rounded-full flex items-center justify-center shrink-0', n.bg]">
                                        <i :class="[n.icon, n.color]"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-800">{{ n.title }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ n.time }}</p>
                                    </div>
                                </a>
                            </div>
                            <div class="px-4 py-2 border-t border-gray-100 text-center">
                                <a href="#" class="text-sm text-indigo-600 hover:text-indigo-800">View all notifications</a>
                            </div>
                        </div>
                    </transition>
                </div>

                <!-- User Menu -->
                <div class="relative" ref="userMenuRef">
                    <button
                        @click="userMenuOpen = !userMenuOpen"
                        class="flex items-center gap-2 p-1 pr-3 rounded-lg hover:bg-gray-100 transition"
                    >
                        <img :src="avatar" alt="Avatar" class="w-8 h-8 rounded-full ring-2 ring-indigo-100" />
                        <span class="hidden sm:block text-sm font-medium text-gray-700">
                            {{ userName }}
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
                                <p class="text-sm font-semibold text-gray-800">{{ userName }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ userEmail }}</p>
                            </div>
                            <div class="py-1">
                                <Link
                                    v-if="hasRoute('profile.edit')"
                                    :href="safeRoute('profile.edit')"
                                    class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition"
                                >
                                    <i class="bi bi-person text-gray-400"></i>
                                    Profile
                                </Link>
                                <Link
                                    v-if="hasRoute('admin.settings.index')"
                                    :href="safeRoute('admin.settings.index')"
                                    class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition"
                                >
                                    <i class="bi bi-gear text-gray-400"></i>
                                    Settings
                                </Link>
                            </div>
                            <div class="border-t border-gray-100 py-1">
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="flex items-center gap-3 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition"
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
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    title: { type: String, default: 'Dashboard' },
});

defineEmits(['toggle-mobile']);

const page = usePage();

/* ========== Safe Route Helpers ========== */
const hasRoute = (name) => {
    try { route(name); return true; } catch { return false; }
};

const safeRoute = (name) => {
    try { return route(name); } catch { return '#'; }
};

/* ========== User Info ========== */
const userName = computed(() => page.props.auth?.user?.name || 'User');
const userEmail = computed(() => page.props.auth?.user?.email || '');
const avatar = computed(() =>
    `https://ui-avatars.com/api/?name=${encodeURIComponent(userName.value)}&background=6366f1&color=fff`
);

/* ========== Notifications (static placeholder) ========== */
const notifications = [
    { bg: 'bg-indigo-100', color: 'text-indigo-600', icon: 'bi bi-person-plus', title: 'New employee added', time: '5 minutes ago' },
    { bg: 'bg-green-100', color: 'text-green-600', icon: 'bi bi-calendar-check', title: 'Attendance synced', time: '1 hour ago' },
    { bg: 'bg-yellow-100', color: 'text-yellow-600', icon: 'bi bi-cash-coin', title: 'Payroll ready', time: '2 hours ago' },
];

/* ========== Dropdown state ========== */
const notificationsOpen = ref(false);
const userMenuOpen = ref(false);

const notificationsRef = ref(null);
const userMenuRef = ref(null);

const handleClickOutside = (e) => {
    if (notificationsRef.value && !notificationsRef.value.contains(e.target)) {
        notificationsOpen.value = false;
    }
    if (userMenuRef.value && !userMenuRef.value.contains(e.target)) {
        userMenuOpen.value = false;
    }
};

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>
