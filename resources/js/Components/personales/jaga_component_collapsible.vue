<script setup>
import { ref } from 'vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
        default: 'Título'
    },
    icon: {
        type: String,
        default: '<i class="fas fa-folder"></i>'
    },
    borderColor: {
        type: String,
        default: 'border-blue-500'
    },
    bgColor: {
        type: String,
        default: 'bg-white'
    },
    headerBgColor: {
        type: String,
        default: 'bg-blue-100'
    },
    headerTextColor: {
        type: String,
        default: 'text-slate-800'
    },
    isOpen: {
        type: Boolean,
        default: false
    },
    borderGrosor: {
        type: String,
        default: 'border'
    }

});

const isExpanded = ref(props.isOpen);

const toggle = () => {
    isExpanded.value = !isExpanded.value;
};
</script>

<template>
    <div class="w-full rounded-lg overflow-hidden mb-4 shadow-lg" :class="[borderColor, borderGrosor]">
        <!-- Header / Barra Superior -->
        <div 
            @click="toggle"
            class="flex items-center justify-between p-4 cursor-pointer hover:opacity-80 transition-opacity"
            :class="[headerBgColor,headerTextColor]" 
        >
            <!-- Lado Izquierdo: Icono + Título -->
            <div class="flex items-center gap-3" >
                <span class="text-xl" v-html="icon"></span>
                <h3 class="text-lg font-semibold"  >{{ title }}</h3>
            </div>

            <!-- Lado Derecho: Flecha -->
            <div class="flex items-center">
                <svg 
                    class="w-6 h-6 _text-gray-600 transition-transform duration-300"
                    :class="{ 'rotate-180': isExpanded }"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>

        <!-- Contenido / Cuerpo -->
        <div 
            class="grid transition-all duration-300 ease-in-out"
            :class="isExpanded ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'"
        >
            <div class="overflow-hidden">
                <div class="p-4" :class="bgColor">
                    <slot></slot>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.rotate-180 {
    transform: rotate(180deg);
}
</style>