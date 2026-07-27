<template>
    <div class="login-page">
        <PCard class="login-page__card">
            <template #title>
                <div class="app-brand">
                    <div class="app-brand__mark">CI</div>
                    <div>
                        <div class="app-brand__title">crmIgrejas</div>
                        <div class="app-brand__subtitle">{{ t('auth.title') }}</div>
                    </div>
                </div>
            </template>

            <template #content>
                <div style="display: grid; gap: 16px;">
                    <label style="display: grid; gap: 8px;">
                        <span>{{ t('auth.email') }}</span>
                        <PInputText v-model="credentials.email" type="email" placeholder="admin@church.local" />
                    </label>

                    <label style="display: grid; gap: 8px;">
                        <span>{{ t('auth.password') }}</span>
                        <PPassword v-model="credentials.password" toggleMask :feedback="false" />
                    </label>

                    <PButton :label="t('auth.login')" icon="pi pi-arrow-right" />
                </div>
            </template>
        </PCard>
    </div>
</template>

<script setup>
import { reactive } from 'vue';

import { t } from '../../i18n';

const credentials = reactive({
    email: 'admin@church.local',
    password: '',
});
</script>
