import { createRouter, createWebHistory } from 'vue-router';

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
        },
        {
            path: '/login',
            name: 'login',
            component: () => import('../pages/auth/LoginPage.vue'),
        },
        {
            path: '/users',
            name: 'users.index',
            component: () => import('../pages/modules/users/UserListPage.vue'),
        },
        {
            path: '/users/create',
            name: 'users.create',
            component: () => import('../pages/modules/users/UserCreatePage.vue'),
        },
        {
            path: '/users/:id/edit',
            name: 'users.edit',
            component: () => import('../pages/modules/users/UserEditPage.vue'),
        },
        {
            path: '/users/:id',
            name: 'users.show',
            component: () => import('../pages/modules/users/UserShowPage.vue'),
        },
    ],
    scrollBehavior() {
        return { top: 0 };
    },
});

export default router;
