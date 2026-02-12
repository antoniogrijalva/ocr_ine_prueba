<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm , Link} from '@inertiajs/vue3';



/* componentes personalizados */


    import jaga_component_input from '@/Components/personales/jaga_component_input.vue';
    import jaga_component_select from '@/Components/personales/jaga_component_select.vue';
    import jaga_component_h3 from '@/Components/personales/jaga_component_h3.vue';



 import Stepper from '@/Components/Stepper.vue';
    /* stepper*/
        const currentStep = ref(1);

        const handleNext = () => {
        if (currentStep.value < 3) {
            currentStep.value++;
        }
        };

        const handlePrev = () => {
        if (currentStep.value > 1) {
            currentStep.value--;
        }
        };

        const handleGoTo = (step) => {
        currentStep.value = step;
        };

 const opcionesGenero = [
    { id: 'H', nombre: 'Masculino' },
    { id: 'M', nombre: 'Femenino' }
];

/* Codigo para utilizar api cloud vision de google */
            import { ref, computed, watch } from 'vue';
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
                    
                    form.ine_clave_elector = ocr_ine.datos.clave_elector || '';
                    form.ine_cic = ocr_ine.datos.cic || '';
                    form.ine_numero_emision = ocr_ine.datos.numero_emision || '';
                    form.ine_ocr= ocr_ine.datos.ocr || '';
                    form.ine_seccion = ocr_ine.datos.seccion || '';

                    form.nombre = ocr_ine.datos.nombre || '';
                    form.primer_apellido = ocr_ine.datos.primer_apellido || '';        
                    form.segundo_apellido = ocr_ine.datos.segundo_apellido || '';
                    form.nacimiento_fecha = ocr_ine.datos.fecha_nacimiento || '';
                    form.sexo = ocr_ine.datos.sexo || '';

                    //domicilio
                    form.domicilio_calle = ocr_ine.datos.calle || '';
                    form.domicilio_num_ext = ocr_ine.datos.num_exterior || '';
                    form.domicilio_num_int = ocr_ine.datos.num_interior || '';
                    form.domicilio_colonia = ocr_ine.datos.colonia || '';
                    form.domicilio_cp = ocr_ine.datos.codigo_postal || '';  
                    form.domicilio_municipio = ocr_ine.datos.municipio || '';
                    form.domicilio_localidad = ocr_ine.datos.municipio || '';

                    //para observar el domicilio completo
                    form.notificaciones_domicilio= ocr_ine.datos.domicilio_completo || '';

                    form.domicilio_estado = ocr_ine.datos.estado || '';

                    form.nacimiento_estado = ocr_ine.datos.estado_nacimiento || '';

                    


                } catch (error) {
                    // Ahora el error será más descriptivo
                    const errorReal = error.response?.data?.error || error.message;
                    console.error("Detalle técnico:", error.response?.data);
                    alert("Archivo no pudo ser procesado. Verifique que sea un archivo de la Credencial de Elector: " + errorReal);
                } finally {
                    cargando.value = false;
                }
            };
/* Codigo para utilizar api cloud vision de google */


