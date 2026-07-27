<template>
    <AppShell title="User details">
        <PCard>
            <template #title>User details</template>
            <template #content>
                <div v-if="message" style="margin-bottom: 16px; color: var(--app-accent); font-weight: 600;">
                    {{ message }}
                </div>

                <div class="stack-form">
                    <div><strong>Name:</strong> {{ user.name }}</div>
                    <div><strong>Email:</strong> {{ user.email }}</div>
                    <div><strong>Locale:</strong> {{ user.locale }}</div>
                    <div><strong>Status:</strong> {{ user.active ? 'Active' : 'Inactive' }}</div>
                </div>
            </template>
        </PCard>
    </AppShell>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';

import AppShell from '../../../layouts/AppShell.vue';
import { showUser } from '../../../api/modules/users';

const route = useRoute();
const message = ref('');
const user = reactive({
    name: 'Sample User',
    email: 'sample@church.local',
    locale: 'pt_BR',
    active: true,
});

onMounted(async () => {
    try {
        const response = await showUser(route.params.id);
        Object.assign(user, response.data);
    } catch {
        message.value = 'Live user data requires authentication.';
    }
});
</script>
