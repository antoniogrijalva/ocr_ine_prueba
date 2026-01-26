<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm , Link} from '@inertiajs/vue3';

/* cosdigp para utilizar api cloud vision de google */
    import { ref } from 'vue';
    import axios from 'axios';
    const cargando = ref(false);

    const procesarImagen = async (event) => {
        const file = event.target.files[0];
        if (!file) return;

        cargando.value = true;
        const formData = new FormData();
        formData.append('imagen', file);

        try {
            // Añadimos el header 'Accept' para ver el error real si algo falla
            const response = await axios.post(route('ocr.ine'), formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'Accept': 'application/json' 
                }
            });
            
            const ocr_ine = response.data;

            // Llenado automático de campos principales
            form.curp = ocr_ine.datos.curp || '';
            form.clave_elector = ocr_ine.datos.clave_elector || '';
            form.ine_cic = ocr_ine.datos.cic || '';


            form.nombre = ocr_ine.datos.nombre || '';
            form.primer_apellido = ocr_ine.datos.primer_apellido || '';        
            form.segundo_apellido = ocr_ine.datos.segundo_apellido || '';

            form.fecha_nacimiento = ocr_ine.datos.fecha_nacimiento || '';
            form.sexo = ocr_ine.datos.sexo || '';




           console.log(ocr_ine.texto_completo);

        } catch (error) {
            // Ahora el error será más descriptivo
            const errorReal = error.response?.data?.error || error.message;
            console.error("Detalle técnico:", error.response?.data);
            alert("Error del servidor: " + errorReal);
        } finally {
            cargando.value = false;
        }
    };



/* cosdigp para utilizar api cloud vision de google */

const form = useForm({
    nombre: '',
    primer_apellido: '',
    segundo_apellido: '',
    curp: '',
    fecha_nacimiento: '',
    sexo: '',
    domicilio: '',
    domicilio_ciudad: '',
    domicilio_estado: '',
    clave_elector: '',
    anio_emision_ine: '',
    ine_cic: '',
});

const submit = () => {
    form.post(route('personas.store'), {
        onFinish: () => form.reset('curp', 'clave_elector'),
    });
};
</script>

<template>
    <Head title="Registrar Persona" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Registro de Nueva Persona (Paso 1)</h2>
                <Link :href="route('personas.index')" class="bg-indigo-600 hover:bg-indigo-300 text-white px-4 py-2 rounded shadow">
                    ← Regresar
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                    <!-- cloud vision google -->
                     <div class="mb-6">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-indigo-400 rounded-lg cursor-pointer bg-indigo-50 hover:bg-indigo-100 transition">
                            <div v-if="!cargando" class="flex flex-col items-center justify-center pt-5 pb-6">
                                <span class="text-2xl mb-2">📷</span>
                                <p class="text-sm text-indigo-600 font-bold">Selecciona imagen o pdf INE para auto-llenado</p>
                            </div>
                            <div v-else class="flex flex-col items-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mb-2"></div>
                                <p class="text-sm text-gray-600">Analizando con Inteligencia Artificial...</p>
                            </div>
                            <input type="file" class="hidden" @change="procesarImagen" 
                             accept="image/jpeg,image/png,image/jpg,application/pdf"  />
                        </label>
                    </div>
                    <!-- cloud vision google -->


                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <!-- {{ form }} -->
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div class="border-b pb-4">
                            <h3 class="text-lg font-medium text-gray-900">Datos Personales</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nombre</label>
                                    <input v-model="form.nombre" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <div v-if="form.errors.nombre" class="text-red-500 text-xs mt-1">{{ form.errors.nombre }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Primer Apellido</label>
                                    <input v-model="form.primer_apellido" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <div v-if="form.errors.primer_apellido" class="text-red-500 text-xs mt-1">{{ form.errors.primer_apellido }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Segundo Apellido</label>
                                    <input v-model="form.segundo_apellido" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">CURP (18 caracteres)</label>
                                    <input v-model="form.curp" type="text" maxlength="18" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase">
                                    <div v-if="form.errors.curp" class="text-red-500 text-xs mt-1">{{ form.errors.curp }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Fecha de Nacimiento</label>
                                    <input v-model="form.fecha_nacimiento" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Sexo</label>
                                    <select v-model="form.sexo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="">Seleccione...</option>
                                        <option value="H">Hombre</option>
                                        <option value="M">Mujer</option>
                                        <option value="X">Otro</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="border-b pb-4 bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 italic">Datos de Credencial (INE)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Clave de Elector</label>
                                    <input v-model="form.clave_elector" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <div v-if="form.errors.clave_elector" class="text-red-500 text-xs mt-1">{{ form.errors.clave_elector }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Año Emisión</label>
                                    <input v-model="form.anio_emision_ine" type="number" placeholder="2024" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <div v-if="form.errors.anio_emision_ine" class="text-red-500 text-xs mt-1">{{ form.errors.anio_emision_ine }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Vencimiento CIC</label>
                                    <input v-model="form.ine_cic" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <div v-if="form.errors.ine_cic" class="text-red-500 text-xs mt-1">{{ form.errors.ine_cic }}</div>
                                </div>


                                
                            </div>
                        </div>

                        <div class="border-b pb-4 bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 italic">Domicilio Actual</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                 <div>
                                    <label class="block text-sm font-medium text-gray-700">Domicilio</label>
                                    <input v-model="form.domicilio" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <div v-if="form.errors.domicilio" class="text-red-500 text-xs mt-1">{{ form.errors.domicilio }}</div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Ciudad</label>
                                    <input v-model="form.domicilio_ciudad" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <div v-if="form.errors.domicilio_ciudad" class="text-red-500 text-xs mt-1">{{ form.errors.domicilio_ciudad }}</div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                                    <input v-model="form.domicilio_estado" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <div v-if="form.errors.domicilio_estado" class="text-red-500 text-xs mt-1">{{ form.errors.domicilio_estado }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                            >
                                {{ form.processing ? 'Guardando...' : 'Guardar y Continuar a Documentos' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>