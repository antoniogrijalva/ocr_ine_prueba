<script setup>
defineProps({
    label: {
        type: String,
        required: true
    },
    modelValue: {
        type: [String, Number],
        default: ''
    },
    type: {
        type: String,
        default: 'text'
    },
    placeholder: {
        type: String,
        default: ''
    },
    required: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
    error: {
        type: String,
        default: ''
    },
    maxlength: {
        type: [String, Number],
        default: null
    }
});

const emit = defineEmits(['update:modelValue']);

const handleInput = (event) => {
    const value = event.target.value.toUpperCase();
    emit('update:modelValue', value);
};

const handleBlur = (event) => {
    // Eliminar espacios al inicio y final, y reemplazar múltiples espacios por uno solo
    const value = event.target.value.trim().replace(/\s+/g, ' ').toUpperCase();
    emit('update:modelValue', value);
};
</script>

<template>
    <div>
        <label class="block text-sm font-medium text-gray-700">
            {{ label }}
            <span v-if="required" class="text-red-700">*</span>
        </label>
        <input 
            :type="type"
            :value="modelValue"
            @input="handleInput"
            @blur="handleBlur"
            :placeholder="placeholder"
            :required="required"
            :disabled="disabled"
            :maxlength="maxlength"
            class="block w-full text-sm text-indigo-700 py-1 border-slate-400 rounded-md focus:ring-indigo-500 focus:border-indigo-500 uppercase placeholder:normal-case  placeholder:italic placeholder:text-gray-300 "
            :class="{ 'bg-slate-200 text-slate-500': disabled }"
        />
        <div v-if="error" class="text-red-500 text-xs mt-1">{{ error }}</div>
    </div>
</template>