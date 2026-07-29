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
            <section v-for="section in sections" :key="section.key" class="app-nav__section">
                <div class="app-nav__section-title">{{ t(section.labelKey) }}</div>
                <RouterLink
                    v-for="item in section.items"
                    :key="item.key"
                    :to="item.route"
                    class="app-nav__item"
                    :class="{ 'is-active': route.path === item.route }"
                >
                    <span class="app-nav__icon">{{ item.icon }}</span>
                    <span>{{ t(item.labelKey) }}</span>
                    <span class="app-nav__meta" v-if="item.meta">{{ item.meta }}</span>
                </RouterLink>
            </section>
        </nav>
    </aside>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { RouterLink, useRoute } from 'vue-router';

import { t } from '../../i18n';
import { useAuthStore } from '../../stores/auth';
import { useNavigationStore } from '../../stores/navigation';

const route = useRoute();
const auth = useAuthStore();
const navigation = useNavigationStore();
const sections = computed(() => navigation.currentSections);

onMounted(async () => {
    if (auth.isAuthenticated) {
        await navigation.load();
    }
});
</script>
