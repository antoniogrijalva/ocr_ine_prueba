<script setup>
import { ref, computed } from 'vue';
import DocumentValidator from '@/Components/DocumentValidator.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ persona: Object });

const documentos = ref(props.persona.documentos
    .map(doc => ({
        id: doc.id,
        name: doc.tipo_documento.nombre,
        url: doc.ruta_archivo,
        validation: { status: '', observation: '' },
        tipo_documento_id: doc.tipo_documento_id
    }))
    .sort((a, b) => a.tipo_documento_id - b.tipo_documento_id)
);




const docSeleccionado = ref(null);

const verDocumento = (documentoId) => {
    const doc = documentos.value.find(d => d.id === documentoId);
    if (doc) {
        docSeleccionado.value = doc;
    }
};

/*const todosValidados = computed(() => {
  return documentos.value.every(d => d.validation.status === 'valido');
});*/

const todosValidados = computed(() => {
    return documentos.value.filter(d => d.validation.status === 'valido').length;
});

const hayRechazos = computed(() => {
  return documentos.value.filter(d => d.validation.status === 'rechazado').length;
});

const enviarRevision = () => {
  
    //si existen rechazos, verificar qeu todos tengan observaciones de lo contrario mandar mensaje de error y no enviar el formulario
    const rechazadosSinObservacion = documentos.value.filter(d => d.validation.status === 'rechazado' && !d.validation.observation.trim());
    if (rechazadosSinObservacion.length > 0) {
        alert('Por favor, agregue observaciones para todos los documentos rechazados antes de enviar la revisión.');
        return;
    }
    
    const form = useForm({
        documentos: documentos.value.map(doc => ({
            id: doc.id,
            status: doc.validation.status,
            observation: doc.validation.observation
        }))
    });

    form.post(route('personas.dictaminar', { persona: props.persona.id }), {
        onSuccess: () => {
            
        },
        onError: (errors) => {
            console.error('Error al enviar la revisión:', errors);
        }
    }); 
};



</script>

<template>
    <Head title="Revisión de Expediente" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Validando: {{ persona.nombre }} {{ persona.primer_apellido }}
                </h2>
                <Link :href="route('personas.index')" class="bg-indigo-600 hover:bg-indigo-300 text-white px-4 py-2 rounded shadow">
                    ← Regresar
                </Link>
            </div>


        </template>
            <div class="p-1 text-xs text-gray-600">
                <!-- Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ratione velit, voluptas voluptatum iusto cupiditate quam? Obcaecati amet ipsum tempora enim hic, nam soluta omnis, tempore voluptatibus corrupti similique architecto atque! -->
                {{ props.persona.documentos }}
            </div>
        <!-- <div class="h-[calc(100vh-140px)] overflow-hidden"> -->
        <div class="h-[calc(100vh-200px)] overflow-hidden mt-4">
            <div class="h-full _max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex gap-6 h-full">

                    <!-- Panel izquierdo: Lista de documentos -->
                    <div class="w-5/12 flex flex-col h-full">
                        <div class="bg-white rounded-lg shadow flex-1 flex flex-col overflow-hidden">
                            <!-- Header fijo -->
                            <div class="p-6 border-b bg-gray-100">
                                <h2 class="text-2xl font-bold">Módulo de Validación</h2>
                                <div class="text-xs text-slate-600">
                                    {{ documentos.length }} documentos cargados.<br>
                                    {{ todosValidados }} validados <br>
                                    {{ hayRechazos }} rechazados
                                </div>
                            </div>

                            <!-- Lista de documentos con scroll -->
                            <div class="flex-1 overflow-y-auto p-2 space-y-1">
                              
                                    <DocumentValidator 
                                        v-for="doc in documentos" 
                                        :key="doc.id"   
                                        :id="doc.id"
                                        :docName="doc.name"
                                        :docUrl="doc.url"
                                        :isActive="docSeleccionado?.id === doc.id"
                                        v-model="doc.validation"
                                        @view="verDocumento"
                                    />
                                
                            </div>

                            <!-- Botón fijo al fondo -->
                            <div class="p-6 border-t bg-white">
                                <button 
                                    @click="enviarRevision"
                                    :disabled="documentos.length !== (todosValidados + hayRechazos)"
                                    :class="todosValidados == documentos.length ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'"
                                    class="w-full px-6 py-3 text-white rounded-lg shadow-md disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
                                >
                                    {{ todosValidados == documentos.length ? '✓ Aprobar Registro' : hayRechazos > 0 ? '✗ Notificar Rechazos' : 'Verificar documentos' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Panel derecho: Vista previa del documento -->
                    <div class="w-7/12 bg-white rounded-lg shadow overflow-hidden flex flex-col h-full">
                        <div v-if="docSeleccionado" class="h-full flex flex-col">
                            <!-- Header del documento -->
                            <div class="p-4 border-b bg-gray-100">
                                  
                                <h3 class="font-bold text-lg"><span class="text-sky-700">Validando: </span>{{ docSeleccionado.name }}</h3>
                               
                            </div>
                            
                            <!-- Visor con scroll independiente -->
                            <div class="flex-1 overflow-auto p-4 bg-gradient-to-t from-gray-900 to-gray-100">
                                <!-- Vista previa para PDF -->
                                <iframe 
                                    v-if="docSeleccionado.url.endsWith('.pdf')" 
                                    :src="'/storage/' + docSeleccionado.url" 
                                    class="w-full h-full min-h-[600px] border rounded bg-white"
                                ></iframe>
                                
                                <!-- Vista previa para imágenes -->
                                <img 
                                    v-else 
                                    :src="'/storage/' + docSeleccionado.url"
                                    :alt="docSeleccionado.name"
                                    class="max-w-full h-auto border rounded mx-auto bg-white shadow-sm" 
                                />
                            </div>
                        </div>
                        
                        <!-- Mensaje cuando no hay documento seleccionado -->
                        <div v-else class="h-full flex items-center justify-center text-gray-400">
                            <div class="text-center">
                                <svg class="w-20 h-20 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-xl font-medium">Selecciona un documento</p>
                                <p class="text-sm mt-2">Haz clic en "Ver documento" para visualizarlo aquí</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<style scoped>
/* Personalizar scrollbar (opcional) */
.overflow-y-auto::-webkit-scrollbar {
    width: 8px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}


</style>
