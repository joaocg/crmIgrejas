<template>
    <label class="app-field" :class="$attrs.class">
        <span v-if="!hideLabel" class="app-field__label">{{ label }}</span>
        <PInputText
            :model-value="modelValue"
            class="app-field__input"
            :placeholder="placeholder"
            :type="type"
            @update:model-value="$emit('update:modelValue', $event)"
        />
    </label>
</template>

<script setup>
defineOptions({
    inheritAttrs: false,
});

defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    label: {
        type: String,
        required: true,
    },
    placeholder: {
        type: String,
        default: '',
    },
    type: {
        type: String,
        default: 'text',
    },
    hideLabel: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['update:modelValue']);
</script>
