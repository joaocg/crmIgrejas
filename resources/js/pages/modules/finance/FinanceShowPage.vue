<template>
    <AppShell :title="t('finance.funds.show.title')">
        <PCard>
            <template #title>{{ t('finance.funds.show.heading') }}</template>
            <template #content>
                <div v-if="message" style="margin-bottom: 16px; color: var(--app-accent); font-weight: 600;">
                    {{ message }}
                </div>

                <div class="stack-form">
                    <div><strong>{{ t('finance.funds.forms.name') }}:</strong> {{ fund.name }}</div>
                    <div><strong>{{ t('finance.funds.forms.description') }}:</strong> {{ fund.description }}</div>
                    <div><strong>{{ t('forms.status.label') }}:</strong> {{ fund.active ? t('forms.status.active') : t('forms.status.inactive') }}</div>
                </div>
            </template>
        </PCard>
    </AppShell>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';

import { t } from '../../../i18n';
import AppShell from '../../../layouts/AppShell.vue';
import { showDonationFund } from '../../../api/modules/finance';

const route = useRoute();
const message = ref('');

const fund = reactive({
    name: 'Oferta geral',
    description: '—',
    active: true,
});

onMounted(async () => {
    try {
        const response = await showDonationFund(route.params.id);
        Object.assign(fund, response.data);
    } catch {
        message.value = t('forms.messages.auth_required_load');
    }
});
</script>
