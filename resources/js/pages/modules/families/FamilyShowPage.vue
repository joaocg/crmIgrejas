<template>
    <AppShell :title="t('families.show.title')">
        <div class="surface-grid">
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

            <PCard>
                <template #title>{{ t('families.show.contacts_title') }}</template>
                <template #content>
                    <div class="stack-form">
                        <div v-if="contactRows.length === 0">
                            {{ t('families.show.contacts_empty') }}
                        </div>
                        <div v-for="contact in contactRows" :key="`${contact.type}-${contact.value}`">
                            <strong>{{ contact.label }}:</strong> {{ contact.value }}
                            <span style="margin-left: 8px; color: var(--app-muted);">
                                {{ contact.is_primary ? t('forms.status.active') : t('forms.status.inactive') }}
                            </span>
                        </div>
                    </div>
                </template>
            </PCard>
        </div>
    </AppShell>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
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
    contacts: [],
});

const contactRows = computed(() => (family.contacts ?? []).map((contact) => ({
    ...contact,
    label: contact.label || contact.type,
})));

onMounted(async () => {
    try {
        const response = await showFamily(route.params.id);
        // The API wraps single resources in a `data` envelope. No fallback
        // to the raw body: that would silently re-create the bug this
        // fixes (assigning a literal `data` key) instead of failing loudly.
        Object.assign(family, response.data.data);
    } catch {
        message.value = t('forms.messages.auth_required_load');
    }
});
</script>
