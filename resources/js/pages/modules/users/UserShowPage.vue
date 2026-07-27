<template>
    <AppShell :title="t('users.show.title')">
        <PCard>
            <template #title>{{ t('users.show.heading') }}</template>
            <template #content>
                <div v-if="message" style="margin-bottom: 16px; color: var(--app-accent); font-weight: 600;">
                    {{ message }}
                </div>

                <div class="stack-form">
                    <div><strong>{{ t('forms.name') }}:</strong> {{ user.name }}</div>
                    <div><strong>{{ t('forms.email') }}:</strong> {{ user.email }}</div>
                    <div><strong>{{ t('forms.locale.label') }}:</strong> {{ user.locale }}</div>
                    <div><strong>{{ t('forms.status.label') }}:</strong> {{ user.active ? t('forms.status.active') : t('forms.status.inactive') }}</div>
                </div>
            </template>
        </PCard>
    </AppShell>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';

import { t } from '../../../i18n';
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
        message.value = t('forms.messages.auth_required_load');
    }
});
</script>
