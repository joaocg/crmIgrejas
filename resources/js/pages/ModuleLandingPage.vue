<template>
    <AppShell :title="t(titleKey)">
        <div class="hero-panel">
            <div class="hero-panel__eyebrow">{{ t(eyebrowKey) }}</div>
            <h1 class="hero-panel__title">{{ t(titleKey) }}</h1>
            <p class="hero-panel__copy">{{ t(copyKey) }}</p>

            <div class="surface-grid">
                <PCard>
                    <template #title>{{ t('modules.placeholder.status') }}</template>
                    <template #content>
                        <strong>{{ t('modules.placeholder.ready') }}</strong>
                        <div class="app-brand__subtitle">{{ t('modules.placeholder.status_copy') }}</div>
                    </template>
                </PCard>

                <PCard>
                    <template #title>{{ t('modules.placeholder.next_step') }}</template>
                    <template #content>
                        <strong>{{ t('modules.placeholder.guided_flow') }}</strong>
                        <div class="app-brand__subtitle">{{ t('modules.placeholder.next_step_copy') }}</div>
                    </template>
                </PCard>

                <PCard>
                    <template #title>{{ t('modules.placeholder.integration') }}</template>
                    <template #content>
                        <strong>{{ t('modules.placeholder.shared_stack') }}</strong>
                        <div class="app-brand__subtitle">{{ t('modules.placeholder.integration_copy') }}</div>
                    </template>
                </PCard>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';

import { t } from '../i18n';
import AppShell from '../layouts/AppShell.vue';

const route = useRoute();

const titleKey = computed(() => route.meta.titleKey ?? 'navigation.dashboard');
const eyebrowKey = computed(() => route.meta.eyebrowKey ?? 'navigation.module');
const copyKey = computed(() => route.meta.copyKey ?? 'modules.placeholder.copy');
</script>
