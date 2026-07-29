<template>
    <AppShell :title="t('events.create.title')">
        <PCard>
            <template #title>{{ t('events.create.heading') }}</template>
            <template #content>
                <form class="stack-form" @submit.prevent="submit">
                    <BaseSelectField
                        v-model="form.group_id"
                        :label="t('events.forms.group')"
                        :options="groupOptions"
                        :placeholder="t('events.forms.group_placeholder')"
                    />
                    <BaseTextField v-model="form.title" :label="t('events.forms.title')" placeholder="Culto de domingo" />
                    <BaseTextField v-model="form.location" :label="t('events.forms.location')" placeholder="Auditório principal" />
                    <BaseTextField v-model="form.starts_at" :label="t('events.forms.starts_at')" type="datetime-local" />
                    <BaseTextField v-model="form.ends_at" :label="t('events.forms.ends_at')" type="datetime-local" />
                    <BaseSelectField
                        v-model="form.all_day"
                        :label="t('events.forms.all_day')"
                        :options="booleanOptions"
                        :placeholder="t('forms.status.placeholder')"
                    />
                    <BaseTextField v-model="form.description" :label="t('events.forms.description')" placeholder="Descrição resumida" />
                    <BaseTextField v-model="form.body" :label="t('events.forms.body')" placeholder="Conteúdo completo do evento" />
                    <BaseTextField v-model="form.calendar_uid" :label="t('events.forms.calendar_uid')" placeholder="uid-externo" />
                    <BaseTextField v-model="form.calendar_url" :label="t('events.forms.calendar_url')" placeholder="https://..." />
                    <BaseTextField v-model="form.source" :label="t('events.forms.source')" placeholder="manual" />
                    <BaseSelectField
                        v-model="form.is_active"
                        :label="t('forms.status.label')"
                        :options="statusOptions"
                        :placeholder="t('forms.status.placeholder')"
                    />

                    <div v-if="message" style="color: var(--app-accent); font-weight: 600;">
                        {{ message }}
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <PButton type="submit" :label="t('events.actions.save_event')" icon="pi pi-check" />
                        <PButton :label="t('forms.actions.cancel')" severity="secondary" text @click="$router.push('/events')" />
                    </div>
                </form>
            </template>
        </PCard>
    </AppShell>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';

import { t } from '../../../i18n';
import AppShell from '../../../layouts/AppShell.vue';
import BaseTextField from '../../../components/forms/BaseTextField.vue';
import BaseSelectField from '../../../components/forms/BaseSelectField.vue';
import { createEvent } from '../../../api/modules/events';
import { listGroups } from '../../../api/modules/groups';

const message = ref('');
const groups = ref([]);

const form = reactive({
    tenant_id: 1,
    group_id: null,
    title: '',
    description: '',
    body: '',
    location: '',
    starts_at: '',
    ends_at: '',
    all_day: false,
    calendar_uid: '',
    calendar_url: '',
    source: 'manual',
    is_active: true,
});

const groupOptions = computed(() => [
    { label: t('events.forms.group_none'), value: null },
    ...groups.value.map((group) => ({
        label: group.name,
        value: group.id,
    })),
]);

const statusOptions = [
    { label: t('forms.status.active'), value: true },
    { label: t('forms.status.inactive'), value: false },
];

const booleanOptions = [
    { label: t('forms.status.inactive'), value: false },
    { label: t('forms.status.active'), value: true },
];

onMounted(async () => {
    try {
        const response = await listGroups();
        groups.value = response.data?.data ?? response.data ?? [];
    } catch {
        groups.value = [];
    }
});

async function submit() {
    try {
        await createEvent(form);
        message.value = t('forms.messages.saved');
    } catch {
        message.value = t('forms.messages.auth_required_create');
    }
}
</script>
