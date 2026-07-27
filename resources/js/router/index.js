import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            redirect: '/dashboard',
        },
        {
            path: '/dashboard',
            name: 'dashboard',
            component: () => import('../pages/DashboardPage.vue'),
            meta: {
                requiresAuth: true,
            },
        },
        {
            path: '/login',
            name: 'login',
            component: () => import('../pages/auth/LoginPage.vue'),
            meta: {
                guestOnly: true,
            },
        },
        {
            path: '/users',
            name: 'users.index',
            component: () => import('../pages/modules/users/UserListPage.vue'),
            meta: {
                requiresAuth: true,
            },
        },
        {
            path: '/users/create',
            name: 'users.create',
            component: () => import('../pages/modules/users/UserCreatePage.vue'),
            meta: {
                requiresAuth: true,
            },
        },
        {
            path: '/users/:id/edit',
            name: 'users.edit',
            component: () => import('../pages/modules/users/UserEditPage.vue'),
            meta: {
                requiresAuth: true,
            },
        },
        {
            path: '/users/:id',
            name: 'users.show',
            component: () => import('../pages/modules/users/UserShowPage.vue'),
            meta: {
                requiresAuth: true,
            },
        },
    ],
    scrollBehavior() {
        return { top: 0 };
    },
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    if (!auth.initialized && auth.token) {
        await auth.hydrate();
    }

    if (to.meta.guestOnly && auth.isAuthenticated) {
        return { path: '/dashboard' };
    }

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return { path: '/login', query: { redirect: to.fullPath } };
    }

    return true;
});

export default router;
