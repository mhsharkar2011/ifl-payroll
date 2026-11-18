<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <!-- Sidebar -->
        <AdminSidebar
            :collapsed="sidebarCollapsed"
            :mobile-open="mobileOpen"
            @toggle-collapse="toggleSidebar"
            @close-mobile="mobileOpen = false"
        />

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
            <!-- Topbar -->
            <AdminTopbar
                :title="pageTitle"
                @toggle-mobile="mobileOpen = !mobileOpen"
            />

            <!-- Page Content -->
            <div class="flex-1 p-4 sm:p-6">
                <slot />
            </div>

            <!-- Footer -->
            <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <span>&copy; {{ currentYear }} Attendance System. All rights reserved.</span>
                    <span>Version 1.0.0</span>
                </div>
            </footer>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import AdminSidebar from '@/Components/Admin/AdminSidebar.vue';
import AdminTopbar from '@/Components/Admin/AdminTopbar.vue';

defineProps({
    pageTitle: {
        type: String,
        default: 'Dashboard',
    },
});

const page = usePage();
const { setTheme } = useTheme();

/* ========== Sidebar collapse ========== */
const sidebarCollapsed = ref(false);
const mobileOpen = ref(false);

const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    localStorage.setItem('sidebarCollapsed', sidebarCollapsed.value);
};

/* ========== Footer ========== */
const currentYear = new Date().getFullYear();

/* ========== On mount ========== */
onMounted(() => {
    // Restore sidebar state
    const saved = localStorage.getItem('sidebarCollapsed');
    if (saved !== null) {
        sidebarCollapsed.value = saved === 'true';
    }

    // Sync theme from user preferences
    const userTheme = page.props.auth?.user?.theme;
    if (userTheme) {
        setTheme(userTheme);
    }
});
</script>