/* catalogos */
defineProps({ 
   c_catalogo_tipo_actor_politico: {
       type: Object,
       default: () => []
   },
   c_catalogo_actor_politico: {
       type: Object,
       default: () => []
   },

   c_catalogo_tipo_eleccion: {
       type: Object,
       default: () => []
   },

   c_catalogo_municipios_distritos: {
       type: Object,
       default: () => []
   },

   c_catalogo_tipo_cargo: {
       type: Object,
       default: () => []
   },
 });

    const c_catalogo_actor_politico = ref([
        { idpartido: 1, nombre: 'PAN', id_tipo_actor_politico: 1 },
        { idpartido: 2, nombre: 'PRI', id_tipo_actor_politico: 1 },
        { idpartido: 3, nombre: 'Movimiento Ciudadano', id_tipo_actor_politico: 1 },
        { idpartido: 4, nombre: 'PRD', id_tipo_actor_politico: 1 },
        { idpartido: 5, nombre: 'PVEM', id_tipo_actor_politico: 1 },
        { idpartido: 6, nombre: 'PT', id_tipo_actor_politico: 1 },
        { idpartido: 7, nombre: 'Morena', id_tipo_actor_politico: 1 },
        { idpartido: 8, nombre: 'Fuerza por México', id_tipo_actor_politico: 1 },
        { idpartido: 9, nombre: 'RSP', id_tipo_actor_politico: 1 },
        { idpartido: 10, nombre: 'Encuentro Solidario', id_tipo_actor_politico: 1 },
        { idpartido: 11, nombre: 'Coalición Va por México', id_tipo_actor_politico: 2 },
        { idpartido: 12, nombre: 'Coalición Juntos Hacemos Historia', id_tipo_actor_politico: 2 },
        { idpartido: 13, nombre: 'Candidato Independiente', id_tipo_actor_politico: 3 },
        { idpartido: 14, nombre: 'Candidatura Común', id_tipo_actor_politico: 4 },
    ]);

    const c_catalogo_tipo_actor_politico = ref([
        { id_tipo_actor_politico: 1, nombre: 'Partido Politico' },
        { id_tipo_actor_politico: 2, nombre: 'Coalición' },
        { id_tipo_actor_politico: 3, nombre: 'Candidato Independiente' },
        { id_tipo_actor_politico: 4, nombre: 'Candidatura Común' },
    ]);

    const c_catalogo_tipo_eleccion = ref([
        { id_tipo_eleccion: 1, nombre: 'Gobernador' },
        { id_tipo_eleccion: 2, nombre: 'Ayuntamiento' },
        { id_tipo_eleccion: 3, nombre: 'Diputaciones MR' },
        { id_tipo_eleccion: 4, nombre: 'Diputaciones RP' },
     
    ]);

    const c_catalogo_municipios_distritos = ref([
        { id_municipio_distrito: 1, id_tipo_eleccion: 2, num_regidores: 10, nombre: 'Municipio 1' },
        { id_municipio_distrito: 2, id_tipo_eleccion: 2, num_regidores: 8,  nombre: 'Municipio 2' },
        { id_municipio_distrito: 3, id_tipo_eleccion: 3, num_regidores: 0,  nombre: 'Distrito 1' },
        { id_municipio_distrito: 4, id_tipo_eleccion: 3, num_regidores: 0,  nombre: 'Distrito 2' },
        
    ]);

    const c_catalogo_tipo_cargo = ref([
        { id_tipo_cargo: 1, id_tipo_eleccion: 1, nombre: 'Gobernador'          },

        { id_tipo_cargo: 2, id_tipo_eleccion: 2, nombre: 'Presidente Municipal'},
        { id_tipo_cargo: 3, id_tipo_eleccion: 2, nombre: 'Sindico Propietario' },
        { id_tipo_cargo: 4, id_tipo_eleccion: 2, nombre: 'Síndico Suplente'    },
        { id_tipo_cargo: 5, id_tipo_eleccion: 2, nombre: 'Regidor Propietario' },
        { id_tipo_cargo: 6, id_tipo_eleccion: 2, nombre: 'Regidor Suplente'    },


        { id_tipo_cargo: 7, id_tipo_eleccion: 3, nombre: 'Diputacion Propietaria (MR)' },
        { id_tipo_cargo: 8, id_tipo_eleccion: 3, nombre: 'Diputacion Suplente (MR)' },

        { id_tipo_cargo: 9, id_tipo_eleccion: 4, nombre: 'Diputacion Propietaria (RP)' },
        { id_tipo_cargo: 10, id_tipo_eleccion: 4, nombre: 'Diputacion Suplente (RP)' },
        
    ]);

   // const c_catalogo_etnias = ref([
  
  
   const c_catalogo_etnias = ref([
        { id: 1, nombre: 'Hiak (Yaqui)' },
        { id: 2, nombre: 'Yorem Maayo (Mayo)' },
        { id: 3, nombre: 'Makurawe (Guarijío)' },
        { id: 4, nombre: 'Tohono O´otham (Pápago)' },
        { id: 5, nombre: 'Comca´ac (Seri)' },
        { id: 6, nombre: 'O´ob (Pima)' },
        { id: 7, nombre: 'Kuapá (Cucapa)' },
        { id: 8, nombre: 'Kickapoó (Kikapú)' },
        { id: 9, nombre: 'Lipan Apache (Apaches Chiricahua, Coyotero)' },
    ]);




