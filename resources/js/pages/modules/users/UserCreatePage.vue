<template>
    <AppShell :title="t('users.create.title')">
        <PCard>
            <template #title>{{ t('users.create.heading') }}</template>
            <template #content>
                <form class="stack-form" @submit.prevent="submit">
                    <BaseTextField v-model="form.name" :label="t('forms.name')" placeholder="Jane Doe" />
                    <BaseTextField v-model="form.email" :label="t('forms.email')" placeholder="jane@example.org" type="email" />
                    <BaseTextField v-model="form.password" :label="t('forms.password')" placeholder="••••••••" type="password" />
                    <BaseSelectField
                        v-model="form.locale"
                        :label="t('forms.locale.label')"
                        :options="localeOptions"
                        :placeholder="t('forms.locale.placeholder')"
                    />
                    <BaseSelectField
                        v-model="form.active"
                        :label="t('forms.status.label')"
                        :options="statusOptions"
                        :placeholder="t('forms.status.placeholder')"
                    />

                    <div v-if="message" style="color: var(--app-accent); font-weight: 600;">
                        {{ message }}
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <PButton type="submit" :label="t('forms.actions.save_user')" icon="pi pi-check" />
                        <PButton :label="t('forms.actions.cancel')" severity="secondary" text @click="$router.push('/users')" />
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
import { createUser } from '../../../api/modules/users';

const message = ref('');
const form = reactive({
    name: '',
    email: '',
    password: '',
    locale: 'pt_BR',
    active: true,
});

const localeOptions = [
    { label: t('forms.locale.pt_br'), value: 'pt_BR' },
    { label: t('forms.locale.en'), value: 'en' },
];

const statusOptions = [
    { label: t('forms.status.active'), value: true },
    { label: t('forms.status.inactive'), value: false },
];

async function submit() {
    try {
        await createUser(form);
        message.value = t('forms.messages.saved');
    } catch {
        message.value = t('forms.messages.auth_required_create');
    }
}
</script>
