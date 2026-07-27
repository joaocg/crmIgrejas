<template>
    <AppShell :title="t('families.show.title')">
        <PCard>
            <template #title>{{ t('families.show.heading') }}</template>
            <template #content>
                <div v-if="message" style="margin-bottom: 16px; color: var(--app-accent); font-weight: 600;">
                    {{ message }}
                </div>

                <div class="stack-form">
                    <div><strong>{{ t('families.forms.name') }}:</strong> {{ family.name }}</div>
                    <div><strong>{{ t('families.forms.email') }}:</strong> {{ family.email }}</div>
                    <div><strong>{{ t('families.forms.mobile_phone') }}:</strong> {{ family.mobile_phone }}</div>
                    <div><strong>{{ t('forms.status.label') }}:</strong> {{ family.newsletter_enabled ? t('forms.status.active') : t('forms.status.inactive') }}</div>
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
import { showFamily } from '../../../api/modules/families';

const route = useRoute();
const message = ref('');

const family = reactive({
    name: 'Família Silva',
    email: 'familia@igreja.org',
    mobile_phone: '(85) 99999-9999',
    newsletter_enabled: true,
});

onMounted(async () => {
    try {
        const response = await showFamily(route.params.id);
        Object.assign(family, response.data);
    } catch {
        message.value = t('forms.messages.auth_required_load');
    }
});
</script>
