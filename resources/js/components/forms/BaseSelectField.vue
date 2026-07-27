<template>
    <label class="app-field">
        <span class="app-field__label">{{ label }}</span>
        <PDropdown
            :model-value="modelValue"
            class="app-field__input"
            :options="options"
            option-label="label"
            option-value="value"
            :placeholder="placeholder"
            @update:model-value="$emit('update:modelValue', $event)"
        />
    </label>
</template>

<script setup>
defineProps({
    modelValue: {
        type: [String, Number, null],
        default: null,
    },
    label: {
        type: String,
        required: true,
    },
    placeholder: {
        type: String,
        default: '',
    },
    options: {
        type: Array,
        default: () => [],
    },
});

defineEmits(['update:modelValue']);
</script>
