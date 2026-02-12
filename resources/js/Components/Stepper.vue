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
  'personas.create_rc',      // Ruta para paso 1
  'personas.create',         // Ruta para paso 2
  'personas.create'          // Ruta para paso 3
];
</script>

<template>
  <div class="max-w-7xl mx-auto mb-6">
    <div class="flex items-center justify-between _bg-slate-200 _shadow-lg p-2 rounded-lg ">
      <div v-for="(label, index) in stepLabels" :key="index + 1" class="flex items-center relative">

        <div 
          v-if="currentStep === (index + 1)"
          :class="[
            'rounded-full flex items-center justify-center border-2 transition-colors duration-300',
            'w-20 h-20 bg-slate-600 border-gray-800 text-white'
          ]"
        >
          <span class="font-semibold text-4xl">{{ index + 1 }}</span>
        </div>


        <Link 
          v-else
          :href="route(stepRoutes[index])"
          :class="[
            'rounded-full flex items-center justify-center border-2 transition-colors duration-300 cursor-pointer _hover:scale-110 bg-white border-gray-300 text-gray-500 hover:bg-slate-900  ',
            'w-12 h-12',
           
          ]"
        >
          <span class="font-semibold">{{ index + 1 }}</span>
        </Link>
                
        <div 
          :class="[
            'h-1 w-32 mx-2 transition-colors duration-300',
            currentStep === (index + 1) ? 'bg-slate-700' : 'bg-gray-200'
          ]"
        >
          <i class="mt-2 text-xs block whitespace-nowrap" :class="currentStep === (index + 1) ? 'text-indigo-300' : 'text-gray-700'">{{ label }}</i>
        </div>
        
       
      </div>
    </div>


    <slot></slot>
  </div>
</template>