<template>
    <AppShell :title="t('communications.title')">
        <div class="hero-panel">
            <div class="hero-panel__eyebrow">{{ t('communications.eyebrow') }}</div>
            <h1 class="hero-panel__title">{{ t('communications.title') }}</h1>
            <p class="hero-panel__copy">{{ t('communications.copy') }}</p>

            <div class="surface-grid">
                <PCard>
                    <template #title>{{ t('communications.summary.channel') }}</template>
                    <template #content>
                        <strong>{{ integration.provider_label }}</strong>
                        <div class="app-brand__subtitle">{{ t('communications.summary.channel_copy') }}</div>
                    </template>
                </PCard>

                <PCard>
                    <template #title>{{ t('communications.summary.status') }}</template>
                    <template #content>
                        <strong>{{ integration.enabled_label }}</strong>
                        <div class="app-brand__subtitle">{{ t('communications.summary.status_copy') }}</div>
                    </template>
                </PCard>

                <PCard>
                    <template #title>{{ t('communications.summary.next_step') }}</template>
                    <template #content>
                        <strong>{{ t('communications.summary.next_step_value') }}</strong>
                        <div class="app-brand__subtitle">{{ t('communications.summary.next_step_copy') }}</div>
                    </template>
                </PCard>
            </div>
        </div>

        <div class="surface-grid" style="margin-top: 24px;">
            <PCard>
                <template #title>{{ t('communications.integration.title') }}</template>
                <template #content>
                    <div class="stack-form">
                        <div><strong>{{ t('communications.integration.provider') }}:</strong> {{ integration.provider_label }}</div>
                        <div><strong>{{ t('communications.integration.mode') }}:</strong> {{ integration.mode_label }}</div>
                        <div><strong>{{ t('communications.integration.endpoint') }}:</strong> {{ integration.endpoint_label }}</div>
                        <div><strong>{{ t('communications.integration.instance') }}:</strong> {{ integration.instance_label }}</div>
                    </div>
                </template>
            </PCard>

            <PCard>
                <template #title>{{ t('communications.integration.actions_title') }}</template>
                <template #content>
                    <div class="stack-form">
                        <PButton :label="t('communications.integration.configure')" icon="pi pi-cog" @click="$router.push('/communications/whatsapp')" />
                        <PButton :label="t('communications.integration.open_whatsapp')" severity="secondary" text @click="$router.push('/communications/whatsapp')" />
                    </div>
                </template>
            </PCard>
        </div>
    </AppShell>
</template>

<script setup>
import { onMounted, reactive } from 'vue';

import { t } from '../../../i18n';
import AppShell from '../../../layouts/AppShell.vue';
import { getWhatsAppIntegration } from '../../../api/modules/communications';

const integration = reactive({
    provider: null,
    provider_label: t('communications.integration.none'),
    enabled: false,
    enabled_label: t('forms.status.inactive'),
    mode_label: '—',
    endpoint_label: '—',
    instance_label: '—',
});

function resolveLabels(payload) {
    const provider = payload?.provider ?? null;
    const settings = payload?.settings ?? {};

    integration.provider = provider;
    integration.provider_label = provider === 'meta'
        ? t('communications.providers.meta')
        : provider === 'waha'
            ? t('communications.providers.waha')
            : t('communications.integration.none');

    integration.enabled = Boolean(payload?.enabled);
    integration.enabled_label = integration.enabled ? t('forms.status.active') : t('forms.status.inactive');

    if (provider === 'waha') {
        integration.mode_label = t('communications.providers.waha_mode');
        integration.endpoint_label = settings.base_url || '—';
        integration.instance_label = settings.instance || '—';
        return;
    }

    if (provider === 'meta') {
        integration.mode_label = t('communications.providers.meta_mode');
        integration.endpoint_label = settings.phone_number_id || '—';
        integration.instance_label = settings.waba_id || '—';
        return;
    }

    integration.mode_label = '—';
    integration.endpoint_label = '—';
    integration.instance_label = '—';
}

onMounted(async () => {
    try {
        const response = await getWhatsAppIntegration();
        resolveLabels(response.data);
    } catch {
        resolveLabels({
            provider: null,
            enabled: false,
            settings: {},
        });
    }
});
</script>
