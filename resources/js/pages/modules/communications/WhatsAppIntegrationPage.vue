<template>
    <AppShell :title="t('communications.integration.title')">
        <PCard>
            <template #title>{{ t('communications.integration.title') }}</template>
            <template #content>
                <form class="stack-form" @submit.prevent="submit">
                    <BaseSelectField
                        v-model="form.provider"
                        :label="t('communications.integration.provider')"
                        :options="providerOptions"
                        :placeholder="t('communications.integration.provider_placeholder')"
                    />
                    <BaseSelectField
                        v-model="form.enabled"
                        :label="t('communications.integration.enabled')"
                        :options="statusOptions"
                        :placeholder="t('forms.status.placeholder')"
                    />

                    <div class="surface-grid">
                        <PCard>
                            <template #title>{{ t('communications.integration.waha_title') }}</template>
                            <template #content>
                                <div class="stack-form">
                                    <BaseTextField v-model="form.settings.base_url" :label="t('communications.integration.waha_base_url')" placeholder="http://waha:3000" />
                                    <BaseTextField v-model="form.settings.instance" :label="t('communications.integration.waha_instance')" placeholder="church" />
                                </div>
                            </template>
                        </PCard>

                        <PCard>
                            <template #title>{{ t('communications.integration.meta_title') }}</template>
                            <template #content>
                                <div class="stack-form">
                                    <BaseTextField v-model="form.settings.phone_number_id" :label="t('communications.integration.meta_phone_number_id')" placeholder="123456789" />
                                    <BaseTextField v-model="form.settings.waba_id" :label="t('communications.integration.meta_waba_id')" placeholder="123456789" />
                                    <BaseTextField v-model="form.settings.access_token" :label="t('communications.integration.meta_access_token')" placeholder="EAAB..." />
                                </div>
                            </template>
                        </PCard>
                    </div>

                    <div v-if="message" style="color: var(--app-accent); font-weight: 600;">
                        {{ message }}
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <PButton type="submit" :label="t('communications.integration.save')" icon="pi pi-check" />
                        <PButton :label="t('forms.actions.back')" severity="secondary" text @click="$router.push('/communications')" />
                    </div>
                </form>
            </template>
        </PCard>
    </AppShell>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';

import { t } from '../../../i18n';
import AppShell from '../../../layouts/AppShell.vue';
import BaseTextField from '../../../components/forms/BaseTextField.vue';
import BaseSelectField from '../../../components/forms/BaseSelectField.vue';
import { getWhatsAppIntegration, saveWhatsAppIntegration } from '../../../api/modules/communications';

const message = ref('');

const form = reactive({
    provider: null,
    enabled: false,
    settings: {
        base_url: '',
        instance: '',
        phone_number_id: '',
        waba_id: '',
        access_token: '',
    },
});

const providerOptions = [
    { label: t('communications.providers.waha'), value: 'waha' },
    { label: t('communications.providers.meta'), value: 'meta' },
];

const statusOptions = [
    { label: t('forms.status.active'), value: true },
    { label: t('forms.status.inactive'), value: false },
];

function applyPayload(payload) {
    form.provider = payload?.provider ?? null;
    form.enabled = Boolean(payload?.enabled);
    form.settings = {
        base_url: payload?.settings?.base_url ?? '',
        instance: payload?.settings?.instance ?? '',
        phone_number_id: payload?.settings?.phone_number_id ?? '',
        waba_id: payload?.settings?.waba_id ?? '',
        access_token: payload?.settings?.access_token ?? '',
    };
}

onMounted(async () => {
    try {
        const response = await getWhatsAppIntegration();
        applyPayload(response.data);
    } catch {
        applyPayload({
            provider: null,
            enabled: false,
            settings: {},
        });
    }
});

async function submit() {
    try {
        await saveWhatsAppIntegration(form);
        message.value = t('communications.integration.saved');
    } catch {
        message.value = t('communications.integration.save_error');
    }
}
</script>
