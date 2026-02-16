<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

defineProps({
  currentStep: {
    type: Number,
    default: 1
  }
});

const totalSteps = 3;

const stepLabels = [
  'Datos Generales',
  'Documentos',
  'Envío de información'
];

// Define las rutas para cada paso
const stepRoutes = [
  'personas.create_rc',         // Ruta para paso 1
  'personas.documentos_rc',     // Ruta para paso 2
  'personas.create'             // Ruta para paso 3
];
</script>

<template>
  <div class="max-w-7xl mx-auto">
    <div class="p-2 rounded-lg">
      <!-- Contenedor principal -->
      <div class="flex items-start justify-between">
        <!-- Cada paso con su círculo, línea y label -->
        <div 
          v-for="(label, index) in stepLabels" 
          :key="index + 1"
          class="flex flex-col items-center"
          :class="index < stepLabels.length - 1 ? 'flex-1' : 'flex-initial'"
        >
          <!-- Contenedor de círculo y línea -->
          <div class="flex items-center w-full">
            <!-- Círculo del paso -->
            <div class="flex flex-col items-center flex-shrink-0">
              <!-- Círculo activo -->
              <div 
                v-if="currentStep === (index + 1)"
                :class="[
                  'rounded-full flex items-center justify-center border-2 transition-colors duration-300',
                  'w-12 h-12 bg-slate-600 border-slate-800 text-white shadow-lg'
                ]"
              >
                <span class="font-semibold text-2xl">{{ index + 1 }}</span>
              </div>

              <!-- Círculo inactivo (con link) -->
              <Link 
                v-else
                :href="route(stepRoutes[index])"
                :class="[
                  'rounded-full flex items-center justify-center border-2 transition-all duration-300 cursor-pointer hover:scale-110 hover:shadow-md',
                  currentStep > (index + 1) 
                    ? 'bg-slate-600 border-slate-700 text-white' 
                    : 'bg-white border-gray-300 text-gray-500 hover:bg-slate-50',
                  'w-12 h-12'
                ]"
              >
                <span class="font-semibold" :class="currentStep > (index + 1) ? 'text-xl' : ''">
                  <i v-if="currentStep > (index + 1)" class="fas fa-check"></i>
                  <span v-else>{{ index + 1 }}</span>
                </span>
              </Link>

              <!-- Label debajo del círculo -->
              <span 
                class="mt-2 text-xs whitespace-nowrap text-center block max-w-[100px]" 
                :class="currentStep === (index + 1) ? 'text-slate-700 font-bold' : 'text-gray-600'"
              >
                {{ label }}
              </span>
            </div>

            <!-- Línea conectora con flecha (excepto en el último paso) -->
            <div 
              v-if="index < stepLabels.length - 1"
              class="flex items-center flex-1 mx-3 relative"
            >
              <!-- Línea -->
              <div 
                :class="[
                  'h-1 flex-1 transition-colors duration-300 rounded',
                  currentStep > (index + 1) ? 'bg-slate-600' : 'bg-gray-300'
                ]"
              ></div>
              
              <!-- Flecha al final de la línea -->
              <div 
                :class="[
                  'w-0 h-0 border-t-[6px] border-t-transparent border-b-[6px] border-b-transparent border-l-[10px] transition-colors duration-300',
                  currentStep > (index + 1) ? 'border-l-slate-600' : 'border-l-gray-300'
                ]"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <slot></slot>
  </div>
</template>

<style scoped>
/* Animación suave para el hover */
.hover\:scale-110:hover {
  transform: scale(1.1);
}
</style>