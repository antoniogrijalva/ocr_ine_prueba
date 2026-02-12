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
    options: {
        type: Array,
        default: () => []
    },
    optionValue: {
        type: String,
        default: 'id'
    },
    optionLabel: {
        type: String,
        default: 'nombre'
    },
    placeholder: {
        type: String,
        default: 'Seleccione...'
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
    emptyMessage: {
        type: String,
        default: 'No hay opciones disponibles'
    }
});

const emit = defineEmits(['update:modelValue']);

const handleChange = (event) => {
    emit('update:modelValue', event.target.value);
};
</script>

<template>
    <div>
        <label class="block text-sm font-medium text-gray-700">
            {{ label }}
            <span v-if="required" class="text-red-700">*</span>
        </label>
        <select 
            :value="modelValue"
            @change="handleChange"
            :required="required"
            :disabled="disabled || options.length === 0"
            class="block w-full text-sm text-slate-900 py-1 border-slate-400 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
            :class="{ 'bg-slate-200 text-slate-500': disabled || options.length === 0 }"
        >
            <option class="text-gray-400" disabled value="0" v-if="options.length > 0">{{ placeholder }}</option>
            <option class="text-gray-400" disabled value="0" v-else>{{ emptyMessage }}</option>
            
            <option 
                v-for="option in options" 
                :key="option[optionValue]" 
                :value="option[optionValue]"
            >
                {{ option[optionLabel] }}
            </option>
        </select>
        <div v-if="error" class="text-red-500 text-xs mt-1">{{ error }}</div>
    </div>
</template>