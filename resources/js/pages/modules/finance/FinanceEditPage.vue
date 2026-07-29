<template>
    <AppShell :title="t('finance.funds.edit.title')">
        <PCard>
            <template #title>{{ t('finance.funds.edit.heading') }}</template>
            <template #content>
                <form class="stack-form" @submit.prevent="submit">
                    <BaseTextField v-model="form.name" :label="t('finance.funds.forms.name')" />
                    <BaseTextField v-model="form.description" :label="t('finance.funds.forms.description')" />
                    <BaseSelectField
                        v-model="form.active"
                        :label="t('forms.status.label')"
                        :options="statusOptions"
                    />

                    <div v-if="message" style="color: var(--app-accent); font-weight: 600;">
                        {{ message }}
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <PButton type="submit" :label="t('finance.funds.actions.update_fund')" icon="pi pi-save" />
                        <PButton :label="t('forms.actions.back')" severity="secondary" text @click="$router.push('/finance')" />
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
import { showDonationFund, updateDonationFund } from '../../../api/modules/finance';

const route = useRoute();
const message = ref('');

const form = reactive({
    tenant_id: 1,
    name: '',
    description: '',
    active: true,
});

const statusOptions = [
    { label: t('forms.status.active'), value: true },
    { label: t('forms.status.inactive'), value: false },
];

onMounted(async () => {
    try {
        const response = await showDonationFund(route.params.id);
        Object.assign(form, response.data);
    } catch {
        message.value = t('forms.messages.auth_required_load');
    }
});

async function submit() {
    try {
        await updateDonationFund(route.params.id, form);
        message.value = t('forms.messages.updated');
    } catch {
        message.value = t('forms.messages.auth_required_update');
    }
}
</script>
