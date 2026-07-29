<template>
    <AppShell :title="t('kiosk.title')">
        <div class="hero-panel">
            <div class="hero-panel__eyebrow">{{ t('kiosk.eyebrow') }}</div>
            <h1 class="hero-panel__title">{{ t('kiosk.title') }}</h1>
            <p class="hero-panel__copy">{{ t('kiosk.copy') }}</p>

            <div class="surface-grid">
                <PCard>
                    <template #title>{{ t('kiosk.summary.people') }}</template>
                    <template #content>
                        <strong>{{ summary.people }}</strong>
                        <div class="app-brand__subtitle">{{ t('kiosk.summary.people_copy') }}</div>
                    </template>
                </PCard>

                <PCard>
                    <template #title>{{ t('kiosk.summary.families') }}</template>
                    <template #content>
                        <strong>{{ summary.families }}</strong>
                        <div class="app-brand__subtitle">{{ t('kiosk.summary.families_copy') }}</div>
                    </template>
                </PCard>

                <PCard>
                    <template #title>{{ t('kiosk.summary.check_in') }}</template>
                    <template #content>
                        <strong>{{ summary.check_in }}</strong>
                        <div class="app-brand__subtitle">{{ t('kiosk.summary.check_in_copy') }}</div>
                    </template>
                </PCard>
            </div>
        </div>

        <div class="surface-grid" style="margin-top: 24px;">
            <PCard>
                <template #title>{{ t('kiosk.snapshot.title') }}</template>
                <template #content>
                    <div class="stack-form">
                        <div><strong>{{ t('kiosk.snapshot.groups') }}:</strong> {{ snapshot.groups }}</div>
                        <div><strong>{{ t('kiosk.snapshot.events') }}:</strong> {{ snapshot.events }}</div>
                        <div><strong>{{ t('kiosk.snapshot.status') }}:</strong> {{ snapshot.status }}</div>
                    </div>
                </template>
            </PCard>

            <PCard>
                <template #title>{{ t('kiosk.actions.title') }}</template>
                <template #content>
                    <div class="stack-form">
                        <PButton :label="t('kiosk.actions.open_people')" icon="pi pi-users" @click="$router.push('/people')" />
                        <PButton :label="t('kiosk.actions.open_groups')" severity="secondary" text @click="$router.push('/groups')" />
                    </div>
                </template>
            </PCard>
        </div>
    </AppShell>
</template>

<script setup>
import { onMounted, reactive } from 'vue';

import { t } from '../../../i18n';
import AppShell from '../../../layouts/AppShell.vue';
import { getKioskOverview } from '../../../api/modules/kiosk';

const summary = reactive({
    people: '0',
    families: '0',
    check_in: '0',
});

const snapshot = reactive({
    groups: '0',
    events: '0',
    status: '—',
});

onMounted(async () => {
    try {
        const response = await getKioskOverview();
        const counts = response.data?.summary ?? {};

        summary.people = String(counts.people ?? 0);
        summary.families = String(counts.families ?? 0);
        summary.check_in = t('kiosk.summary.check_in_value');
        snapshot.groups = String(counts.groups ?? 0);
        snapshot.events = String(counts.events ?? 0);
        snapshot.status = t('kiosk.snapshot.ready');
    } catch {
        summary.people = '0';
        summary.families = '0';
        summary.check_in = t('kiosk.summary.check_in_value');
        snapshot.groups = '0';
        snapshot.events = '0';
        snapshot.status = t('kiosk.snapshot.ready');
    }
});
</script>
