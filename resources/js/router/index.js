import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { placeholderRoutes } from '../navigation/siteNavigation';

function hasPermission(user, ability) {
    if (!ability) {
        return true;
    }

    const permissions = user?.role?.permissions ?? {};

    if (permissions['*'] === true) {
        return true;
    }

    return permissions[ability] === true;
}

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
                ability: null,
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
                ability: 'navigation.users',
            },
        },
        {
            path: '/users/create',
            name: 'users.create',
            component: () => import('../pages/modules/users/UserCreatePage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.users',
            },
        },
        {
            path: '/users/:id/edit',
            name: 'users.edit',
            component: () => import('../pages/modules/users/UserEditPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.users',
            },
        },
        {
            path: '/users/:id',
            name: 'users.show',
            component: () => import('../pages/modules/users/UserShowPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.users',
            },
        },
        {
            path: '/people',
            name: 'people.index',
            component: () => import('../pages/modules/people/PeopleListPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.people',
            },
        },
        {
            path: '/people/create',
            name: 'people.create',
            component: () => import('../pages/modules/people/PeopleCreatePage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.people',
            },
        },
        {
            path: '/people/:id/edit',
            name: 'people.edit',
            component: () => import('../pages/modules/people/PeopleEditPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.people',
            },
        },
        {
            path: '/people/:id',
            name: 'people.show',
            component: () => import('../pages/modules/people/PeopleShowPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.people',
            },
        },
        {
            path: '/families',
            name: 'families.index',
            component: () => import('../pages/modules/families/FamilyListPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.families',
            },
        },
        {
            path: '/families/create',
            name: 'families.create',
            component: () => import('../pages/modules/families/FamilyCreatePage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.families',
            },
        },
        {
            path: '/families/:id/edit',
            name: 'families.edit',
            component: () => import('../pages/modules/families/FamilyEditPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.families',
            },
        },
        {
            path: '/families/:id',
            name: 'families.show',
            component: () => import('../pages/modules/families/FamilyShowPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.families',
            },
        },
        {
            path: '/groups',
            name: 'groups.index',
            component: () => import('../pages/modules/groups/GroupListPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'groups.view_all',
            },
        },
        {
            path: '/groups/create',
            name: 'groups.create',
            component: () => import('../pages/modules/groups/GroupCreatePage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'groups.view_all',
            },
        },
        {
            path: '/groups/:id/edit',
            name: 'groups.edit',
            component: () => import('../pages/modules/groups/GroupEditPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'groups.view_all',
            },
        },
        {
            path: '/groups/:id',
            name: 'groups.show',
            component: () => import('../pages/modules/groups/GroupShowPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'groups.view_all',
            },
        },
        {
            path: '/events',
            name: 'events.index',
            component: () => import('../pages/modules/events/EventListPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.events',
            },
        },
        {
            path: '/events/create',
            name: 'events.create',
            component: () => import('../pages/modules/events/EventCreatePage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.events',
            },
        },
        {
            path: '/events/:id/edit',
            name: 'events.edit',
            component: () => import('../pages/modules/events/EventEditPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.events',
            },
        },
        {
            path: '/events/:id',
            name: 'events.show',
            component: () => import('../pages/modules/events/EventShowPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.events',
            },
        },
        {
            path: '/finance',
            name: 'finance.index',
            component: () => import('../pages/modules/finance/FinanceListPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.finance',
            },
        },
        {
            path: '/finance/create',
            name: 'finance.create',
            component: () => import('../pages/modules/finance/FinanceCreatePage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.finance',
            },
        },
        {
            path: '/finance/:id/edit',
            name: 'finance.edit',
            component: () => import('../pages/modules/finance/FinanceEditPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.finance',
            },
        },
        {
            path: '/finance/:id',
            name: 'finance.show',
            component: () => import('../pages/modules/finance/FinanceShowPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.finance',
            },
        },
        {
            path: '/care',
            name: 'care.index',
            component: () => import('../pages/modules/care/CareOverviewPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.care',
            },
        },
        {
            path: '/care/notes/create',
            name: 'care.notes.create',
            component: () => import('../pages/modules/care/NoteCreatePage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.care',
            },
        },
        {
            path: '/care/pastoral-care/create',
            name: 'care.records.create',
            component: () => import('../pages/modules/care/PastoralCareCreatePage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.care',
            },
        },
        {
            path: '/communications',
            name: 'communications.index',
            component: () => import('../pages/modules/communications/CommunicationsOverviewPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.communications',
            },
        },
        {
            path: '/communications/whatsapp',
            name: 'communications.whatsapp',
            component: () => import('../pages/modules/communications/WhatsAppIntegrationPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.communications',
            },
        },
        {
            path: '/calendar',
            name: 'calendar.index',
            component: () => import('../pages/modules/calendar/CalendarOverviewPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.calendar',
            },
        },
        {
            path: '/kiosk',
            name: 'kiosk.index',
            component: () => import('../pages/modules/kiosk/KioskOverviewPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.kiosk',
            },
        },
        {
            path: '/repertoire',
            name: 'repertoire.index',
            component: () => import('../pages/modules/repertoire/RepertoireOverviewPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.repertoire',
            },
        },
        {
            path: '/manuals',
            name: 'manuals.index',
            component: () => import('../pages/modules/manuals/ManualsOverviewPage.vue'),
            meta: {
                requiresAuth: true,
                ability: 'navigation.manuals',
            },
        },
        {
            path: '/settings/integrations/whatsapp',
            redirect: '/communications/whatsapp',
        },
        ...placeholderRoutes.map((item) => ({
            path: item.route,
            name: item.key,
            component: () => import('../pages/ModuleLandingPage.vue'),
            meta: {
                requiresAuth: true,
                ability: item.ability ?? null,
                titleKey: item.labelKey,
                eyebrowKey: item.sectionLabelKey,
                copyKey: 'modules.placeholder.copy',
            },
        })),
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

    if (auth.isAuthenticated && !hasPermission(auth.user, to.meta.ability)) {
        return { path: '/dashboard' };
    }

    return true;
});

export default router;
