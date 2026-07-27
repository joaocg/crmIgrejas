<template>
    <AppShell :title="t('people.show.title')">
        <PCard>
            <template #title>{{ t('people.show.heading') }}</template>
            <template #content>
                <div v-if="message" style="margin-bottom: 16px; color: var(--app-accent); font-weight: 600;">
                    {{ message }}
                </div>

                <div class="stack-form">
                    <div><strong>{{ t('people.forms.first_name') }}:</strong> {{ person.first_name }}</div>
                    <div><strong>{{ t('people.forms.last_name') }}:</strong> {{ person.last_name }}</div>
                    <div><strong>{{ t('people.forms.birth_date') }}:</strong> {{ person.birth_date }}</div>
                    <div><strong>{{ t('forms.status.label') }}:</strong> {{ person.newsletter_enabled ? t('forms.status.active') : t('forms.status.inactive') }}</div>
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
import { showPerson } from '../../../api/modules/people';

const route = useRoute();
const message = ref('');

const person = reactive({
    first_name: 'Maria',
    last_name: 'Silva',
    birth_date: '1990-01-01',
    newsletter_enabled: true,
});

onMounted(async () => {
    try {
        const response = await showPerson(route.params.id);
        Object.assign(person, response.data);
    } catch {
        message.value = t('forms.messages.auth_required_load');
    }
});
</script>
