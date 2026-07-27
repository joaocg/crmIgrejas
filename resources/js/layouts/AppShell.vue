<template>
    <div class="app-shell">
        <div class="app-shell__surface">
            <div class="app-shell__grid">
                <aside class="app-sidebar">
                    <div class="app-brand">
                        <div class="app-brand__mark">CI</div>
                        <div>
                            <div class="app-brand__title">crmIgrejas</div>
                            <div class="app-brand__subtitle">Laravel 12 + Vue 3 + PrimeVue 4</div>
                        </div>
                    </div>

                    <nav class="app-nav">
                        <RouterLink class="app-nav__item" to="/dashboard">Dashboard</RouterLink>
                        <RouterLink class="app-nav__item" to="/login">Login</RouterLink>
                    </nav>
                </aside>

                <main class="app-main">
                    <header class="app-topbar">
                        <div>
                            <div class="app-topbar__heading">Private Space</div>
                            <div class="app-topbar__title">{{ pageTitle }}</div>
                        </div>

                        <PButton label="Novo cadastro" icon="pi pi-plus" severity="primary" />
                    </header>

                    <section class="app-content">
                        <slot />
                    </section>
                </main>
            </div>
        </div>
    </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';

const props = defineProps({
    title: {
        type: String,
        default: 'Dashboard',
    },
});

const pageTitle = props.title;
</script>
