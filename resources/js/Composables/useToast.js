import { ref } from 'vue';

// Global toast state
const toasts = ref([]);
let toastId = 0;

export function useToast() {
    /**
     * Show a toast notification
     * @param {Object|String} options - Toast options or message
     */
    const show = (options) => {
        // If string is passed, convert to object
        if (typeof options === 'string') {
            options = { message: options, type: 'info' };
        }

        const {
            message = '',
            type = 'info', // success, error, warning, info
            title = '',
            duration = 3000,
            position = 'top-right',
        } = options;

        const id = ++toastId;
        const toast = {
            id,
            message,
            type,
            title,
            position,
        };

        toasts.value.push(toast);

        if (duration > 0) {
            setTimeout(() => {
                remove(id);
            }, duration);
        }

        return id;
    };

    /**
     * Show success toast
     */
    const success = (message, options = {}) => {
        return show({ message, type: 'success', ...options });
    };

    /**
     * Show error toast
     */
    const error = (message, options = {}) => {
        return show({ message, type: 'error', duration: 5000, ...options });
    };

    /**
     * Show warning toast
     */
    const warning = (message, options = {}) => {
        return show({ message, type: 'warning', ...options });
    };

    /**
     * Show info toast
     */
    const info = (message, options = {}) => {
        return show({ message, type: 'info', ...options });
    };

    /**
     * Remove a specific toast
     */
    const remove = (id) => {
        const index = toasts.value.findIndex((t) => t.id === id);
        if (index > -1) {
            toasts.value.splice(index, 1);
        }
    };

    /**
     * Clear all toasts
     */
    const clear = () => {
        toasts.value = [];
    };

    /**
     * Promise-based toast for async operations
     */
    const promise = (promiseFn, { loading, success: successMsg, error: errorMsg }) => {
        const id = show({
            message: loading || 'Loading...',
            type: 'info',
            duration: 0,
        });

        return promiseFn
            .then((result) => {
                remove(id);
                if (successMsg) {
                    show({
                        message: typeof successMsg === 'function' ? successMsg(result) : successMsg,
                        type: 'success',
                    });
                }
                return result;
            })
            .catch((err) => {
                remove(id);
                show({
                    message: typeof errorMsg === 'function' ? errorMsg(err) : (errorMsg || err.message || 'Something went wrong'),
                    type: 'error',
                    duration: 5000,
                });
                throw err;
            });
    };

    return {
        toasts,
        show,
        success,
        error,
        warning,
        info,
        remove,
        clear,
        promise,
    };
}
