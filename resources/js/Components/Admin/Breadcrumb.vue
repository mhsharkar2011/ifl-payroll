<template>
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h4 class="mb-0 fw-bold">{{ title }}</h4>
            <nav aria-label="breadcrumb" class="mt-1">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item">
                        <Link :href="route('admin.dashboard')">
                            <i class="bi bi-house-door"></i> Home
                        </Link>
                    </li>
                    <li
                        v-for="(item, index) in items"
                        :key="index"
                        class="breadcrumb-item"
                        :class="{ active: index === items.length - 1 }"
                    >
                        <Link v-if="item.href && index !== items.length - 1" :href="item.href">
                            {{ item.label }}
                        </Link>
                        <span v-else>{{ item.label }}</span>
                    </li>
                </ol>
            </nav>
        </div>
        <div>
            <slot name="actions" />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const title = computed(() => {
    const url = page.url;
    if (url.includes('/admin/dashboard')) return 'Dashboard';
    if (url.includes('/admin/employees')) return 'Employees';
    if (url.includes('/admin/attendance')) return 'Attendance';
    if (url.includes('/admin/payroll')) return 'Payroll';
    if (url.includes('/admin/devices')) return 'Devices';
    if (url.includes('/admin/reports')) return 'Reports';
    if (url.includes('/admin/settings')) return 'Settings';
    return 'Dashboard';
});

const items = computed(() => {
    const url = page.url;
    const segments = url.split('/').filter(s => s && s !== 'admin');

    return segments.map((segment, index) => {
        const label = segment.charAt(0).toUpperCase() + segment.slice(1).replace(/-/g, ' ');
        return {
            label,
            href: index < segments.length - 1 ? `/admin/${segments.slice(0, index + 1).join('/')}` : null
        };
    });
});
</script>

<style scoped>
.breadcrumb {
    background: transparent;
    padding: 0;
    margin: 0;
}

.breadcrumb-item a {
    color: #6c757d;
    text-decoration: none;
}

.breadcrumb-item a:hover {
    color: #4F46E5;
}

.breadcrumb-item.active {
    color: #4F46E5;
    font-weight: 500;
}
</style>
