<template>
    <AppShell :title="t('events.show.title')">
        <PCard>
            <template #title>{{ t('events.show.heading') }}</template>
            <template #content>
                <div v-if="message" style="margin-bottom: 16px; color: var(--app-accent); font-weight: 600;">
                    {{ message }}
                </div>

                <div class="stack-form">
                    <div><strong>{{ t('events.forms.title') }}:</strong> {{ event.title }}</div>
                    <div><strong>{{ t('events.forms.group') }}:</strong> {{ event.group_name }}</div>
                    <div><strong>{{ t('events.forms.location') }}:</strong> {{ event.location }}</div>
                    <div><strong>{{ t('events.forms.starts_at') }}:</strong> {{ event.starts_at_label }}</div>
                    <div><strong>{{ t('events.forms.ends_at') }}:</strong> {{ event.ends_at_label }}</div>
                    <div><strong>{{ t('events.forms.all_day') }}:</strong> {{ event.all_day ? t('forms.status.active') : t('forms.status.inactive') }}</div>
                    <div><strong>{{ t('forms.status.label') }}:</strong> {{ event.is_active ? t('forms.status.active') : t('forms.status.inactive') }}</div>
                    <div><strong>{{ t('events.forms.description') }}:</strong> {{ event.description }}</div>
                    <div><strong>{{ t('events.forms.body') }}:</strong> {{ event.body }}</div>
                </div>
            </template>
        </PCard>
    </AppShell>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';

import { t } from '../../../i18n';
import AppShell from '../../../layouts/AppShell.vue';
import { showEvent } from '../../../api/modules/events';

const route = useRoute();
const message = ref('');

const event = reactive({
    title: 'Culto de domingo',
    group_name: '—',
    location: 'Auditório principal',
    starts_at_label: '—',
    ends_at_label: '—',
    all_day: false,
    is_active: true,
    description: '',
    body: '',
});

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
        const response = await showEvent(route.params.id);
        Object.assign(event, response.data, {
            group_name: response.data.group?.name ?? '—',
            starts_at_label: formatDateTime(response.data.starts_at),
            ends_at_label: formatDateTime(response.data.ends_at),
        });
    } catch {
        message.value = t('forms.messages.auth_required_load');
    }
});
</script>