/* catalogos */

const form = useForm({

    id_tipo_actor_politico:0,
    id_actor_politico:0,

    /* cargo */
    id_tipo_eleccion:0,
    id_tipo_cargo:0,
    id_municipio_distrito:0,
    id_numero:0,

   /* datos generales */
    nombre: '',
    primer_apellido: '',
    segundo_apellido: '',
    alias: '',
    domicilio_calle: '',
    domicilio_num_ext: '',
    domicilio_num_int: '',
    domicilio_colonia: '',
    domicilio_estado: '',
    domicilio_municipio: '',
    domicilio_localidad: '',
    domicilio_cp: '',
    domicilio_telefono: '',
    domicilio_email: '',
    curp: '',
   
    sexo: '',
    
    /* para notificaciones */
    notificaciones_email: '',
    notificaciones_domicilio: '',


    /* grupo vulnerable - etnia, discapacidad, lgbttiq+ */

    grupo_vulnerable: false,
    grupo_vulnerable_etnia: 0,
    grupo_vulnerable_discapacidad: '',
    grupo_vulnerable_lgbttiq: '',
    grupo_vulnerable_otro: '',
    accion_afirmativa: '',

    /*tiempo de residencia */
    tiempo_residencia_anios: null,
    tiempo_residencia_meses: null,
   

    
   /* datos credencial INE */ 
    ine_clave_elector: '',
    ine_seccion: '',
    ine_numero_emision: '',
    ine_ocr: '',
    ine_cic: '',

   /* datos de nacimiento */
    nacimiento_fecha: null,
    nacimiento_estado: '',
    nacimiento_municipio: '',


    registro_fecha: null,

    idestatus: 1, // 1 = captura, 2 = enviado a revision, 3 = aceptado, 4 = rechazado

});

    // Filtrar actores políticos según el tipo seleccionado
        // const actoresPoliticosFiltrados = computed(() => {
        //     if (!form.id_tipo_actor_politico || form.id_tipo_actor_politico === 0) {
        //         return [];
        //     }
        //     return c_catalogo_actor_politico.value.filter(
        //         actor => actor.id_tipo_actor_politico === form.id_tipo_actor_politico
        //     );
        // });

        //calcular edad
        const calcula_edad = computed(() => {
            if (!form.nacimiento_fecha) {
            return '';
            }
            const hoy = new Date();
            //console.log(hoy);
            const nacimiento = new Date(form.nacimiento_fecha);
            let edad = hoy.getFullYear() - nacimiento.getFullYear();
            const mes = hoy.getMonth() - nacimiento.getMonth();
            const dia = hoy.getDate() - nacimiento.getDate();
            if (mes < 0 || (mes === 0 && dia < 0)) {
            edad--;
            }
            return edad;
        });

        const actoresPoliticosFiltrados = computed(() => {
            const tipoSeleccionado = Number(form.id_tipo_actor_politico);
            if (!tipoSeleccionado || tipoSeleccionado === 0) {
                return [];
            }
            return c_catalogo_actor_politico.value.filter(
                actor => actor.id_tipo_actor_politico === tipoSeleccionado
            );
        });

    // Resetear el actor político cuando cambie el tipo
        watch(() => form.id_tipo_actor_politico, (newValue) => {
            form.id_actor_politico = 0;
        });


    // Filtrar municipios/distritos según el tipo de elección seleccionado
        const municipiosDistritosFiltrados = computed(() => {
            const eleccionSeleccionado = Number(form.id_tipo_eleccion);
            if (!form.id_tipo_eleccion || form.id_tipo_eleccion === 0) {
                return [];
            }
                // return c_catalogo_municipios_distritos.value.filter(
                //     item => item.id_tipo_eleccion === form.id_tipo_eleccion
                // );
            return c_catalogo_municipios_distritos.value.filter(
                actor => actor.id_tipo_eleccion === eleccionSeleccionado
            );
        });



        //filtro por tipo de cargo según tipo de elección
        const CargosFiltrados = computed(() => {
            const eleccionSeleccionado = Number(form.id_tipo_eleccion);
            if (!eleccionSeleccionado || eleccionSeleccionado === 0) {
                return [];
            }
            return c_catalogo_tipo_cargo.value.filter(
                item => item.id_tipo_eleccion === eleccionSeleccionado
            );
        });

        // Generar opciones dinámicas para número de regidor/diputado
        const opcionesNumero = computed(() => {
            // Si es Diputado RP (id_tipo_cargo === 4), generar del 1 al 12
            if (Number(form.id_tipo_eleccion) === 4) {
                return Array.from({ length: 12 }, (_, i) => i + 1);
            }
            
            // Si hay municipio/distrito seleccionado, usar su num_regidores
            if (form.id_municipio_distrito && Number(form.id_municipio_distrito) > 0) {
                const selectedMunicipio = c_catalogo_municipios_distritos.value.find(
                    item => item.id_municipio_distrito === Number(form.id_municipio_distrito)
                );
                
                if (selectedMunicipio && selectedMunicipio.num_regidores > 0) {
                    return Array.from({ length: selectedMunicipio.num_regidores }, (_, i) => i + 1);
                }
            }
            
            return [];
        });

    // Resetear el municipio/distrito cuando cambie el tipo de elección
        watch(() => form.id_tipo_eleccion, (newValue) => {
            form.id_municipio_distrito = 0;
            form.id_tipo_cargo = 0;
            form.id_numero = 0;
        });

    // Resetear el número cuando cambie el tipo de cargo o municipio/distrito
        watch([() => form.id_tipo_cargo, () => form.id_municipio_distrito], () => {
            form.id_numero = 0;
        });

    //poner en blanco las opciones de grupos vulnerables en caso de que no pertenezca
        watch(() => form.grupo_vulnerable, (newValue) => {
            if (!newValue) {
                form.grupo_vulnerable_etnia = 0;
                form.grupo_vulnerable_discapacidad = '';
                form.grupo_vulnerable_lgbttiq = '';
                form.grupo_vulnerable_otro = '';
                form.accion_afirmativa = '';
            }
        });


    /* seleccionar acciones afirmativas segun lo registrado por el capturista */
                // Computed para generar opciones de acciones afirmativas
                const accionesAfirmativasOpciones = computed(() => {
                    const opciones = [];
                     
                    if (form.grupo_vulnerable_etnia && Number(form.grupo_vulnerable_etnia) > 0) {
                        const etniaSeleccionada = c_catalogo_etnias.value.find(
                            etnia => etnia.id === Number(form.grupo_vulnerable_etnia)
                        );
                        
                        if (etniaSeleccionada) {
                            opciones.push({
                                id: `etnia_${etniaSeleccionada.id}`,
                                nombre: `Candidatura Indígena (${etniaSeleccionada.nombre})`
                            });
                        }
                    }
                    
                    if (form.grupo_vulnerable_discapacidad && form.grupo_vulnerable_discapacidad.trim() !== '') {
                        opciones.push({
                            id: 'discapacidad',
                            nombre: `Candidatura para Personas con Discapacidad (${form.grupo_vulnerable_discapacidad})`
                        });
                    }
                    
                    if (form.grupo_vulnerable_lgbttiq && form.grupo_vulnerable_lgbttiq.trim() !== '') {
                        opciones.push({
                            id: 'lgbttiq',
                            nombre: `Candidatura LGBTTIQ+ (${form.grupo_vulnerable_lgbttiq})`
                        });
                    }

                    if (form.grupo_vulnerable_otro && form.grupo_vulnerable_otro.trim() !== '') {
                        opciones.push({
                            id: 'otro',
                            nombre: `Otro Grupo Vulnerable (${form.grupo_vulnerable_otro})`
                        });
                    }
                    
                    return opciones;
                });

                // Watch para resetear la acción afirmativa si cambian las opciones disponibles
                watch([() => form.grupo_vulnerable_etnia, () => form.grupo_vulnerable_discapacidad, () => form.grupo_vulnerable_lgbttiq, () => form.grupo_vulnerable_otro], () => {
                    // Si no hay opciones disponibles, resetear
                    if (accionesAfirmativasOpciones.value.length === 0) {
                        form.accion_afirmativa = '';
                    } else {
                        // Si la opción seleccionada ya no está disponible, resetear
                        const opcionExiste = accionesAfirmativasOpciones.value.some(
                            opcion => opcion.id === form.accion_afirmativa
                        );
                        if (!opcionExiste) {
                            form.accion_afirmativa = '';
                        }
                    }
                });
     /* seleccionar acciones afirmativas segun lo registrado por el capturista */

    // const submit = () => {
    //     form.post(route('personas.store'), {
    //         onFinish: () => form.reset('curp', 'clave_elector'),
    //     });
    // };
