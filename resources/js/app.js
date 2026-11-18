import './bootstrap';
import 'bootstrap-icons/font/bootstrap-icons.css';
import '../css/app.css';
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import ToastContainer from '@/Components/UI/ToastContainer.vue';

// Import Ziggy for route helper
import { ZiggyVue } from 'ziggy-js';
import { Ziggy } from './ziggy';

// Make Ziggy's base URL match the current browser origin at runtime.
// This eliminates any dependency on APP_URL or VITE_APP_URL for the frontend.
if (typeof window !== 'undefined' && window.location) {
    Ziggy.url = window.location.origin;
    Ziggy.port = window.location.port || null;
}

// Merge any Ziggy data injected via the @routes Blade directive
if (typeof window !== 'undefined' && typeof window.Ziggy !== 'undefined') {
    Object.assign(Ziggy.routes, window.Ziggy.routes);
}

import { useTheme } from '@/composables/useTheme';
useTheme();

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';


createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue')
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({
            render: () => h('div', [
                h(App, props),
                h(ToastContainer), // ✅ Toast Container এখানে render হচ্ছে
            ]),
        });

        app.use(plugin);
        app.use(ZiggyVue, Ziggy);

        app.mount(el);
    },

    // Optional: Inertia Progress bar
    progress: {
        color: '#4F46E5',
        showSpinner: true,
    },
});
