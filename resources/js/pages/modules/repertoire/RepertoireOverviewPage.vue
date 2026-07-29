<template>
    <AppShell :title="t('repertoire.title')">
        <div class="hero-panel">
            <div class="hero-panel__eyebrow">{{ t('repertoire.eyebrow') }}</div>
            <h1 class="hero-panel__title">{{ t('repertoire.title') }}</h1>
            <p class="hero-panel__copy">{{ t('repertoire.copy') }}</p>

            <div class="surface-grid">
                <PCard>
                    <template #title>{{ t('repertoire.summary.tracks') }}</template>
                    <template #content>
                        <strong>{{ summary.tracks }}</strong>
                        <div class="app-brand__subtitle">{{ t('repertoire.summary.tracks_copy') }}</div>
                    </template>
                </PCard>

                <PCard>
                    <template #title>{{ t('repertoire.summary.groups') }}</template>
                    <template #content>
                        <strong>{{ summary.groups }}</strong>
                        <div class="app-brand__subtitle">{{ t('repertoire.summary.groups_copy') }}</div>
                    </template>
                </PCard>

                <PCard>
                    <template #title>{{ t('repertoire.summary.ready') }}</template>
                    <template #content>
                        <strong>{{ summary.ready }}</strong>
                        <div class="app-brand__subtitle">{{ t('repertoire.summary.ready_copy') }}</div>
                    </template>
                </PCard>
            </div>
        </div>

        <div class="surface-grid" style="margin-top: 24px;">
            <PCard>
                <template #title>{{ t('repertoire.guidelines.title') }}</template>
                <template #content>
                    <div class="stack-form">
                        <div>{{ t('repertoire.guidelines.item_1') }}</div>
                        <div>{{ t('repertoire.guidelines.item_2') }}</div>
                        <div>{{ t('repertoire.guidelines.item_3') }}</div>
                    </div>
                </template>
            </PCard>

            <PCard>
                <template #title>{{ t('repertoire.actions.title') }}</template>
                <template #content>
                    <div class="stack-form">
                        <PButton :label="t('repertoire.actions.open_groups')" icon="pi pi-users" @click="$router.push('/groups')" />
                        <PButton :label="t('repertoire.actions.open_events')" severity="secondary" text @click="$router.push('/events')" />
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
    tracks: '—',
    groups: '—',
    ready: '—',
});

summary.tracks = '0';
summary.groups = '0';
summary.ready = t('repertoire.summary.ready_value');
</script>
