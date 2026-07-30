<template>
    <AppShell :title="t('groups.edit.title')">
        <PCard>
            <template #title>{{ t('groups.edit.heading') }}</template>
            <template #content>
                <form class="stack-form" @submit.prevent="submit">
                    <BaseTextField v-model="form.name" :label="t('groups.forms.name')" />
                    <BaseTextField v-model="form.type" :label="t('groups.forms.type')" />
                    <BaseTextField v-model="form.description" :label="t('groups.forms.description')" />
                    <BaseSelectField v-model="form.is_active" :label="t('forms.status.label')" :options="statusOptions" />

                    <div v-if="message" style="color: var(--app-accent); font-weight: 600;">
                        {{ message }}
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <PButton type="submit" :label="t('groups.actions.update_group')" icon="pi pi-save" />
                        <PButton :label="t('forms.actions.back')" severity="secondary" text @click="$router.push('/groups')" />
                    </div>
                </form>
            </template>
        </PCard>
    </AppShell>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';

import { t } from '../../../i18n';
import AppShell from '../../../layouts/AppShell.vue';
import BaseTextField from '../../../components/forms/BaseTextField.vue';
import BaseSelectField from '../../../components/forms/BaseSelectField.vue';
import { showGroup, updateGroup } from '../../../api/modules/groups';

const route = useRoute();
const message = ref('');

const form = reactive({
    tenant_id: 1,
    name: '',
    type: '',
    description: '',
    is_active: true,
});

const statusOptions = [
    { label: t('forms.status.active'), value: true },
    { label: t('forms.status.inactive'), value: false },
];

onMounted(async () => {
    try {
        const response = await showGroup(route.params.id);
        // The API wraps single resources in a `data` envelope; the list
        // pages unwrap it the same way.
        Object.assign(form, response.data?.data ?? response.data ?? {});
    } catch {
        message.value = t('forms.messages.auth_required_load');
    }
});

async function submit() {
    try {
        await updateGroup(route.params.id, form);
        message.value = t('forms.messages.updated');
    } catch {
        message.value = t('forms.messages.auth_required_update');
    }
}
</script>
