<template>
    <!-- Single Link (no children) -->
    <li v-if="!item.children">
        <Link
            :href="safeRoute(item.route)"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition"
            :class="isActive(item.route)
                ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-500/30'
                : 'text-slate-300 hover:bg-white/10 hover:text-white'"
            :aria-current="isActive(item.route) ? 'page' : undefined"
        >
            <i :class="[item.icon, 'text-lg w-5 text-center']" aria-hidden="true"></i>
            <span v-if="!collapsed" class="text-sm font-medium">{{ item.label }}</span>
        </Link>
    </li>

    <!-- Dropdown (has children) -->
    <li v-else>
        <button
            @click="$emit('toggle', item.key)"
            class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg transition"
            :class="isGroupActive(item)
                ? 'bg-white/10 text-white'
                : 'text-slate-300 hover:bg-white/10 hover:text-white'"
            :aria-expanded="open"
        >
            <div class="flex items-center gap-3">
                <i :class="[item.icon, 'text-lg w-5 text-center']" aria-hidden="true"></i>
                <span v-if="!collapsed" class="text-sm font-medium">{{ item.label }}</span>
            </div>
            <i
                v-if="!collapsed"
                class="bi text-xs transition-transform"
                :class="open ? 'bi-chevron-down' : 'bi-chevron-right'"
                aria-hidden="true"
            ></i>
        </button>

        <ul v-show="open && !collapsed" class="ml-8 mt-1 space-y-1">
            <li v-for="child in item.children" :key="child.route">
                <Link
                    :href="safeRoute(child.route)"
                    class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg transition"
                    :class="isActive(child.route)
                        ? 'text-white bg-white/5'
                        : 'text-slate-400 hover:text-white hover:bg-white/10'"
                    :aria-current="isActive(child.route) ? 'page' : undefined"
                >
                    <i class="bi bi-circle text-[6px]" aria-hidden="true"></i>
                    {{ child.label }}
                </Link>
            </li>
        </ul>
    </li>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    item:      { type: Object,  required: true },
    collapsed: { type: Boolean, default: false },
    open:      { type: Boolean, default: false },
});

defineEmits(['toggle']);

const page = usePage();

/* ============================================================
 |  Route helpers
 * ============================================================ */

/**
 * Safely resolve a route name to a URL.
 * Logs the missing route in dev so silent failures become visible.
 */
const safeRoute = (name) => {
    try {
        return route(name);
    } catch (e) {
        if (import.meta.env.DEV) {
            console.warn(`[Sidebar] Missing route: ${name}`, e);
        }
        return '#';
    }
};

/**
 * Is the current page the given route (or any route in that group)?
 *
 * Uses Ziggy's route().current() which:
 *   ✅ matches by route name, not URL prefix
 *   ✅ ignores query strings (?month=11&year=2024)
 *   ✅ supports wildcards via `${name}.*`
 */
const isActive = (routeName) => {
    if (!routeName) return false;

    try {
        return route().current(routeName) || route().current(`${routeName}.*`);
    } catch {
        // Fallback for edge cases (e.g. route not yet registered)
        const url = page.url.split('?')[0];
        const prefix = routeName.replace('admin.', '/admin/').replace(/\./g, '/');
        return url.startsWith(prefix);
    }
};

/**
 * Group is active if:
 *   - it's a leaf and its own route matches, OR
 *   - it has children and any of them (or their descendants) match.
 */
const isGroupActive = (item) => {
    if (!item.children || item.children.length === 0) {
        return isActive(item.route);
    }
    return item.children.some(child => isActive(child.route));
};
</script>
