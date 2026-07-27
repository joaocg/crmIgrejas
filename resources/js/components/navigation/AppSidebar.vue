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

const route = useRoute();

const items = [
    { to: '/dashboard', label: 'Dashboard', icon: '⌂' },
    { to: '/login', label: 'Login', icon: '→' },
];
</script>
