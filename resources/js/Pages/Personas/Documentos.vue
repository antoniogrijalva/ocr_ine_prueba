<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head,Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    persona: Object,
    tipos: Array,
    documentosSubidos: Array,
    can_manage: Boolean,
});

const form = useForm({
    archivo: null,
    tipo_documento_id: null,
});

// Función para subir
const uploadDocument = (tipoId) => {
    form.tipo_documento_id = tipoId;
    form.post(route('personas.documentos.store', props.persona.id), {
        onSuccess: () => {
            form.reset('archivo');
        },
    });
};

// Función para eliminar
const deleteDocument = (id) => {
    if (confirm('¿Estás seguro de que deseas eliminar este documento?')) {
        router.delete(route('documentos.destroy', id));
    }
};

// Función auxiliar para saber si un tipo de documento ya fue subido
const getDocumentoSubido = (tipoId) => {
    return props.documentosSubidos.find(doc => doc.tipo_documento_id === tipoId);
};
</script>

<template>
    <AuthenticatedLayout>

        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Documentos de: <span class="text-sky-600">{{ persona.nombre }} {{ persona.primer_apellido }} {{ persona.segundo_apellido }}</span></h2>
                <Link :href="route('personas.index')" class="bg-indigo-600 hover:bg-indigo-300 text-white px-4 py-2 rounded shadow">
                    ← Regresar
                </Link>
            </div>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div v-for="tipo in tipos" :key="tipo.id" class="bg-slate-200 p-6 shadow   sm:rounded-lg">
                        <h3 class="font-bold text-lg border-b mb-2">{{ tipo.nombre }}</h3>
                        
                        <!-- <div v-if="getDocumentoSubido(tipo.id)" class="bg-green-50 p-4 rounded-lg flex justify-between items-center">
                            <div>
                                <p class="text-sm text-green-700 font-medium">✅ Archivo cargado:</p>
                                <a :href="'/storage/' + getDocumentoSubido(tipo.id).ruta_archivo" target="_blank" class="text-blue-600 underline text-sm">
                                    Ver {{ getDocumentoSubido(tipo.id).nombre_original }}
                                </a>
                            </div>
                            
                            <button v-if="can_manage" @click="deleteDocument(getDocumentoSubido(tipo.id).id)" class="text-red-600 hover:bg-red-100 p-2 rounded">
                                🗑️ Quitar
                            </button>
                        </div>

                        <div v-else class="mt-4">
                            <p class="text-sm text-gray-600 mb-4 italic">{{ tipo.instrucciones }}</p>
                            <div class="flex items-center gap-4" v-if="can_manage">
                                <input type="file" @input="form.archivo = $event.target.files[0]" class="text-sm w-full" />
                                <button @click="uploadDocument(tipo.id)" :disabled="form.processing" class="bg-indigo-600 text-white px-4 py-2 rounded">
                                    Subir
                                </button>
                            </div>
                            <div v-if="form.errors.archivo" class="text-red-500 text-xs mt-2">{{ form.errors.archivo }}</div>
                        </div> -->
                        <div>
                            <div v-for="(doc, index) in props.documentosSubidos.filter(d => d.tipo_documento_id === tipo.id)" :key="doc.id" class="bg-green-50 p-4 rounded-lg flex justify-between items-center mb-2">
                                <div>
                                    <p class="text-sm text-green-700 font-medium">✅ Archivo cargado:</p>
                                    <a :href="'/storage/' + doc.ruta_archivo" target="_blank" class="text-blue-600 underline text-sm">
                                        Ver {{ doc.nombre_original }}
                                    </a>
                                </div>
                                
                                <button v-if="can_manage" @click="deleteDocument(doc.id)" class="text-red-600 hover:bg-red-100 p-2 rounded">
                                    🗑️ Quitar
                                </button>
                            </div>

                            <div v-if="props.documentosSubidos.filter(d => d.tipo_documento_id === tipo.id).length < tipo.max_archivos" class="mt-4">
                                <p class="text-sm text-gray-600 mb-4 italic">{{ tipo.instrucciones }}</p>
                                <div class="flex items-center gap-4" v-if="can_manage">
                                    <input type="file" @input="form.archivo = $event.target.files[0]" class="text-sm w-full" />
                                    <button @click="uploadDocument(tipo.id)" :disabled="form.processing" class="bg-indigo-600 text-white px-4 py-2 rounded">
                                        Subir
                                    </button>
                                </div>
                                <div v-if="form.errors.archivo" class="text-red-500 text-xs mt-2">{{ form.errors.archivo }}</div>
                            </div>
                        </div>



                    </div>
                </div>
            </div>
        </div>

        
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-12">
            <button @click="router.visit(route('personas.index'))" class="bg-gray-600 text-white px-4 py-2 rounded">
                ← Regresar al Listado de Personas
            </button>
        </div>
    </AuthenticatedLayout>
</template>