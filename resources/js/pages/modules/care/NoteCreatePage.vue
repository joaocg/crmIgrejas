<template>
    <AppShell :title="t('care.notes.create.title')">
        <PCard>
            <template #title>{{ t('care.notes.create.heading') }}</template>
            <template #content>
                <form class="stack-form" @submit.prevent="submit">
                    <BaseTextField v-model="form.person_id" :label="t('care.forms.person_id')" type="number" />
                    <BaseTextField v-model="form.family_id" :label="t('care.forms.family_id')" type="number" />
                    <BaseTextField v-model="form.title" :label="t('care.forms.title')" />
                    <BaseTextField v-model="form.type" :label="t('care.forms.type')" />
                    <BaseTextField v-model="form.info" :label="t('care.forms.info')" />
                    <BaseTextField v-model="form.body" :label="t('care.forms.body')" />
                    <BaseSelectField
                        v-model="form.is_private"
                        :label="t('care.forms.private')"
                        :options="visibilityOptions"
                        :placeholder="t('forms.status.placeholder')"
                    />

                    <div v-if="message" style="color: var(--app-accent); font-weight: 600;">
                        {{ message }}
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <PButton type="submit" :label="t('care.notes.actions.save_note')" icon="pi pi-check" />
                        <PButton :label="t('forms.actions.cancel')" severity="secondary" text @click="$router.push('/care')" />
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
import { createNote } from '../../../api/modules/care';

const message = ref('');

const form = reactive({
    tenant_id: 1,
    person_id: '',
    family_id: '',
    title: '',
    body: '',
    type: '',
    info: '',
    is_private: false,
});

const visibilityOptions = [
    { label: t('forms.status.inactive'), value: true },
    { label: t('forms.status.active'), value: false },
];

async function submit() {
    try {
        await createNote(form);
        message.value = t('forms.messages.saved');
    } catch {
        message.value = t('forms.messages.auth_required_create');
    }
}
</script>
