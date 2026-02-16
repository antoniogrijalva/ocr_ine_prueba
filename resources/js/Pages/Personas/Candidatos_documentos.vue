<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

/* componentes personalizados */
import jaga_component_h3 from '@/Components/personales/jaga_component_h3.vue';
import jaga_component_input from '@/Components/personales/jaga_component_input.vue';
import Stepper from '@/Components/Stepper.vue';

/* Props */
const props = defineProps({
    candidato: {
        type: Object,
        required: true,
        default: () => ({})
    },
    documentos: {
        type: Array,
        required: true,
        default: () => []
    }
});

/* Form para subir documentos */
const form = useForm({
    candidato_id: props.candidato.id || null,
    documentos_files: {}
});

/* Referencias reactivas */
const archivosPreview = ref({});
const cargando = ref(false);

/* Manejar selección de archivo */
const handleFileSelect = (event, documentoId) => {
    const file = event.target.files[0];
    if (!file) return;

    // Validar tamaño (max 5MB)
    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
        alert('El archivo excede el tamaño máximo permitido de 5MB');
        event.target.value = '';
        return;
    }

    // Validar tipo de archivo
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
    if (!allowedTypes.includes(file.type)) {
        alert('Solo se permiten archivos PDF, JPG, JPEG o PNG');
        event.target.value = '';
        return;
    }

    // Guardar archivo en el form
    form.documentos_files[documentoId] = file;

    // Crear preview para imágenes
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
            archivosPreview.value[documentoId] = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        archivosPreview.value[documentoId] = null;
    }
};

/* Eliminar archivo seleccionado */
const removeFile = (documentoId) => {
    delete form.documentos_files[documentoId];
    delete archivosPreview.value[documentoId];
    
    // Limpiar input file
    const input = document.querySelector(`input[data-documento-id="${documentoId}"]`);
    if (input) input.value = '';
};

/* Nombre completo del candidato */
const nombreCompleto = computed(() => {
    return `${props.candidato.nombre || ''} ${props.candidato.primer_apellido || ''} ${props.candidato.segundo_apellido || ''}`.trim();
});

/* Cargo del candidato */
const cargoCompleto = computed(() => {
    const cargo = props.candidato.tipo_cargo?.nombre || '';
    const municipio = props.candidato.municipio_distrito?.nombre || '';
    return `${cargo}${municipio ? ' - ' + municipio : ''}`;
});

/* Contar documentos subidos */
const documentosSubidos = computed(() => {
    return Object.keys(form.documentos_files).length;
});

const totalDocumentos = computed(() => {
    return props.documentos.length;
});

/* Verificar si un documento está cargado */
const documentoCargado = (documentoId) => {
    return form.documentos_files[documentoId] !== undefined;
};

/* Enviar formulario */
const submit = () => {
    if (Object.keys(form.documentos_files).length === 0) {
        alert('Debe cargar al menos un documento');
        return;
    }

    cargando.value = true;
    
    const formData = new FormData();
    formData.append('candidato_id', form.candidato_id);
    
    Object.keys(form.documentos_files).forEach(documentoId => {
        formData.append(`documento_${documentoId}`, form.documentos_files[documentoId]);
    });

    form.post(route('candidatos.documentos.store'), {
        data: formData,
        onFinish: () => {
            cargando.value = false;
        },
        onSuccess: () => {
            alert('Documentos cargados exitosamente');
        },
        onError: () => {
            alert('Error al cargar los documentos');
        }
    });
};
</script>

