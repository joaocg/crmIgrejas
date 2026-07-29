<template>
    <AppShell :title="t('manuals.title')">
        <div class="hero-panel">
            <div class="hero-panel__eyebrow">{{ t('manuals.eyebrow') }}</div>
            <h1 class="hero-panel__title">{{ t('manuals.title') }}</h1>
            <p class="hero-panel__copy">{{ t('manuals.copy') }}</p>

            <div class="surface-grid">
                <PCard>
                    <template #title>{{ t('manuals.summary.guides') }}</template>
                    <template #content>
                        <strong>{{ summary.guides }}</strong>
                        <div class="app-brand__subtitle">{{ t('manuals.summary.guides_copy') }}</div>
                    </template>
                </PCard>

                <PCard>
                    <template #title>{{ t('manuals.summary.modules') }}</template>
                    <template #content>
                        <strong>{{ summary.modules }}</strong>
                        <div class="app-brand__subtitle">{{ t('manuals.summary.modules_copy') }}</div>
                    </template>
                </PCard>

                <PCard>
                    <template #title>{{ t('manuals.summary.updated') }}</template>
                    <template #content>
                        <strong>{{ summary.updated }}</strong>
                        <div class="app-brand__subtitle">{{ t('manuals.summary.updated_copy') }}</div>
                    </template>
                </PCard>
            </div>
        </div>

        <div class="surface-grid" style="margin-top: 24px;">
            <PCard>
                <template #title>{{ t('manuals.topics.title') }}</template>
                <template #content>
                    <div class="stack-form">
                        <div>{{ t('manuals.topics.item_1') }}</div>
                        <div>{{ t('manuals.topics.item_2') }}</div>
                        <div>{{ t('manuals.topics.item_3') }}</div>
                    </div>
                </template>
            </PCard>

            <PCard>
                <template #title>{{ t('manuals.actions.title') }}</template>
                <template #content>
                    <div class="stack-form">
                        <PButton :label="t('manuals.actions.open_people')" icon="pi pi-users" @click="$router.push('/people')" />
                        <PButton :label="t('manuals.actions.open_care')" severity="secondary" text @click="$router.push('/care')" />
                    </div>
                </template>
            </PCard>
        </div>
    </AppShell>
</template>

<script setup>
import { reactive } from 'vue';

import { t } from '../../../i18n';
import AppShell from '../../../layouts/AppShell.vue';

const summary = reactive({
    guides: '—',
    modules: '—',
    updated: '—',
});

summary.guides = '0';
summary.modules = '0';
summary.updated = t('manuals.summary.updated_value');
</script>
