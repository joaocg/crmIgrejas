<template>
    <AppShell title="Dashboard">
        <div class="hero-panel">
            <div class="hero-panel__eyebrow">Welcome back</div>
            <h1 class="hero-panel__title">A clean church CRM for complex workflows.</h1>
            <p class="hero-panel__copy">
                This new shell is built to reduce friction, keep navigation stable, and support module-driven CRUD
                screens without forcing full page reloads.
            </p>

            <div class="surface-grid">
                <PCard>
                    <template #title>People</template>
                    <template #content>
                        <strong>1</strong>
                        <div class="app-brand__subtitle">Active records in the normalized baseline</div>
                    </template>
                </PCard>

                <PCard>
                    <template #title>Modules</template>
                    <template #content>
                        <strong>2</strong>
                        <div class="app-brand__subtitle">Core and Users module boundaries loaded</div>
                    </template>
                </PCard>

                <PCard>
                    <template #title>Locale</template>
                    <template #content>
                        <strong>pt_BR</strong>
                        <div class="app-brand__subtitle">Primary language for admin experience</div>
                    </template>
                </PCard>
            </div>
        </div>

        <PCard style="margin-top: 24px;">
            <template #title>Recent activity</template>
            <template #content>
                <BaseDataTable :rows="recentActivity" :rows-per-page="5">
                    <PColumn field="name" header="Name" />
                    <PColumn field="module" header="Module" />
                    <PColumn field="status" header="Status" />
                </BaseDataTable>
            </template>
        </PCard>
    </AppShell>
</template>

<script setup>
import AppShell from '../layouts/AppShell.vue';
import BaseDataTable from '../components/tables/BaseDataTable.vue';

const recentActivity = [
    { name: 'Admin user updated', module: 'Users', status: 'Saved' },
    { name: 'System locale set', module: 'Core', status: 'Ready' },
    { name: 'Church override loaded', module: 'Users', status: 'Loaded' },
];
</script>
