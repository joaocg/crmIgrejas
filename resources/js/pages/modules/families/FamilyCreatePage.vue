<template>
    <AppShell :title="t('families.create.title')">
        <PCard>
            <template #title>{{ t('families.create.heading') }}</template>
            <template #content>
                <form class="stack-form" @submit.prevent="submit">
                    <BaseTextField v-model="form.name" :label="t('families.forms.name')" placeholder="Família Silva" />
                    <BaseTextField v-model="form.email" :label="t('families.forms.email')" placeholder="familia@igreja.org" type="email" />
                    <BaseTextField v-model="form.mobile_phone" :label="t('families.forms.mobile_phone')" placeholder="(85) 99999-9999" />
                    <BaseSelectField
                        v-model="form.newsletter_enabled"
                        :label="t('forms.status.label')"
                        :options="statusOptions"
                        :placeholder="t('forms.status.placeholder')"
                    />

                    <div v-if="message" style="color: var(--app-accent); font-weight: 600;">
                        {{ message }}
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <PButton type="submit" :label="t('families.actions.save_family')" icon="pi pi-check" />
                        <PButton :label="t('forms.actions.cancel')" severity="secondary" text @click="$router.push('/families')" />
                    </div>
                </form>
            </template>
        </PCard>
    </AppShell>
</template>

<script setup>
import { reactive, ref } from 'vue';

import { t } from '../../../i18n';
import AppShell from '../../../layouts/AppShell.vue';
import BaseTextField from '../../../components/forms/BaseTextField.vue';
import BaseSelectField from '../../../components/forms/BaseSelectField.vue';
import { createFamily } from '../../../api/modules/families';

const message = ref('');

const form = reactive({
    tenant_id: 1,
    name: '',
    email: '',
    mobile_phone: '',
    newsletter_enabled: true,
});

const statusOptions = [
    { label: t('forms.status.active'), value: true },
    { label: t('forms.status.inactive'), value: false },
];

async function submit() {
    try {
        await createFamily(form);
        message.value = t('forms.messages.saved');
    } catch {
        message.value = t('forms.messages.auth_required_create');
    }
}
</script>
