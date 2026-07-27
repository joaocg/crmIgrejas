<template>
    <header class="app-topbar">
        <div>
            <div class="app-topbar__heading">Private Space</div>
            <div class="app-topbar__title">{{ title }}</div>
        </div>

        <div class="app-topbar__actions">
            <BaseTextField
                v-model="search"
                class="app-topbar__search"
                label="Search"
                placeholder="Search people, families, groups"
                hide-label
            />

            <PButton label="Novo cadastro" icon="pi pi-plus" severity="primary" />
        </div>
    </header>
</template>

<script setup>
import { ref } from 'vue';

import BaseTextField from '../forms/BaseTextField.vue';

defineProps({
    title: {
        type: String,
        default: 'Dashboard',
    },
});

const search = ref('');
</script>
