<template>
    <AppShell title="Edit user">
        <PCard>
            <template #title>Edit user</template>
            <template #content>
                <form class="stack-form" @submit.prevent="submit">
                    <BaseTextField v-model="form.name" label="Name" />
                    <BaseTextField v-model="form.email" label="Email" type="email" />
                    <BaseSelectField v-model="form.locale" label="Locale" :options="localeOptions" />
                    <BaseSelectField v-model="form.active" label="Status" :options="statusOptions" />

                    <div v-if="message" style="color: var(--app-accent); font-weight: 600;">
                        {{ message }}
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <PButton type="submit" label="Update user" icon="pi pi-save" />
                        <PButton label="Back" severity="secondary" text @click="$router.push('/users')" />
                    </div>
                </form>
            </template>
        </PCard>
    </AppShell>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';

import AppShell from '../../../layouts/AppShell.vue';
import BaseTextField from '../../../components/forms/BaseTextField.vue';
import BaseSelectField from '../../../components/forms/BaseSelectField.vue';
import { showUser, updateUser } from '../../../api/modules/users';

const route = useRoute();
const message = ref('');
const form = reactive({
    name: '',
    email: '',
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

onMounted(async () => {
    try {
        const response = await showUser(route.params.id);
        Object.assign(form, response.data);
    } catch {
        message.value = 'Live user data requires authentication.';
    }
});

async function submit() {
    try {
        await updateUser(route.params.id, form);
        message.value = 'User updated.';
    } catch {
        message.value = 'Authentication required to update live records.';
    }
}
</script>
