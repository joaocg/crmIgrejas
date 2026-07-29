<template>
    <AppShell :title="t('events.title')">
        <div class="hero-panel">
            <div class="hero-panel__eyebrow">{{ t('events.eyebrow') }}</div>
            <h1 class="hero-panel__title">{{ t('events.title') }}</h1>
            <p class="hero-panel__copy">{{ t('events.copy') }}</p>
        </div>

        <PCard style="margin-top: 24px;">
            <template #title>{{ t('events.registered') }}</template>
            <template #content>
                <div v-if="error" style="margin-bottom: 16px; color: var(--app-accent); font-weight: 600;">
                    {{ error }}
                </div>

                <BaseDataTable :rows="events" :rows-per-page="8">
                    <PColumn field="title" :header="t('events.columns.title')" />
                    <PColumn field="group_name" :header="t('events.columns.group')" />
                    <PColumn field="starts_at_label" :header="t('events.columns.starts_at')" />
                    <PColumn field="location" :header="t('events.columns.location')" />
                    <PColumn field="is_active_label" :header="t('events.columns.is_active')" />
                </BaseDataTable>
            </template>
        </PCard>
    </AppShell>
</template>

<script setup>
import { onMounted, ref } from 'vue';

import { t } from '../../../i18n';
import AppShell from '../../../layouts/AppShell.vue';
import BaseDataTable from '../../../components/tables/BaseDataTable.vue';
import { listEvents } from '../../../api/modules/events';

const events = ref([]);
const error = ref('');

function formatDateTime(value) {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(new Date(value));
}

onMounted(async () => {
    try {
        const response = await listEvents();
        const rows = response.data?.data ?? response.data ?? [];

        events.value = rows.map((event) => ({
            ...event,
            group_name: event.group?.name ?? '—',
            starts_at_label: formatDateTime(event.starts_at),
            is_active_label: event.is_active ? t('forms.status.active') : t('forms.status.inactive'),
        }));
    } catch {
        error.value = t('forms.messages.auth_required_load');
        events.value = [
            {
                title: t('events.fallback_event.title'),
                group_name: t('events.fallback_event.group'),
                starts_at_label: t('events.fallback_event.starts_at'),
                location: t('events.fallback_event.location'),
                is_active_label: t('forms.status.active'),
            },
        ];
    }
});
</script>
