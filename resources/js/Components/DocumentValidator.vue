<template>

  <!-- <div 
  :class="[
          'p-2 border rounded-lg   transition-all hover:bg-slate-500 hover:text-white cursor-pointer',
          isActive ? 'bg-gray-600 text-white' : 'bg-gray-300'
        ]"
  > -->

  <div 
  :class="[
          
          isActive ? ' border-b border-t border-slate-400 rounded-md' : ' '
        ]"
  >
  
    <div class="flex  items-center gap-4">
    

      <!-- Información del documento -->
      <!-- <div class="flex-1"> -->
        
          <!-- Icono del tipo de documento -->
        <!-- <div class="text-3xl flex flex-col flex-shrink-0"> -->
          <!-- <span v-if="isPdf">📄</span>
          <span v-else-if="isImage">🖼️</span>
          <span v-else>📁</span> -->

            <button 
              @click="$emit('view', id)" 
              class="flex-1  _text-sky-800  _text-xs font-medium inline-flex items-center gap-1"
                 :class="[
                    'p-2 border rounded-lg   transition-all hover:bg-slate-500 hover:text-white cursor-pointer',
                    isActive ? 'bg-gray-600 text-white' : 'bg-gray-300'
                  ]"
            >
                <h4 class="font-thin  line-flex _mb-1 text-sm">
                  <!-- 👁️  -->
                  <span v-if="isPdf">📄</span>
                  <span v-else-if="isImage">🖼️</span>
                  <span v-else>📁</span>
                 
                  {{ docName }}
                  
                </h4>

            </button>

           
        <!-- </div> -->
       
      <!-- </div> -->

      <!-- Controles de validación -->
      <div class="flex flex-col gap-2 border-l pl-4 flex-shrink-0">

       

        <div class="flex items-center gap-4">

          <!-- Textarea para motivo de rechazo -->
          <Transition name="bounce" _name="slide-fade">
          <textarea 
            v-if="internalStatus === 'rechazado'"
            v-model="internalObservation"
            placeholder="Motivo del rechazo..."
            class="border rounded _p-2 text-sm  text-red-700 w-full _mt-2 focus:ring-1 focus:ring-red-400 outline-none"
            rows="2"
          ></textarea>
          </Transition>

          <label class="flex items-center text-sm  gap-1 cursor-pointer">
            <input 
              type="radio" 
              :name="'status-' + id" 
              value="valido" 
              v-model="internalStatus" 
              class="text-green-600 _accent-green-600"
            >
            <span class="text-sm">Válido</span>
          </label>
          
          <label class="flex items-center text-sm  gap-1 cursor-pointer">
            <input 
              type="radio" 
              :name="'status-' + id" 
              value="rechazado" 
              v-model="internalStatus" 
              class="text-red-600 _accent-red-600"
            >
            <span class="text-sm">Rechazar</span>
          </label>



        </div>

        
      </div>
    </div>

  </div>

</template>

<style>
  .fade-enter-active, .fade-leave-active {
    transition: opacity 1s ease;
  }
  .fade-enter-from, .fade-leave-to {
    opacity: 0;
  }

  /*
    Enter and leave animations can use different
    durations and timing functions.
  */
  .slide-fade-enter-active {
    transition: all 0.3s ease-out;
  }

  .slide-fade-leave-active {
    transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1);
  }

  .slide-fade-enter-from,
  .slide-fade-leave-to {
    transform: translateX(20px);
    opacity: 0;
  }


  .bounce-enter-active {
    animation: bounce-in 0.5s;
  }
  .bounce-leave-active {
    animation: bounce-in 0.5s reverse;
  }
  @keyframes bounce-in {
    0% {
      transform: scale(0);
    }
    50% {
      transform: scale(1.25);
    }
    100% {
      transform: scale(1);
    }
  }

</style>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
  id: [String, Number],
  docName: {
    type: String,
    default: ''
  },
  docUrl: String,
  modelValue: Object, // Para sincronizar con el padre
  isActive: Boolean // Para resaltar documento activo
});

const emit = defineEmits(['update:modelValue', 'view']);

// Estado interno vinculado al v-model del padre
const internalStatus = ref(props.modelValue?.status || '');
const internalObservation = ref(props.modelValue?.observation || '');

// Lógica para determinar el tipo de archivo
const isPdf = computed(() => {
  if (!props.docUrl) return false;
  return props.docUrl.toLowerCase().endsWith('.pdf');
});

const isImage = computed(() => {
  if (!props.docUrl) return false;
  return /\.(jpg|jpeg|png|gif|webp)$/i.test(props.docUrl);
});

// Sincronizar cambios internos con el prop modelValue del padre
watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    internalStatus.value = newVal.status || '';
    internalObservation.value = newVal.observation || '';
  }
}, { deep: true });

// Notificar cambios al padre cada vez que cambie algo
watch([internalStatus, internalObservation], () => {
  emit('update:modelValue', {
    status: internalStatus.value,
    observation: internalStatus.value === 'rechazado' ? internalObservation.value : ''
  });
});
</script>