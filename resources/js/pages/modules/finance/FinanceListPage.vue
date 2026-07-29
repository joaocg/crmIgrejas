<template>
    <AppShell :title="t('finance.title')">
        <div class="hero-panel">
            <div class="hero-panel__eyebrow">{{ t('finance.eyebrow') }}</div>
            <h1 class="hero-panel__title">{{ t('finance.title') }}</h1>
            <p class="hero-panel__copy">{{ t('finance.copy') }}</p>

            <div class="surface-grid">
                <PCard>
                    <template #title>{{ t('finance.summary.funds') }}</template>
                    <template #content>
                        <strong>{{ stats.funds }}</strong>
                        <div class="app-brand__subtitle">{{ t('finance.summary.funds_copy') }}</div>
                    </template>
                </PCard>

                <PCard>
                    <template #title>{{ t('finance.summary.deposits') }}</template>
                    <template #content>
                        <strong>{{ stats.deposits }}</strong>
                        <div class="app-brand__subtitle">{{ t('finance.summary.deposits_copy') }}</div>
                    </template>
                </PCard>

                <PCard>
                    <template #title>{{ t('finance.summary.pledges') }}</template>
                    <template #content>
                        <strong>{{ stats.pledges }}</strong>
                        <div class="app-brand__subtitle">{{ t('finance.summary.pledges_copy') }}</div>
                    </template>
                </PCard>
            </div>
        </div>

        <div class="surface-grid" style="margin-top: 24px;">
            <PCard>
                <template #title>{{ t('finance.funds.title') }}</template>
                <template #content>
                    <BaseDataTable :rows="fundRows" :rows-per-page="5">
                        <PColumn field="name" :header="t('finance.funds.columns.name')" />
                        <PColumn field="description" :header="t('finance.funds.columns.description')" />
                        <PColumn field="active_label" :header="t('finance.funds.columns.active')" />
                    </BaseDataTable>
                </template>
            </PCard>

            <PCard>
                <template #title>{{ t('finance.deposits.title') }}</template>
                <template #content>
                    <BaseDataTable :rows="depositRows" :rows-per-page="5">
                        <PColumn field="date_label" :header="t('finance.deposits.columns.date')" />
                        <PColumn field="fund_name" :header="t('finance.deposits.columns.fund')" />
                        <PColumn field="type" :header="t('finance.deposits.columns.type')" />
                        <PColumn field="closed_label" :header="t('finance.deposits.columns.closed')" />
                    </BaseDataTable>
                </template>
            </PCard>
        </div>

        <PCard style="margin-top: 24px;">
            <template #title>{{ t('finance.pledges.title') }}</template>
            <template #content>
                <BaseDataTable :rows="pledgeRows" :rows-per-page="5">
                    <PColumn field="family_name" :header="t('finance.pledges.columns.family')" />
                    <PColumn field="fund_name" :header="t('finance.pledges.columns.fund')" />
                    <PColumn field="amount" :header="t('finance.pledges.columns.amount')" />
                    <PColumn field="status" :header="t('finance.pledges.columns.status')" />
                </BaseDataTable>
            </template>
        </PCard>
    </AppShell>
</template>

<script setup>
import { onMounted, reactive } from 'vue';

import { t } from '../../../i18n';
import AppShell from '../../../layouts/AppShell.vue';
import BaseDataTable from '../../../components/tables/BaseDataTable.vue';
import { listDonationFunds, listDeposits, listPledges } from '../../../api/modules/finance';

const stats = reactive({
    funds: '0',
    deposits: '0',
    pledges: '0',
});

const fundRows = reactive([]);
const depositRows = reactive([]);
const pledgeRows = reactive([]);

function formatDate(value) {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'short',
    }).format(new Date(value));
}

onMounted(async () => {
    try {
        const [fundsResponse, depositsResponse, pledgesResponse] = await Promise.all([
            listDonationFunds(),
            listDeposits(),
            listPledges(),
        ]);

        const funds = fundsResponse.data?.data ?? fundsResponse.data ?? [];
        const deposits = depositsResponse.data?.data ?? depositsResponse.data ?? [];
        const pledges = pledgesResponse.data?.data ?? pledgesResponse.data ?? [];

        fundRows.splice(0, fundRows.length, ...funds.map((fund) => ({
            ...fund,
            active_label: fund.active ? t('forms.status.active') : t('forms.status.inactive'),
        })));

        depositRows.splice(0, depositRows.length, ...deposits.map((deposit) => ({
            ...deposit,
            fund_name: deposit.fund?.name ?? '—',
            date_label: formatDate(deposit.date),
            closed_label: deposit.closed ? t('forms.status.active') : t('forms.status.inactive'),
        })));

        pledgeRows.splice(0, pledgeRows.length, ...pledges.map((pledge) => ({
            ...pledge,
            family_name: pledge.family?.name ?? '—',
            fund_name: pledge.fund?.name ?? '—',
            amount: pledge.amount,
        })));

        stats.funds = String(fundRows.length);
        stats.deposits = String(depositRows.length);
        stats.pledges = String(pledgeRows.length);
    } catch {
        fundRows.splice(0, fundRows.length, {
            name: t('finance.fallback_fund.name'),
            description: t('finance.fallback_fund.description'),
            active_label: t('forms.status.active'),
        });
        depositRows.splice(0, depositRows.length, {
            date_label: t('finance.fallback_deposit.date'),
            fund_name: t('finance.fallback_deposit.fund'),
            type: t('finance.fallback_deposit.type'),
            closed_label: t('forms.status.active'),
        });
        pledgeRows.splice(0, pledgeRows.length, {
            family_name: t('finance.fallback_pledge.family'),
            fund_name: t('finance.fallback_pledge.fund'),
            amount: t('finance.fallback_pledge.amount'),
            status: t('finance.fallback_pledge.status'),
        });
    }
});
</script>
