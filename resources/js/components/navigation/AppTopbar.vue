<template>
    <header class="app-topbar">
        <div>
            <div class="app-topbar__heading">{{ t('navigation.private_space') }}</div>
            <div class="app-topbar__title">{{ title }}</div>
        </div>

        <div class="app-topbar__actions">
            <BaseTextField
                v-model="search"
                class="app-topbar__search"
                :label="t('forms.search')"
                :placeholder="t('forms.search_placeholder')"
                hide-label
            />

            <PButton :label="t('forms.new_record')" icon="pi pi-plus" severity="primary" />
        </div>
    </header>
</template>

<script setup>
import { ref } from 'vue';

import { t } from '../../i18n';
import BaseTextField from '../forms/BaseTextField.vue';

defineProps({
    title: {
        type: String,
        default: 'Dashboard',
    },
});

const search = ref('');
</script>
