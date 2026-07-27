<template>
    <div class="app-shell">
        <div class="app-shell__surface">
            <div class="app-shell__grid">
                <AppSidebar />

                <main class="app-main">
                    <AppTopbar :title="title" />

                    <section class="app-content">
                        <slot />
                    </section>
                </main>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppSidebar from '../components/navigation/AppSidebar.vue';
import AppTopbar from '../components/navigation/AppTopbar.vue';

defineProps({
    title: {
        type: String,
        default: 'Dashboard',
    },
});
</script>
