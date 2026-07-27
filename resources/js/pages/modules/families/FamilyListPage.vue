<template>
    <AppShell :title="t('families.title')">
        <div class="hero-panel">
            <div class="hero-panel__eyebrow">{{ t('families.eyebrow') }}</div>
            <h1 class="hero-panel__title">{{ t('families.title') }}</h1>
            <p class="hero-panel__copy">{{ t('families.copy') }}</p>
        </div>

        <PCard style="margin-top: 24px;">
            <template #title>{{ t('families.registered') }}</template>
            <template #content>
                <div v-if="error" style="margin-bottom: 16px; color: var(--app-accent); font-weight: 600;">
                    {{ error }}
                </div>

                <BaseDataTable :rows="families" :rows-per-page="8">
                    <PColumn field="name" :header="t('families.columns.name')" />
                    <PColumn field="email" :header="t('families.columns.email')" />
                    <PColumn field="mobile_phone" :header="t('families.columns.mobile_phone')" />
                    <PColumn field="newsletter_enabled" :header="t('families.columns.newsletter_enabled')" />
                </BaseDataTable>
            </template>
        </PCard>
    </AppShell>
</template>

<script setup>
import { onMounted, ref } from 'vue';

import { t } from '../../../i18n';
import AppShell from '../../../layouts/AppShell.vue';
import BaseDataTable from '../../../components/tables/BaseDataTable.vue';
import { listFamilies } from '../../../api/modules/families';

const families = ref([]);
const error = ref('');

onMounted(async () => {
    try {
        const response = await listFamilies();
        families.value = response.data?.data ?? response.data ?? [];
    } catch {
        error.value = t('forms.messages.auth_required_load');
        families.value = [
            {
                name: t('families.fallback_family.name'),
                email: t('families.fallback_family.email'),
                mobile_phone: t('families.fallback_family.mobile_phone'),
                newsletter_enabled: true,
            },
        ];
    }
});
</script>
