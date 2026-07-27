<template>
    <AppShell title="Create user">
        <PCard>
            <template #title>Create user</template>
            <template #content>
                <form class="stack-form" @submit.prevent="submit">
                    <BaseTextField v-model="form.name" label="Name" placeholder="Jane Doe" />
                    <BaseTextField v-model="form.email" label="Email" placeholder="jane@example.org" type="email" />
                    <BaseTextField v-model="form.password" label="Password" placeholder="••••••••" type="password" />
                    <BaseSelectField
                        v-model="form.locale"
                        label="Locale"
                        :options="localeOptions"
                        placeholder="Select a locale"
                    />
                    <BaseSelectField
                        v-model="form.active"
                        label="Status"
                        :options="statusOptions"
                        placeholder="Select a status"
                    />

                    <div v-if="message" style="color: var(--app-accent); font-weight: 600;">
                        {{ message }}
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <PButton type="submit" label="Save user" icon="pi pi-check" />
                        <PButton label="Cancel" severity="secondary" text @click="$router.push('/users')" />
                    </div>
                </form>
            </template>
        </PCard>
    </AppShell>
</template>

<script setup>
import { reactive, ref } from 'vue';

import AppShell from '../../../layouts/AppShell.vue';
import BaseTextField from '../../../components/forms/BaseTextField.vue';
import BaseSelectField from '../../../components/forms/BaseSelectField.vue';
import { createUser } from '../../../api/modules/users';

const message = ref('');
const form = reactive({
    name: '',
    email: '',
    password: '',
    locale: 'pt_BR',
    active: true,
});

const localeOptions = [
    { label: 'Português (Brasil)', value: 'pt_BR' },
    { label: 'English', value: 'en' },
];

const statusOptions = [
    { label: 'Active', value: true },
    { label: 'Inactive', value: false },
];

async function submit() {
    try {
        await createUser(form);
        message.value = 'User saved.';
    } catch {
        message.value = 'Authentication required to create live records.';
    }
}
</script>
