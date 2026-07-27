<template>
    <AppShell :title="t('users.edit.title')">
        <PCard>
            <template #title>{{ t('users.edit.heading') }}</template>
            <template #content>
                <form class="stack-form" @submit.prevent="submit">
                    <BaseTextField v-model="form.name" :label="t('forms.name')" />
                    <BaseTextField v-model="form.email" :label="t('forms.email')" type="email" />
                    <BaseSelectField v-model="form.locale" :label="t('forms.locale.label')" :options="localeOptions" />
                    <BaseSelectField v-model="form.active" :label="t('forms.status.label')" :options="statusOptions" />

                    <div v-if="message" style="color: var(--app-accent); font-weight: 600;">
                        {{ message }}
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <PButton type="submit" :label="t('forms.actions.update_user')" icon="pi pi-save" />
                        <PButton :label="t('forms.actions.back')" severity="secondary" text @click="$router.push('/users')" />
                    </div>
                </form>
            </template>
        </PCard>
    </AppShell>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';

import { t } from '../../../i18n';
import AppShell from '../../../layouts/AppShell.vue';
import BaseTextField from '../../../components/forms/BaseTextField.vue';
import BaseSelectField from '../../../components/forms/BaseSelectField.vue';
import { showUser, updateUser } from '../../../api/modules/users';

const route = useRoute();
const message = ref('');
const form = reactive({
    name: '',
    email: '',
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

onMounted(async () => {
    try {
        const response = await showUser(route.params.id);
        Object.assign(form, response.data);
    } catch {
        message.value = t('forms.messages.auth_required_load');
    }
});

async function submit() {
    try {
        await updateUser(route.params.id, form);
        message.value = t('forms.messages.updated');
    } catch {
        message.value = t('forms.messages.auth_required_update');
    }
}
</script>
