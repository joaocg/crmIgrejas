<template>
    <AppShell title="Users">
        <div class="hero-panel">
            <div class="hero-panel__eyebrow">Module</div>
            <h1 class="hero-panel__title">Users</h1>
            <p class="hero-panel__copy">
                This module owns user CRUD operations through a dedicated API and a route-split SPA page structure.
            </p>
        </div>

        <PCard style="margin-top: 24px;">
            <template #title>Registered users</template>
            <template #content>
                <div v-if="error" style="margin-bottom: 16px; color: var(--app-accent); font-weight: 600;">
                    {{ error }}
                </div>

                <BaseDataTable :rows="users" :rows-per-page="8">
                    <PColumn field="name" header="Name" />
                    <PColumn field="email" header="Email" />
                    <PColumn field="locale" header="Locale" />
                    <PColumn field="active" header="Active" />
                </BaseDataTable>
            </template>
        </PCard>
    </AppShell>
</template>

<script setup>
import { onMounted, ref } from 'vue';

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
        error.value = 'Login required to load the live dataset.';
        users.value = [
            { name: 'System Admin', email: 'admin@church.local', locale: 'pt_BR', active: true },
        ];
    }
});
</script>
