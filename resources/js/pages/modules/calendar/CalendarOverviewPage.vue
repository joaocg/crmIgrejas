<template>
    <AppShell :title="t('calendar.title')">
        <div class="hero-panel">
            <div class="hero-panel__eyebrow">{{ t('calendar.eyebrow') }}</div>
            <h1 class="hero-panel__title">{{ t('calendar.title') }}</h1>
            <p class="hero-panel__copy">{{ t('calendar.copy') }}</p>

            <div class="surface-grid">
                <PCard>
                    <template #title>{{ t('calendar.summary.events') }}</template>
                    <template #content>
                        <strong>{{ summary.events }}</strong>
                        <div class="app-brand__subtitle">{{ t('calendar.summary.events_copy') }}</div>
                    </template>
                </PCard>

                <PCard>
                    <template #title>{{ t('calendar.summary.next_event') }}</template>
                    <template #content>
                        <strong>{{ summary.next_event }}</strong>
                        <div class="app-brand__subtitle">{{ t('calendar.summary.next_event_copy') }}</div>
                    </template>
                </PCard>

                <PCard>
                    <template #title>{{ t('calendar.summary.sync') }}</template>
                    <template #content>
                        <strong>{{ summary.sync }}</strong>
                        <div class="app-brand__subtitle">{{ t('calendar.summary.sync_copy') }}</div>
                    </template>
                </PCard>
            </div>
        </div>

        <PCard style="margin-top: 24px;">
            <template #title>{{ t('calendar.list.title') }}</template>
            <template #content>
                <div v-if="error" style="margin-bottom: 16px; color: var(--app-accent); font-weight: 600;">
                    {{ error }}
                </div>

                <BaseDataTable :rows="events" :rows-per-page="8">
                    <PColumn field="title" :header="t('calendar.list.columns.title')" />
                    <PColumn field="group_name" :header="t('calendar.list.columns.group')" />
                    <PColumn field="starts_at_label" :header="t('calendar.list.columns.starts_at')" />
                    <PColumn field="is_active_label" :header="t('calendar.list.columns.is_active')" />
                </BaseDataTable>
            </template>
        </PCard>
    </AppShell>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';

import { t } from '../../../i18n';
import AppShell from '../../../layouts/AppShell.vue';
import BaseDataTable from '../../../components/tables/BaseDataTable.vue';
import { getCalendarOverview } from '../../../api/modules/calendar';

const summary = reactive({
    events: '0',
    next_event: '—',
    sync: '—',
});

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
        const response = await getCalendarOverview();
        const rows = response.data?.events ?? [];

        events.value = rows.map((event) => ({
            ...event,
            group_name: event.group?.name ?? '—',
            starts_at_label: formatDateTime(event.starts_at),
            is_active_label: event.is_active ? t('forms.status.active') : t('forms.status.inactive'),
        }));

        summary.events = String(events.value.length);
        summary.next_event = events.value[0]?.starts_at_label ?? '—';
        summary.sync = t('calendar.summary.sync_value');
    } catch {
        error.value = t('forms.messages.auth_required_load');
        events.value = [
            {
                title: t('calendar.fallback_event.title'),
                group_name: t('calendar.fallback_event.group'),
                starts_at_label: t('calendar.fallback_event.starts_at'),
                is_active_label: t('forms.status.active'),
            },
        ];
        summary.events = '1';
        summary.next_event = t('calendar.fallback_event.starts_at');
        summary.sync = t('calendar.summary.sync_value');
    }
});
</script>
