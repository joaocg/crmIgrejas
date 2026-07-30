<template>
    <AppShell :title="t('groups.show.title')">
        <PCard>
            <template #title>{{ t('groups.show.heading') }}</template>
            <template #content>
                <div v-if="message" style="margin-bottom: 16px; color: var(--app-accent); font-weight: 600;">
                    {{ message }}
                </div>

                <div class="stack-form">
                    <div><strong>{{ t('groups.forms.name') }}:</strong> {{ group.name }}</div>
                    <div><strong>{{ t('groups.forms.type') }}:</strong> {{ group.type }}</div>
                    <div><strong>{{ t('groups.forms.description') }}:</strong> {{ group.description }}</div>
                    <div><strong>{{ t('forms.status.label') }}:</strong> {{ group.is_active ? t('forms.status.active') : t('forms.status.inactive') }}</div>
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
import { showGroup } from '../../../api/modules/groups';

const route = useRoute();
const message = ref('');

const group = reactive({
    name: 'Pequeno Grupo',
    type: 'small-group',
    description: 'Reunião semanal',
    is_active: true,
});

onMounted(async () => {
    try {
        const response = await showGroup(route.params.id);
        // The API wraps single resources in a `data` envelope. No fallback
        // to the raw body: that would silently re-create the bug this
        // fixes (assigning a literal `data` key) instead of failing loudly.
        Object.assign(group, response.data.data);
    } catch {
        message.value = t('forms.messages.auth_required_load');
    }
});
</script>
