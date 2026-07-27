<template>
    <AppShell :title="t('people.create.title')">
        <PCard>
            <template #title>{{ t('people.create.heading') }}</template>
            <template #content>
                <form class="stack-form" @submit.prevent="submit">
                    <BaseTextField v-model="form.first_name" :label="t('people.forms.first_name')" placeholder="Maria" />
                    <BaseTextField v-model="form.last_name" :label="t('people.forms.last_name')" placeholder="Silva" />
                    <BaseTextField v-model="form.birth_date" :label="t('people.forms.birth_date')" placeholder="1990-01-01" type="date" />
                    <BaseSelectField
                        v-model="form.newsletter_enabled"
                        :label="t('forms.status.label')"
                        :options="statusOptions"
                        :placeholder="t('forms.status.placeholder')"
                    />

                    <div v-if="message" style="color: var(--app-accent); font-weight: 600;">
                        {{ message }}
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <PButton type="submit" :label="t('people.actions.save_person')" icon="pi pi-check" />
                        <PButton :label="t('forms.actions.cancel')" severity="secondary" text @click="$router.push('/people')" />
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
import { createPerson } from '../../../api/modules/people';

const message = ref('');

const form = reactive({
    tenant_id: 1,
    first_name: '',
    last_name: '',
    birth_date: '',
    newsletter_enabled: true,
});

const statusOptions = [
    { label: t('forms.status.active'), value: true },
    { label: t('forms.status.inactive'), value: false },
];

async function submit() {
    try {
        await createPerson(form);
        message.value = t('forms.messages.saved');
    } catch {
        message.value = t('forms.messages.auth_required_create');
    }
}
</script>