<template>
    <Head title="Cargar Documentos del Candidato" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Registro de Candidatos (paso-2)
                </h2>
                <Link :href="route('personas.index')" class="text-sm text-blue-600 hover:text-blue-800">
                    ← Regresar a la lista
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                 <Stepper :current-step="2"/>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Información del Candidato -->
                        <div class="bg-slate-100 border-l-4 border-slate-500 p-4 mb-6 rounded-lg">
                            <!-- <jaga_component_h3 text="👤 Información del Candidato" /> -->
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-1">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nombre Completo:</label>
                                    <p class="mt-0 text-sm font-semibold text-gray-900">{{ nombreCompleto }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">CURP:</label>
                                    <p class="mt-0 text-sm font-mono font-semibold  text-gray-900">{{ candidato.curp || 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Cargo:</label>
                                    <p class="mt-0 text-sm font-semibold text-blue-700">{{ cargoCompleto || 'N/A' }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Partido/Actor Político:</label>
                                    <p class="mt-0 text-sm font-semibold text-gray-900">{{ candidato.actor_politico?.nombre || 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Clave de Elector:</label>
                                    <p class="mt-0 text-sm font-mono font-semibold text-gray-900">{{ candidato.ine_clave_elector || 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Contador de documentos -->
                        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-semibold text-green-800">Progreso de carga</h4>
                                    <p class="text-sm text-green-700 mt-1">
                                        {{ documentosSubidos }} de {{ totalDocumentos }} documentos cargados
                                    </p>
                                </div>
                                <div class="text-4xl font-bold text-green-600">
                                    {{ Math.round((documentosSubidos / totalDocumentos) * 100) }}%
                                </div>
                            </div>
                            
                            <div class="mt-3 w-full bg-gray-200 rounded-full h-3">
                                <div 
                                    class="bg-green-600 h-3 rounded-full transition-all duration-500"
                                    :style="{ width: `${(documentosSubidos / totalDocumentos) * 100}%` }"
                                ></div>
                            </div>
                        </div>

                        <!-- Formulario de carga de documentos -->
                        <form @submit.prevent="submit" class="space-y-4">
                            <div class="border-t pt-6">
                                <jaga_component_h3 text="📎 Documentos Requeridos" />
                                
                                <div class="mt-4 space-y-1">
                                    <div 
                                        v-for="documento in documentos" 
                                        :key="documento.id"
                                        class="bg-gray-50 border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                                        :class="{ 'border-green-500 bg-green-50': documentoCargado(documento.id) }"
                                    >
                                        <div class="flex items-start justify-between">
                                          
                                                <div class="_flex-1 ">
                                                    <div class="flex items-center gap-2">
                                                        <span v-if="documentoCargado(documento.id)" class="text-green-600 text-2xl">✅</span>
                                                        <span v-else class="text-gray-400 text-2xl">📄</span>
                                                        
                                                        <div>
                                                            <h4 class="font-semibold text-gray-900">
                                                                {{ documento.nombre }}
                                                                <span v-if="documento.obligatorio" class="text-red-500">*</span>
                                                            </h4>
                                                            <p v-if="documento.descripcion" class="text-sm text-gray-600 mt-1">
                                                                {{ documento.descripcion }}
                                                            </p>
                                                            <p class="text-xs text-gray-500 mt-1">
                                                                Formatos permitidos: PDF, JPG, PNG | Tamaño máximo: 5MB
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <!-- Preview o indicador de archivo cargado -->
                                                    <div v-if="documentoCargado(documento.id)" class="mt-3 flex items-center gap-3">
                                                        <img 
                                                            v-if="archivosPreview[documento.id]" 
                                                            :src="archivosPreview[documento.id]" 
                                                            alt="Preview" 
                                                            class="w-20 h-20 object-cover rounded border"
                                                        >
                                                        <div class="flex-1">
                                                            <p class="text-sm font-medium text-green-700">
                                                                ✓ {{ form.documentos_files[documento.id]?.name }}
                                                            </p>
                                                            <p class="text-xs text-gray-600">
                                                                {{ (form.documentos_files[documento.id]?.size / 1024).toFixed(2) }} KB
                                                            </p>
                                                        </div>
                                                        <button 
                                                            type="button"
                                                            @click="removeFile(documento.id)"
                                                            class="text-red-600 hover:text-red-800 text-sm font-medium"
                                                        >
                                                            🗑️ Eliminar
                                                        </button>
                                                    </div>

                                                </div>

                                                <!-- Input de archivo -->
                                                <div class="ml-4">
                                                    <label 
                                                        :for="`file-${documento.id}`" 
                                                        class="cursor-pointer inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors"
                                                        :class="{ 'bg-green-600 hover:bg-green-700': documentoCargado(documento.id) }"
                                                    >
                                                        <span v-if="documentoCargado(documento.id)">🔄 Cambiar archivo</span>
                                                        <span v-else>📤 Seleccionar archivo</span>
                                                    </label>
                                                    <input 
                                                        :id="`file-${documento.id}`"
                                                        :data-documento-id="documento.id"
                                                        type="file" 
                                                        class="hidden"
                                                        accept=".pdf,.jpg,.jpeg,.png"
                                                        @change="handleFileSelect($event, documento.id)"
                                                    >
                                                </div>

                                            </div>
                                       

                                    </div>
                                </div>
                            </div>

                            <!-- Botones de acción -->
                            <div class="flex items-center justify-between pt-6 border-t">
                                <Link 
                                    :href="route('personas.index')" 
                                    class="px-6 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium rounded-md transition-colors"
                                >
                                    Cancelar
                                </Link>

                                <button 
                                    type="submit"
                                    :disabled="cargando || documentosSubidos === 0"
                                    class="inline-flex items-center px-4 py-2 bg-slate-900 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-700 active:bg-slate-900 focus:outline-none focus:border-slate-900 focus:ring ring-slate-300 disabled:opacity-25 transition ease-in-out duration-150"
                                >
                                    <span v-if="cargando">⏳ Cargando documentos...</span>
                                    <span v-else>💾 Guardar Documentos ({{ documentosSubidos }})</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.form-radio {
    height: 1rem;
    width: 1rem;
    color: rgb(37, 99, 235);
    border-color: rgb(209, 213, 219);
}

.form-radio:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-checkbox {
    height: 1rem;
    width: 1rem;
    color: rgb(37, 99, 235);
    border-color: rgb(209, 213, 219);
    border-radius: 0.375rem;
}

.form-checkbox:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
</style>