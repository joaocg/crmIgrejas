<template>
    <AppShell :title="t('finance.funds.create.title')">
        <PCard>
            <template #title>{{ t('finance.funds.create.heading') }}</template>
            <template #content>
                <form class="stack-form" @submit.prevent="submit">
                    <BaseTextField v-model="form.name" :label="t('finance.funds.forms.name')" placeholder="Oferta geral" />
                    <BaseTextField v-model="form.description" :label="t('finance.funds.forms.description')" placeholder="Descrição resumida" />
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
                        <PButton type="submit" :label="t('finance.funds.actions.save_fund')" icon="pi pi-check" />
                        <PButton :label="t('forms.actions.cancel')" severity="secondary" text @click="$router.push('/finance')" />
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
import { createDonationFund } from '../../../api/modules/finance';

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

async function submit() {
    try {
        await createDonationFund(form);
        message.value = t('forms.messages.saved');
    } catch {
        message.value = t('forms.messages.auth_required_create');
    }
}
</script>
