<template>
    <AppShell :title="t('groups.title')">
        <div class="hero-panel">
            <div class="hero-panel__eyebrow">{{ t('groups.eyebrow') }}</div>
            <h1 class="hero-panel__title">{{ t('groups.title') }}</h1>
            <p class="hero-panel__copy">{{ t('groups.copy') }}</p>
        </div>

        <PCard style="margin-top: 24px;">
            <template #title>{{ t('groups.registered') }}</template>
            <template #content>
                <div v-if="error" style="margin-bottom: 16px; color: var(--app-accent); font-weight: 600;">
                    {{ error }}
                </div>

                <BaseDataTable :rows="groups" :rows-per-page="8">
                    <PColumn field="name" :header="t('groups.columns.name')" />
                    <PColumn field="type" :header="t('groups.columns.type')" />
                    <PColumn field="description" :header="t('groups.columns.description')" />
                    <PColumn field="is_active" :header="t('groups.columns.is_active')" />
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
import { listGroups } from '../../../api/modules/groups';

const groups = ref([]);
const error = ref('');

onMounted(async () => {
    try {
        const response = await listGroups();
        groups.value = response.data?.data ?? response.data ?? [];
    } catch {
        error.value = t('forms.messages.auth_required_load');
        groups.value = [
            {
                name: t('groups.fallback_group.name'),
                type: t('groups.fallback_group.type'),
                description: t('groups.fallback_group.description'),
                is_active: true,
            },
        ];
    }
});
</script>
