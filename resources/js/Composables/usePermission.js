import { usePage } from '@inertiajs/vue3';

export function usePermission() {
    const page = usePage();

    const can = (permission) => {
        const permissions = page.props.permissions || [];
        return permissions.includes(permission);
    };

    const is = (role) => {
        const roles = page.props.roles || [];
        return roles.includes(role);
    };

    return {
        can,
        is,
        permissions: page.props.permissions || [],
        roles: page.props.roles || []
    };
}
