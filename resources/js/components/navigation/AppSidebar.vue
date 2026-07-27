<template>
    <aside class="app-sidebar">
        <div class="app-brand">
            <div class="app-brand__mark">CI</div>
            <div>
                <div class="app-brand__title">crmIgrejas</div>
                <div class="app-brand__subtitle">Modular church CRM</div>
            </div>
        </div>

        <nav class="app-nav">
            <RouterLink
                v-for="item in items"
                :key="item.to"
                :to="item.to"
                class="app-nav__item"
                :class="{ 'is-active': route.path === item.to }"
            >
                <span class="app-nav__icon">{{ item.icon }}</span>
                <span>{{ item.label }}</span>
                <span class="app-nav__meta" v-if="item.meta">{{ item.meta }}</span>
            </RouterLink>
        </nav>
    </aside>
</template>

<script setup>
import { RouterLink, useRoute } from 'vue-router';

import { t } from '../../i18n';

const route = useRoute();

const items = [
    { to: '/dashboard', label: t('navigation.dashboard'), icon: '⌂' },
    { to: '/users', label: t('navigation.users'), icon: '◫', meta: 'CRUD' },
    { to: '/people', label: t('navigation.people'), icon: '◉', meta: 'CRUD' },
    { to: '/families', label: t('navigation.families'), icon: '◔', meta: 'CRUD' },
    { to: '/login', label: t('navigation.login'), icon: '→' },
];
</script>
