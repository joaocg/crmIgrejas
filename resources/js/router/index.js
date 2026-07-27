import { createRouter, createWebHistory } from 'vue-router';

import DashboardPage from '../pages/DashboardPage.vue';
import LoginPage from '../pages/auth/LoginPage.vue';

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
            component: DashboardPage,
        },
        {
            path: '/login',
            name: 'login',
            component: LoginPage,
        },
    ],
    scrollBehavior() {
        return { top: 0 };
    },
});

export default router;
