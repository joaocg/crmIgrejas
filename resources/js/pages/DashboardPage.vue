<template>
    <AppShell :title="t('navigation.dashboard')">
        <div class="hero-panel">
            <div class="hero-panel__eyebrow">{{ t('dashboard.eyebrow') }}</div>
            <h1 class="hero-panel__title">{{ t('dashboard.title') }}</h1>
            <p class="hero-panel__copy">{{ t('dashboard.copy') }}</p>

            <div class="surface-grid">
                <PCard>
                    <template #title>{{ t('dashboard.people') }}</template>
                    <template #content>
                        <strong>{{ t('dashboard.stats.people') }}</strong>
                        <div class="app-brand__subtitle">{{ t('dashboard.subtitles.people') }}</div>
                    </template>
                </PCard>

                <PCard>
                    <template #title>{{ t('dashboard.modules') }}</template>
                    <template #content>
                        <strong>{{ t('dashboard.stats.modules') }}</strong>
                        <div class="app-brand__subtitle">{{ t('dashboard.subtitles.modules') }}</div>
                    </template>
                </PCard>

                <PCard>
                    <template #title>{{ t('dashboard.locale') }}</template>
                    <template #content>
                        <strong>{{ t('dashboard.stats.locale') }}</strong>
                        <div class="app-brand__subtitle">{{ t('dashboard.subtitles.locale') }}</div>
                    </template>
                </PCard>
            </div>
        </div>

        <PCard style="margin-top: 24px;">
            <template #title>{{ t('dashboard.recent_activity') }}</template>
            <template #content>
                <BaseDataTable :rows="recentActivity" :rows-per-page="5">
                    <PColumn field="name" :header="t('dashboard.columns.name')" />
                    <PColumn field="module" :header="t('dashboard.columns.module')" />
                    <PColumn field="status" :header="t('dashboard.columns.status')" />
                </BaseDataTable>
            </template>
        </PCard>
    </AppShell>
</template>

<script setup>
import { t } from '../i18n';
import AppShell from '../layouts/AppShell.vue';
import BaseDataTable from '../components/tables/BaseDataTable.vue';

const recentActivity = [
    { name: t('dashboard.activity.admin_user_updated'), module: t('navigation.users'), status: t('dashboard.activity_status.saved') },
    { name: t('dashboard.activity.system_locale_set'), module: t('navigation.dashboard'), status: t('dashboard.activity_status.ready') },
    { name: t('dashboard.activity.church_override_loaded'), module: t('navigation.users'), status: t('dashboard.activity_status.loaded') },
];
</script>
