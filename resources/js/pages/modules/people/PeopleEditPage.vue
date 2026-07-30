<template>
    <AppShell :title="t('people.edit.title')">
        <PCard>
            <template #title>{{ t('people.edit.heading') }}</template>
            <template #content>
                <form class="stack-form" @submit.prevent="submit">
                    <BaseTextField v-model="form.first_name" :label="t('people.forms.first_name')" />
                    <BaseTextField v-model="form.last_name" :label="t('people.forms.last_name')" />
                    <BaseTextField v-model="form.birth_date" :label="t('people.forms.birth_date')" type="date" />
                    <BaseSelectField v-model="form.newsletter_enabled" :label="t('forms.status.label')" :options="statusOptions" />

                    <div v-if="message" style="color: var(--app-accent); font-weight: 600;">
                        {{ message }}
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <PButton type="submit" :label="t('people.actions.update_person')" icon="pi pi-save" />
                        <PButton :label="t('forms.actions.back')" severity="secondary" text @click="$router.push('/people')" />
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
import { showPerson, updatePerson } from '../../../api/modules/people';

const route = useRoute();
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

onMounted(async () => {
    try {
        const response = await showPerson(route.params.id);
        // The API wraps single resources in a `data` envelope. No fallback
        // to the raw body: that would silently re-create the bug this
        // fixes (assigning a literal `data` key) instead of failing loudly.
        Object.assign(form, response.data.data);
    } catch {
        message.value = t('forms.messages.auth_required_load');
    }
});

async function submit() {
    try {
        await updatePerson(route.params.id, form);
        message.value = t('forms.messages.updated');
    } catch {
        message.value = t('forms.messages.auth_required_update');
    }
}
</script>
