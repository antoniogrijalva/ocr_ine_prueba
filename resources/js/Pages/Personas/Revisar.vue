<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ persona: Object });

const form = useForm({
    resultado: '',
    observaciones: '',
});

const docSeleccionado = ref(null);

const seleccionarDoc = (doc) => {
    docSeleccionado.ref = doc;
};

const submit = () => {
    form.post(route('personas.dictaminar', props.persona.id));
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

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row gap-6">
                    
                    <div class="w-full md:w-1/3 space-y-4">
                        <div class="bg-white p-4 shadow rounded-lg">
                            <h3 class="font-bold border-b pb-2 mb-4">Documentos Entregados</h3>
                            <div v-for="doc in persona.documentos" :key="doc.id" 
                                 @click="docSeleccionado = doc"
                                 :class="['p-3 rounded-md cursor-pointer mb-2 border transition', 
                                         docSeleccionado?.id === doc.id ? 'bg-indigo-50 border-indigo-500' : 'hover:bg-gray-50 border-gray-200']">
                                <p class="text-sm font-bold">{{ doc.tipo_documento.nombre }}</p>
                                <p class="text-xs text-gray-500">{{ doc.nombre_original }}</p>
                            </div>
                        </div>

                        <div class="bg-white p-4 shadow rounded-lg">
                            <h3 class="font-bold border-b pb-2 mb-4">Dictamen Final</h3>
                            <div class="space-y-4">
                                <select v-model="form.resultado" class="w-full rounded-md border-gray-300">
                                    <option value="">Seleccione resultado...</option>
                                    <option value="aceptado">✅ ACEPTAR REGISTRO</option>
                                    <option value="rechazado">❌ RECHAZAR (Con observaciones)</option>
                                </select>

                                <textarea v-if="form.resultado === 'rechazado'" 
                                          v-model="form.observaciones"
                                          placeholder="Explica el motivo del rechazo..."
                                          class="w-full rounded-md border-gray-300 text-sm" rows="3"></textarea>
                                          
                                <div v-if="form.resultado === 'rechazado' && form.errors.observaciones" class="text-red-500 text-xs">
                                    {{ form.errors.observaciones }}
                                </div>

                                <button @click="submit" 
                                        :disabled="!form.resultado || form.processing"
                                        class="w-full bg-gray-800 text-white py-2 rounded-md font-bold disabled:opacity-50">
                                    {{ form.processing ? 'Guardando...' : 'Confirmar Dictamen' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="w-full md:w-2/3 bg-white p-4 shadow rounded-lg flex flex-col items-center justify-center min-h-[600px]">
                        <div v-if="docSeleccionado" class="w-full h-full">
                            <h3 class="font-bold mb-2">{{ docSeleccionado.tipo_documento.nombre }}</h3>
                            
                            <iframe v-if="docSeleccionado.extension === 'pdf'" 
                                    :src="'/storage/' + docSeleccionado.ruta_archivo" 
                                    class="w-full h-[600px] border"></iframe>
                            
                            <img v-else :src="'/storage/' + docSeleccionado.ruta_archivo" 
                                 class="max-w-full h-auto border mx-auto" />
                        </div>
                        <div v-else class="text-gray-400 text-center">
                            <svg class="w-16 h-16 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p>Selecciona un documento de la izquierda para visualizarlo</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>