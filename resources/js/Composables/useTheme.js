import { ref, watch } from 'vue';

const STORAGE_KEY = 'app-theme';

/* Reactive global theme — persists across components */
const theme = ref(
    typeof window !== 'undefined'
        ? localStorage.getItem(STORAGE_KEY) || 'system'
        : 'system'
);

/**
 * Apply the theme class to <html>.
 */
const applyTheme = (value) => {
    if (typeof document === 'undefined') return;

    const root = document.documentElement;
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    const isDark = value === 'dark' || (value === 'system' && prefersDark);

    root.classList.toggle('dark', isDark);
    root.dataset.theme = value;
};

/* Watch for changes and persist + apply */
watch(
    theme,
    (value) => {
        if (typeof window === 'undefined') return;
        localStorage.setItem(STORAGE_KEY, value);
        applyTheme(value);
    },
    { immediate: true }
);

/* React to OS preference changes when in 'system' mode */
if (typeof window !== 'undefined') {
    window
        .matchMedia('(prefers-color-scheme: dark)')
        .addEventListener('change', () => {
            if (theme.value === 'system') applyTheme('system');
        });
}

export function useTheme() {
    const setTheme = (value) => {
        theme.value = value;
    };

    return { theme, setTheme };
}
