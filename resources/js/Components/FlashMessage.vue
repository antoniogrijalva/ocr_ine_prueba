<script setup>
import { computed, watch, ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const show = ref(false);

const message = computed(() => page.props.flash.message);
const error = computed(() => page.props.flash.error);

// Función para controlar la aparición y desaparición
const triggerAlert = () => {
    if (message.value || error.value) {
        show.value = true;
        // Limpiamos cualquier timer anterior para evitar conflictos
        setTimeout(() => {
            show.value = false;
        }, 5000);
    }
};

// Escuchar cambios (cuando navegas o realizas acciones)
watch([message, error], () => {
    triggerAlert();
});

// Ejecutar al montar (por si el mensaje ya viene en la carga inicial)
onMounted(() => {
    triggerAlert();
});
</script>

<template>
    <transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="(message || error) && show" 
             class="fixed top-5 right-5 z-[100] min-w-[350px] pointer-events-auto">
            
            <div v-if="message" class="bg-white border-l-4 border-green-500 p-4 shadow-2xl rounded flex items-center justify-between">
                <div class="flex items-center">
                    <span class="text-green-500 mr-3">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    </span>
                    <p class="text-sm font-medium text-gray-800">{{ message }}</p>
                </div>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl ml-4">&times;</button>
            </div>

            <div v-if="error" class="bg-white border-l-4 border-red-500 p-4 shadow-2xl rounded flex items-center justify-between">
                <div class="flex items-center">
                    <span class="text-red-500 mr-3">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                    </span>
                    <p class="text-sm font-medium text-gray-800">{{ error }}</p>
                </div>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl ml-4">&times;</button>
            </div>
        </div>
    </transition>
</template>