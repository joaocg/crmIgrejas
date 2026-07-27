<template>
    <div class="login-page">
        <PCard class="login-page__card">
            <template #title>
                <div class="login-page__brand">
                    <div class="app-brand">
                        <div class="app-brand__mark">CI</div>
                        <div>
                            <div class="app-brand__title">crmIgrejas</div>
                            <div class="app-brand__subtitle">{{ t('auth.title') }}</div>
                        </div>
                    </div>

                    <p class="login-page__intro">{{ t('auth.subtitle') }}</p>
                </div>
            </template>

            <template #content>
                <form class="login-page__form" @submit.prevent="submit">
                    <label class="login-page__field">
                        <span>{{ t('auth.email') }}</span>
                        <PInputText
                            v-model="credentials.email"
                            type="email"
                            placeholder="admin@church.local"
                            autocomplete="username"
                            class="login-page__control"
                        />
                    </label>

                    <label class="login-page__field">
                        <span>{{ t('auth.password') }}</span>
                        <PPassword
                            v-model="credentials.password"
                            toggleMask
                            :feedback="false"
                            autocomplete="current-password"
                            class="login-page__password"
                            input-class="login-page__control"
                        />
                    </label>

                    <PButton type="submit" class="login-page__submit" :label="t('auth.login')" icon="pi pi-arrow-right" />
                </form>
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

function submit() {
    //
}
</script>
