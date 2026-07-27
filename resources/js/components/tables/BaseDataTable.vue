<template>
    <div class="base-data-table">
        <PDataTable :value="rows" paginator :rows="rowsPerPage" responsive-layout="scroll">
            <slot />
        </PDataTable>
    </div>
</template>

<script setup>
defineProps({
    rows: {
        type: Array,
        default: () => [],
    },
    rowsPerPage: {
        type: Number,
        default: 10,
    },
});
</script>
