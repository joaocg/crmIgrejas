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

                    <PButton
                        type="submit"
                        class="login-page__submit"
                        :label="t('auth.login')"
                        icon="pi pi-arrow-right"
                        :loading="loading"
                        :disabled="loading"
                    />

                    <p v-if="error" class="login-page__error">{{ error }}</p>
                </form>
            </template>
        </PCard>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import { login } from '../../api/auth';
import { t } from '../../i18n';
import { useAuthStore } from '../../stores/auth';

const credentials = reactive({
    email: 'admin@church.local',
    password: '',
});

const error = ref('');
const loading = ref(false);
const auth = useAuthStore();
const route = useRoute();
const router = useRouter();

async function submit() {
    error.value = '';
    loading.value = true;

    try {
        const response = await login(credentials);
        auth.setSession(response.data.user, response.data.token);

        const redirect = typeof route.query.redirect === 'string' && route.query.redirect !== ''
            ? route.query.redirect
            : '/dashboard';

        await router.push(redirect);
    } catch (exception) {
        error.value = exception?.response?.data?.errors?.email?.[0]
            ?? exception?.response?.data?.message
            ?? t('auth.failed');
    } finally {
        loading.value = false;
    }
}
</script>