</script>

<template>
    <Head title="Registrar Persona" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Registro de Candidatos (paso-1)</h2>
                <Link :href="route('personas.index')" class="bg-indigo-600 hover:bg-indigo-300 text-white px-4 py-2 rounded shadow">
                    ← Regresar
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
               
                        <!-- Stepper solo muestra el paso actual  -->
                        <Stepper :current-step="1"/>
                            




                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                   
                    <div class="flex items-center mb-4">
                        <span class="text-xl mr-2">👨🏻‍⚖️</span>
                        <h2 class="text-xl font-semibold text-gray-700">Candidato</h2>
                    </div>

                    <!-- {{ form }} -->
                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- PARTIDO, COALICIÓN O CANDIDATO INDEPENDIENTE -->
                        <div class="border-b mb-4 bg-gray-50 rounded-lg p-4">
                            <jaga_component_h3 text="PARTIDO, COALICIÓN O CANDIDATO INDEPENDIENTE" />
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <jaga_component_select label="Tipo de Actor Político" v-model="form.id_tipo_actor_politico" :options="c_catalogo_tipo_actor_politico" option-value="id_tipo_actor_politico"  required :error="form.errors.id_tipo_actor_politico"/>  
                                <jaga_component_select label="Actor Político" v-model="form.id_actor_politico" :options="actoresPoliticosFiltrados" option-value="idpartido"  :disabled="!form.id_tipo_actor_politico || form.id_tipo_actor_politico === 0" required :error="form.errors.id_actor_politico"/>
                            </div>
                        </div>

                        <!-- CARGO PARA EL QUE SE POSTULA -->
                        <div class="border-b mb-4 bg-gray-50 rounded-lg p-4">
                            <jaga_component_h3 text="CARGO PARA EL QUE SE POSTULA" />
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <jaga_component_select label="Tipo de Elección" v-model="form.id_tipo_eleccion" :options="c_catalogo_tipo_eleccion" option-value="id_tipo_eleccion"  required :error="form.errors.id_tipo_eleccion"/>  
                                <jaga_component_select label="Municipio/Distrito" v-model="form.id_municipio_distrito" :options="municipiosDistritosFiltrados" option-value="id_municipio_distrito"  :disabled="!form.id_tipo_eleccion || form.id_tipo_eleccion === 0 || municipiosDistritosFiltrados.length === 0"  :error="form.errors.id_municipio_distrito"/>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <jaga_component_select label="Tipo de Cargo" v-model="form.id_tipo_cargo" :options="CargosFiltrados" option-value="id_tipo_cargo"  :disabled="!form.id_tipo_eleccion || form.id_tipo_eleccion === 0 || CargosFiltrados.length === 0" required :error="form.errors.id_tipo_cargo" />
                                <jaga_component_select label="No. de Regidor / Diputado" v-model="form.id_numero" :options="opcionesNumero.map(num => ({ id: num, nombre: num }))" option-value="id"  :disabled="opcionesNumero.length === 0"  :error="form.errors.id_numero" />
                            </div>
                        </div>  
                        
                         <!-- DATOS GENERALES -->
                       
                       

                        
                        <div class="border-b bg-gray-50 rounded-lg p-4">
                            <jaga_component_h3 text="DATOS PERSONALES" />
                             <!-- cloud vision google -->
                            <div class="mb-0">
                                <label class="flex flex-col items-center justify-center w-full h-16 _h-32 border  border-dashed border-slate-700 rounded-lg cursor-pointer bg-slate-100 hover:bg-slate-200 transition">
                                    <div v-if="!cargando" class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <span class="text-2xl mb-2">📷</span>
                                        <p class="text-xs text-gray-800 font-thin italic">Selecciona imagen o pdf con el <b class="text-indigo-500 font-bold">INE</b> para auto-llenado</p>
                                    </div>
                                    <div v-else class="flex flex-col items-center">
                                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-500 mt-2 mb-2"></div>
                                        <p class="text-xs text-gray-800 font-thin italic">Analizando archivo para extraer la información ...</p>
                                    </div>
                                    <input type="file" class="hidden" @change="procesarImagen" 
                                    accept="image/jpeg,image/png,image/jpg,application/pdf"  />
                                </label>
                            </div>
                            <!-- cloud vision google -->

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 mt-4">
                                <jaga_component_input label="Nombre"                :required="true" placeholder="Escriba el o los nombres" maxlength="30" v-model="form.nombre" :error="form.errors.nombre"/>
                                <jaga_component_input label="Primer Apellido"                   placeholder="Escriba el primer apellido" maxlength="30" v-model="form.primer_apellido" :error="form.errors.primer_apellido"/>
                                <jaga_component_input label="Segundo Apellido"                  placeholder="Escriba el segundo apellido" maxlength="30" v-model="form.segundo_apellido" :error="form.errors.segundo_apellido"/>
                                <jaga_component_input label="Alias/Sobrenombre"                 placeholder="Escriba el alias o sobrenombre" maxlength="30" v-model="form.alias" :error="form.errors.alias"/>
                                <jaga_component_input label="Calle del Domicilio"   :required="true"           placeholder="Escriba la calle del domicilio" maxlength="50" v-model="form.domicilio_calle" :error="form.errors.domicilio_calle"/>
                                <div class="grid grid-cols-2 gap-4">  
                                    <jaga_component_input label="Número Exterior"   placeholder="Escriba el número exterior del domicilio" maxlength="10" v-model="form.domicilio_num_ext" :error="form.errors.domicilio_num_ext"/>
                                    <jaga_component_input label="Número Interior"   placeholder="Escriba el número interior del domicilio" maxlength="10" v-model="form.domicilio_num_int" :error="form.errors.domicilio_num_int"/>
                                </div>
                                <jaga_component_input label="Colonia"         :required="true"     placeholder="Escriba la colonia del domicilio" maxlength="50" v-model="form.domicilio_colonia" :error="form.errors.domicilio_colonia"/>
                                <jaga_component_input label="Estado"          :required="true"     placeholder="Escriba el estado del domicilio" maxlength="50" v-model="form.domicilio_estado" :error="form.errors.domicilio_estado"/>
                                <jaga_component_input label="Municipio"       :required="true"     placeholder="Escriba el municipio del domicilio" maxlength="50" v-model="form.domicilio_municipio" :error="form.errors.domicilio_municipio"/>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-4">
                                    <jaga_component_input label="Localidad"  class="col-span-3"      :required="true"    placeholder="Escriba la localidad del domicilio" maxlength="50" v-model="form.domicilio_localidad" :error="form.errors.domicilio_localidad"/>
                                    <jaga_component_input label="C.P."             :required="true"    placeholder="Escriba el código postal del domicilio" maxlength="10" v-model="form.domicilio_cp" :error="form.errors.domicilio_cp"/>
                                    <jaga_component_input label="Teléfono Personal" :required="true"   class="col-span-2" placeholder="Escriba el teléfono personal" maxlength="15" v-model="form.domicilio_telefono" :error="form.errors.domicilio_telefono"/>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">   
                                <jaga_component_input label="Email Personal"    placeholder="Escriba el email personal" maxlength="100" type="email" v-model="form.domicilio_email" :error="form.errors.domicilio_email"/>
                                <jaga_component_input label="CURP"             placeholder="Escriba la CURP" maxlength="18" v-model="form.curp" :error="form.errors.curp"/>
                                <jaga_component_select label="Género" v-model="form.sexo" :options="opcionesGenero" option-value="id"  required/>
                            </div> 
                      
                        </div>

                        <div class="border-b pb-4 bg-gray-50 p-4 rounded-lg">
                            <jaga_component_h3 text="Datos de contacto para Notificaciones" />
                            <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mt-4">
                                <jaga_component_input label="Email para Notificaciones" :required="true"    class="col-span-2" placeholder="Escriba el email para notificaciones" maxlength="100" type="email" v-model="form.notificaciones_email" :error="form.errors.notificaciones_email"/>
                                <jaga_component_input label="Domicilio para Notificaciones" :required="true"    class="col-span-4" placeholder="Escriba el domicilio para notificaciones" maxlength="100" v-model="form.notificaciones_domicilio" :error="form.errors.notificaciones_domicilio"/>   
                            
                            </div>
                        </div>

                        <div class="border-b pb-4 bg-gray-50 p-4 rounded-lg">
                            <jaga_component_h3 text="Etnia o Grupo al que pertenece"  />
                            <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mt-4">
                                
                                <div class="col-span-6">
                                    <label class="block text-sm font-medium text-gray-700">¿Se considera parte de algún grupo vulnerable?</label>
                                    <div class="mt-2 flex items-center space-x-4">
                                        <label class="inline-flex items-center">
                                            <input type="radio" v-model="form.grupo_vulnerable" :value="true" class="form-radio">
                                            <span class="ml-2">Sí</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" v-model="form.grupo_vulnerable" :value="false" class="form-radio">
                                            <span class="ml-2">No</span>
                                        </label>
                                    </div>
                                    <div v-if="form.errors.grupo_vulnerable" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.grupo_vulnerable }}
                                    </div>
                                </div>
                                
                                <jaga_component_select
                                    label="Etnia"  
                                    :required="false"    
                                    
                                    v-model="form.grupo_vulnerable_etnia" 
                                    :options="c_catalogo_etnias" 
                                    option-value="id"
                                    option-label="nombre"
                                    :error="form.errors.grupo_vulnerable_etnia" 
                                    :disabled="!form.grupo_vulnerable"
                                    class="col-span-3"
                                />

                                  
                                <jaga_component_input 
                                    label="Discapacidad"  
                                    :required="false"    
                                    placeholder="Escriba el tipo de discapacidad si aplica" 
                                    maxlength="50" 
                                    v-model="form.grupo_vulnerable_discapacidad" 
                                    :error="form.errors.grupo_vulnerable_discapacidad" 
                                    :disabled="!form.grupo_vulnerable"
                                    class="col-span-3"
                                />
                                <jaga_component_input 
                                    label="LGBTTIQ+"  
                                    :required="false"    
                                    placeholder="Indique si pertenece a la comunidad LGBTTIQ+" 
                                    maxlength="50" 
                                    v-model="form.grupo_vulnerable_lgbttiq" 
                                    :error="form.errors.grupo_vulnerable_lgbttiq" 
                                    :disabled="!form.grupo_vulnerable"
                                    class="col-span-3"
                                />
                                <jaga_component_input 
                                    label="Otro Grupo Vulnerable"  
                                    :required="false"    
                                    placeholder="Escriba otro grupo vulnerable al que pertenezca si no se encuentra en las opciones anteriores" 
                                    maxlength="50" 
                                    v-model="form.grupo_vulnerable_otro" 
                                    :error="form.errors.grupo_vulnerable_otro" 
                                    :disabled="!form.grupo_vulnerable"
                                    class="col-span-3"
                                />
                                <jaga_component_select 
                                    label="Su postulación es bajo una acción afirmativa?"  
                                    :required="false"    
                                    v-model="form.accion_afirmativa" 
                                    :options="accionesAfirmativasOpciones"
                                    option-value="id"
                                    option-label="nombre"
                                    :error="form.errors.accion_afirmativa" 
                                    :disabled="!form.grupo_vulnerable || accionesAfirmativasOpciones.length === 0"
                                    placeholder="Seleccione una acción afirmativa"
                                    empty-message="Complete los campos anteriores para ver opciones"
                                    class="col-span-6"
                                />
                            
                            </div>

                        </div>


                        <div class="border-b pb-4 bg-gray-50 p-4 rounded-lg">
                            <jaga_component_h3 text="Información de la Credenciál para Votar" />
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-4">
                                <jaga_component_input label="Clave de Elector" v-model="form.ine_clave_elector" :required="true"    placeholder="Escriba la clave de elector" maxlength="20" :error="form.errors.ine_clave_elector" />
                                <jaga_component_input label="Sección" v-model="form.ine_seccion" :required="true"    placeholder="Escriba la sección" maxlength="10" :error="form.errors.ine_seccion" />
                                <jaga_component_input label="Número de Emisión" v-model="form.ine_numero_emision" :required="true"    placeholder="Escriba el número de emisión" maxlength="10" :error="form.errors.ine_numero_emision" />
                                <jaga_component_input label="OCR" v-model="form.ine_ocr" :required="true"    type="number" placeholder="OCR" :error="form.errors.ine_ocr" />
                                <jaga_component_input label="CIC" v-model="form.ine_cic" :required="true"    placeholder="CIC" maxlength="20" :error="form.errors.ine_cic" />
                            </div>
                        </div>

                        <div class="border-b pb-4 bg-gray-50 p-4 rounded-lg">
                            <jaga_component_h3 text="Datos de Nacimiento" />
                           
                            <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mt-4">
                                <jaga_component_input label="Fecha Nacimiento" v-model="form.nacimiento_fecha" :required="true"    placeholder="Escriba el fecha de nacimiento" type="date" :error="form.errors.nacimiento_fecha" />
                                <jaga_component_input label="Edad" v-model="calcula_edad" type="number" placeholder="Escriba la edad" :error="form.errors.edad" />
                                <jaga_component_input label="Estado"  class="col-span-2" v-model="form.nacimiento_estado" :required="true"    placeholder="Estado de Nacimiento" maxlength="50" :error="form.errors.nacimiento_estado" />
                                <jaga_component_input label="Municipio"  class="col-span-2" v-model="form.nacimiento_municipio" :required="true"    placeholder="Escriba el municipio de nacimiento" maxlength="50" :error="form.errors.nacimiento_municipio" />
                            </div>
                        </div>

                        <div class="border-b pb-4 bg-gray-50 p-4 rounded-lg">
                            <jaga_component_h3 text="Fecha de Registro" />
                            <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mt-4">
                                <jaga_component_input label="Fecha de Registro" v-model="form.registro_fecha" placeholder="Escriba la fecha de registro" :disabled='true' type="date" :error="form.errors.registro_fecha" />


                                 <div class="flex justify-end col-span-5">
                                    <button 
                                        type="submit" 
                                        :disabled="form.processing"
                                        class="inline-flex items-center px-4 py-2 bg-slate-900 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-700 active:bg-slate-900 focus:outline-none focus:border-slate-900 focus:ring ring-slate-300 disabled:opacity-25 transition ease-in-out duration-150"
                                    >
                                        {{ form.processing ? 'Guardando...' : 'Guardar y Continuar a Documentos' }}
                                    </button>

                                </div>
                            </div>
                        </div>


                       
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>