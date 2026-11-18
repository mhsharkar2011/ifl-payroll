import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import os from "os";
import path from "path";
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const getLocalIp = () => {
    const interfaces = os.networkInterfaces();
    for (const name of Object.keys(interfaces)) {
        for (const iface of interfaces[name]) {
            if (iface.family === "IPv4" && !iface.internal) {
                return iface.address;
            }
        }
    }
    return "localhost";
};

const localIp = getLocalIp();

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
    ],
    server: {
        host: true,
        port: 5174,
        strictPort: true,
        cors: {
            origin: true,
            // origin: [
            //     "http://localhost:8080",
            //     "http://127.0.0.1:8080",
            //     "http://192.168.1.9:8080",
            //     `http://${localIp}:8080`,
            //     /^http:\/\/192\.168\.\d+\.\d+:8080$/,
            //     /^http:\/\/10\.\d+\.\d+\.\d+:8080$/,
            // ],
            methods: ["GET", "HEAD", "PUT", "POST", "DELETE", "PATCH"],
            credentials: true,
        },
        hmr: {
            host: localIp,
            port: 5174,
            protocol: "ws",
        },
        watch: {
            usePolling: true,
        },
    },
    optimizeDeps: {
        include: ["vue", "@inertiajs/vue3", "ziggy-js", "axios", "lodash"],
    },
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "resources/js"),
            "@components": path.resolve(__dirname, "resources/js/Components"),
            "@layouts": path.resolve(__dirname, "resources/js/Layouts"),
            "@pages": path.resolve(__dirname, "resources/js/Pages"),
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                additionalData: `@import "@/css/_variables.scss";`,
            },
        },
    },
    build: {
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                // ✅ FIXED: manualChunks as a function
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        if (id.includes('vue') ||
                            id.includes('inertia') ||
                            id.includes('axios')) {
                            return 'vendor';
                        }
                        if (id.includes('lucide-vue-next')) {
                            return 'ui';
                        }
                        // All other dependencies
                        return 'vendor';
                    }
                },
            },
        },
    },
});
