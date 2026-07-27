<template>
    <AppShell :title="t('users.title')">
        <div class="hero-panel">
            <div class="hero-panel__eyebrow">{{ t('users.eyebrow') }}</div>
            <h1 class="hero-panel__title">{{ t('users.title') }}</h1>
            <p class="hero-panel__copy">{{ t('users.copy') }}</p>
        </div>

        <PCard style="margin-top: 24px;">
            <template #title>{{ t('users.registered') }}</template>
            <template #content>
                <div v-if="error" style="margin-bottom: 16px; color: var(--app-accent); font-weight: 600;">
                    {{ error }}
                </div>

                <BaseDataTable :rows="users" :rows-per-page="8">
                    <PColumn field="name" :header="t('users.columns.name')" />
                    <PColumn field="email" :header="t('users.columns.email')" />
                    <PColumn field="locale" :header="t('users.columns.locale')" />
                    <PColumn field="active" :header="t('users.columns.active')" />
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
import { listUsers } from '../../../api/modules/users';

const users = ref([]);
const error = ref('');

onMounted(async () => {
    try {
        const response = await listUsers();
        users.value = response.data?.data ?? response.data ?? [];
    } catch {
        error.value = t('forms.messages.auth_required_load');
        users.value = [
            {
                name: t('users.fallback_user.name'),
                email: t('users.fallback_user.email'),
                locale: t('users.fallback_user.locale'),
                active: true,
            },
        ];
    }
});
</script>
