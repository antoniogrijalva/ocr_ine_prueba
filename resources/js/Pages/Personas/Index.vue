<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link ,router} from '@inertiajs/vue3';
import { ref, watch } from 'vue';

//import debounce from 'lodash/debounce'; // Laravel ya incluye lodash por defecto

//defineProps({ personas: Array});

const props = defineProps({ 
    personas: Array,
    filters: Object 
});

// Iniciamos el buscador con lo que venga en la URL (si existe)
const search = ref(props.filters.search || '');

// Función debounce manual
const customDebounce = (fn, delay) => {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
};

// Uso del watch con la función manual
watch(search, customDebounce((value) => {
    router.get(route('personas.index'), { search: value }, {
        preserveState: true,
        replace: true
    });
}, 300));


const enviarExpediente = (id) => {
    if (confirm('Una vez enviado no podrás editar los documentos. ¿Continuar?')) {
        router.post(route('personas.enviar', id));
    }
};
</script>

<template>
    <Head title="Listado de Personas" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Registros Capturados</h2>
                <Link :href="route('personas.create')" class="bg-indigo-600 hover:bg-indigo-300 text-white px-4 py-2 rounded shadow">
                    + Nuevo Registro
                </Link>
            </div>
        </template>

        <div class="py-12">

            
                <div class="py-12">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        
                        <div class="mb-6 flex justify-between items-center bg-white p-4 rounded-lg shadow-sm">
                            <div class="relative w-full max-w-md">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </span>
                                <input 
                                    v-model="search"
                                    type="text" 
                                    placeholder="Buscar por nombre, apellidos o CURP..."
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>
                            
                            <div class="text-sm text-gray-500">
                                Resultados: {{ personas.length }}
                            </div>
                        </div>

                        </div>
                </div>
      
            
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre Completo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CURP</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estatus</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="persona in personas" :key="persona.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ persona.nombre }} {{ persona.primer_apellido }} {{ persona.segundo_apellido }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">{{ persona.curp }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="{
                                        'px-2 py-1 rounded-full text-xs font-bold': true,
                                        'bg-yellow-100 text-yellow-800': persona.status === 'borrador',
                                        'bg-blue-100 text-blue-800': persona.status === 'en_revision',
                                        'bg-green-100 text-green-800': persona.status === 'aceptado',
                                        'bg-red-100 text-red-800': persona.status === 'rechazado'
                                    }">
                                        {{ persona.status.toUpperCase() }}
                                    </span>

                                    <div v-if="persona.status === 'rechazado' && persona.observaciones" class="mt-2">
                                        <!-- <p class="text-[10px] font-bold text-red-600 uppercase">Motivo del rechazo:</p> -->
                                        <p class="text-xs text-gray-600 italic bg-red-50 p-2 rounded border border-red-100 max-w-xs whitespace-normal">
                                            "{{ persona.observaciones }}"
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                   
                                       <Link 
                                            :href="route('personas.documentos', persona.id)" 
                                            :class="[
                                                'px-3 py-1 rounded transition-colors mr-3',
                                                persona.documentos_count > 0 
                                                    ? 'bg-green-100 text-green-700 hover:bg-green-200' // Verde si tiene
                                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'    // Gris si no tiene
                                            ]"
                                        >
                                            {{ persona.documentos_count > 0 ? '📂 Ver Documentos' : '📁 Subir Documentos' }}
                                        </Link>

                                        <button 
                                            v-if="(persona.status === 'borrador' || persona.status==='rechazado') && persona.documentos_count > 0"
                                            @click="enviarExpediente(persona.id)"
                                            class="text-orange-600 hover:text-orange-900 font-bold border border-orange-600 px-3 py-1 rounded"
                                        >
                                            🚀 Enviar a Revisión
                                        </button>
                                        
                                        <span v-if="(persona.status === 'borrador' || persona.status==='rechazado') && persona.documentos_count === 0" class="text-gray-400 italic text-xs">
                                            (Faltan documentos)
                                        </span>

                                        <Link 
                                            v-if="persona.status === 'en_revision' && ($page.props.auth.user.roles.includes('validador') || $page.props.auth.user.roles.includes('administrador'))"
                                            :href="route('personas.revisar', persona.id)"
                                            class="bg-blue-600 text-white px-3 py-1 rounded ml-2"
                                        >
                                            ⚖️ Dictaminar
                                        </Link>
                                    
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>