<template>
    <AppShell :title="t('care.records.create.title')">
        <PCard>
            <template #title>{{ t('care.records.create.heading') }}</template>
            <template #content>
                <form class="stack-form" @submit.prevent="submit">
                    <BaseTextField v-model="form.person_id" :label="t('care.forms.person_id')" type="number" />
                    <BaseTextField v-model="form.family_id" :label="t('care.forms.family_id')" type="number" />
                    <BaseTextField v-model="form.pastor_user_id" :label="t('care.forms.pastor_user_id')" type="number" />
                    <BaseTextField v-model="form.pastor_name" :label="t('care.forms.pastor_name')" />
                    <BaseTextField v-model="form.type" :label="t('care.forms.type')" />
                    <BaseTextField v-model="form.body" :label="t('care.forms.body')" />
                    <BaseSelectField
                        v-model="form.visible"
                        :label="t('care.forms.visible')"
                        :options="visibilityOptions"
                        :placeholder="t('forms.status.placeholder')"
                    />

                    <div v-if="message" style="color: var(--app-accent); font-weight: 600;">
                        {{ message }}
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <PButton type="submit" :label="t('care.records.actions.save_record')" icon="pi pi-check" />
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
import { createPastoralCareRecord } from '../../../api/modules/care';

const message = ref('');

const form = reactive({
    tenant_id: 1,
    person_id: '',
    family_id: '',
    pastor_user_id: '',
    pastor_name: '',
    type: '',
    body: '',
    visible: true,
});

const visibilityOptions = [
    { label: t('forms.status.inactive'), value: false },
    { label: t('forms.status.active'), value: true },
];

async function submit() {
    try {
        await createPastoralCareRecord(form);
        message.value = t('forms.messages.saved');
    } catch {
        message.value = t('forms.messages.auth_required_create');
    }
}
</script>
