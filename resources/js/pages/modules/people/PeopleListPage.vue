<template>
    <AppShell :title="t('people.title')">
        <div class="hero-panel">
            <div class="hero-panel__eyebrow">{{ t('people.eyebrow') }}</div>
            <h1 class="hero-panel__title">{{ t('people.title') }}</h1>
            <p class="hero-panel__copy">{{ t('people.copy') }}</p>
        </div>

        <PCard style="margin-top: 24px;">
            <template #title>{{ t('people.registered') }}</template>
            <template #content>
                <div v-if="error" style="margin-bottom: 16px; color: var(--app-accent); font-weight: 600;">
                    {{ error }}
                </div>

                <BaseDataTable :rows="people" :rows-per-page="8">
                    <PColumn field="first_name" :header="t('people.columns.first_name')" />
                    <PColumn field="last_name" :header="t('people.columns.last_name')" />
                    <PColumn field="birth_date" :header="t('people.columns.birth_date')" />
                    <PColumn field="newsletter_enabled" :header="t('people.columns.newsletter_enabled')" />
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
import { listPeople } from '../../../api/modules/people';

const people = ref([]);
const error = ref('');

onMounted(async () => {
    try {
        const response = await listPeople();
        people.value = response.data?.data ?? response.data ?? [];
    } catch {
        error.value = t('forms.messages.auth_required_load');
        people.value = [
            {
                first_name: t('people.fallback_person.first_name'),
                last_name: t('people.fallback_person.last_name'),
                birth_date: t('people.fallback_person.birth_date'),
                newsletter_enabled: true,
            },
        ];
    }
});
</script>
