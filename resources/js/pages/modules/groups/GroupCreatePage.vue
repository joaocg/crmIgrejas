<template>
    <AppShell :title="t('groups.create.title')">
        <PCard>
            <template #title>{{ t('groups.create.heading') }}</template>
            <template #content>
                <form class="stack-form" @submit.prevent="submit">
                    <BaseTextField v-model="form.name" :label="t('groups.forms.name')" placeholder="Pequeno Grupo" />
                    <BaseTextField v-model="form.type" :label="t('groups.forms.type')" placeholder="small-group" />
                    <BaseTextField v-model="form.description" :label="t('groups.forms.description')" placeholder="Reunião semanal" />
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
                        <PButton type="submit" :label="t('groups.actions.save_group')" icon="pi pi-check" />
                        <PButton :label="t('forms.actions.cancel')" severity="secondary" text @click="$router.push('/groups')" />
                    </div>
                </form>
            </template>
        </PCard>
    </AppShell>
</template>

<script setup>
import { reactive, ref } from 'vue';

import { t } from '../../../i18n';
import AppShell from '../../../layouts/AppShell.vue';
import BaseTextField from '../../../components/forms/BaseTextField.vue';
import BaseSelectField from '../../../components/forms/BaseSelectField.vue';
import { createGroup } from '../../../api/modules/groups';

const message = ref('');

const form = reactive({
    tenant_id: 1,
    name: '',
    type: '',
    description: '',
    is_active: true,
});

const statusOptions = [
    { label: t('forms.status.active'), value: true },
    { label: t('forms.status.inactive'), value: false },
];

async function submit() {
    try {
        await createGroup(form);
        message.value = t('forms.messages.saved');
    } catch {
        message.value = t('forms.messages.auth_required_create');
    }
}
</script>
