<template>
    <RouterView />
</template>

<script setup>
import { onMounted } from 'vue';
import { RouterView } from 'vue-router';
import { useAuthStore } from './stores/auth';

const auth = useAuthStore();

onMounted(async () => {
    await auth.hydrate();
});
</script>
