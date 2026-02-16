<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm , Link} from '@inertiajs/vue3';



/* componentes personalizados */


    import jaga_component_input from '@/Components/personales/jaga_component_input.vue';
    import jaga_component_select from '@/Components/personales/jaga_component_select.vue';
    import jaga_component_h3 from '@/Components/personales/jaga_component_h3.vue';
    import jaga_component_collapsible from '@/Components/personales/jaga_component_collapsible.vue';



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


    /* Datos especiales para mujeres */
    mujer_tipo_participacion: '', // aspirante, precandidta, candidata
    mujer_via_postulacion: '', // partido, independiente
    mujer_calidad: '', // propietaria, suplente
    mujer_rango_edad: '', // 18-30, 31-40, 41-50, 51-60, mas-60
    mujer_discapacidad: false,
    
    mujer_discapacidad_tipos: {
        visual: { seleccionada: false, descripcion: '' },
        comunicacion: { seleccionada: false, descripcion: '' },
        auditiva: { seleccionada: false, descripcion: '' },
        intelectual: { seleccionada: false, descripcion: '' },
        motriz: { seleccionada: false, descripcion: '' },
        otra: { seleccionada: false, descripcion: '' }
    },

    mujer_afromexicana: false,
    mujer_indigena: false,
    mujer_lengua_indigena: '',
    mujer_requiere_interprete: false,
    mujer_tipo_interprete: '',
    mujer_lgbtttiq: 'prefiero-no-contestar',  // si, no, prefiero-no-contestar
    mujer_lgbtttiq_especifique: ''

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


        // RED DE MUJERES:  para resetear los datos especiales de mujer si cambia el sexo
        watch(() => form.sexo, (newValue) => {
            if (newValue !== 'M') {
                form.mujer_tipo_participacion = '';
                form.mujer_via_postulacion = '';
                form.mujer_calidad = '';
                form.mujer_rango_edad = '';
                form.mujer_discapacidad = false;
                
                form.mujer_discapacidad_tipos = {
                    visual: { seleccionada: false, descripcion: '' },
                    comunicacion: { seleccionada: false, descripcion: '' },
                    auditiva: { seleccionada: false, descripcion: '' },
                    intelectual: { seleccionada: false, descripcion: '' },
                    motriz: { seleccionada: false, descripcion: '' },
                    otra: { seleccionada: false, descripcion: '' }
                };

                form.mujer_afromexicana = false;
                form.mujer_indigena = false;
                form.mujer_lengua_indigena = '';
                form.mujer_requiere_interprete = false;
                form.mujer_tipo_interprete = '';
                form.mujer_lgbtttiq = 'prefiero-no-contestar';
                form.mujer_lgbtttiq_especifique = '';
            }
        });

        // Watch para limpiar tipo de discapacidad si no tiene discapacidad
        watch(() => form.mujer_discapacidad, (newValue) => {
            if (!newValue) {
                form.mujer_discapacidad_tipos = {
                    visual: { seleccionada: false, descripcion: '' },
                    comunicacion: { seleccionada: false, descripcion: '' },
                    auditiva: { seleccionada: false, descripcion: '' },
                    intelectual: { seleccionada: false, descripcion: '' },
                    motriz: { seleccionada: false, descripcion: '' },
                    otra: { seleccionada: false, descripcion: '' }
                };
            }
        });

        // Watch para limpiar descripción si se desmarca un tipo de discapacidad
        watch(() => form.mujer_discapacidad_tipos, (newValue) => {
            Object.keys(newValue).forEach(tipo => {
                if (!newValue[tipo].seleccionada) {
                    newValue[tipo].descripcion = '';
                }
            });
        }, { deep: true });

        // Watch para limpiar lengua indígena e intérprete si no es indígena
        watch(() => form.mujer_indigena, (newValue) => {
            if (!newValue) {
                form.mujer_lengua_indigena = '';
                form.mujer_requiere_interprete = false;
                form.mujer_tipo_interprete = '';
            }
        });

        // Watch para limpiar tipo de intérprete si no requiere
        watch(() => form.mujer_requiere_interprete, (newValue) => {
            if (!newValue) {
                form.mujer_tipo_interprete = '';
            }
        });

        // Watch para limpiar especificación LGBTTTIQ+ según la respuesta
        watch(() => form.mujer_lgbtttiq, (newValue) => {
            if (newValue !== 'si') {
                form.mujer_lgbtttiq_especifique = '';
            }
        });
        // RED DE MUJERES:  para resetear los datos especiales de mujer si cambia el sexo


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

                <Link :href="route('personas.index')" class="text-sm text-blue-600 hover:text-blue-800">
                    ← Regresar a la lista
                </Link>

                
            </div>
            
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
               
                        <!-- Stepper solo muestra el paso actual  -->
                        <Stepper :current-step="1"/>
                            




                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                   
                    <div class="flex items-center mb-4">
                        <span class="text-xl mr-2">👨🏻‍⚖️</span>
                        <h2 class="text-xl font-semibold text-gray-700">Candidato</h2>
                    </div>


                    <!-- pruebas de componente colapsable -->
                      <!-- Ejemplo 1: Datos Generales (Azul) -->
                            <jaga_component_collapsible
                                title="Componente tipo CARD para datos generales"
                                icon="<i class='fas fa-id-card'></i>"
                                border-color="border-slate-900"
                                border-grosor="border-2"
                                bg-color="bg-yellow-50"
                                header-bg-color="bg-slate-900"
                                header-text-color="text-slate-100"
                                :isOpen="true"
                            >

                                <!-- PARTIDO, COALICIÓN O CANDIDATO INDEPENDIENTE -->
                                <div class="mb-4 rounded-lg p-4">
                                    <jaga_component_h3 text="PARTIDO, COALICIÓN O CANDIDATO INDEPENDIENTE" />
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <jaga_component_select label="Tipo de Actor Político" v-model="form.id_tipo_actor_politico" :options="c_catalogo_tipo_actor_politico" option-value="id_tipo_actor_politico"  required :error="form.errors.id_tipo_actor_politico"/>  
                                        <jaga_component_select label="Actor Político" v-model="form.id_actor_politico" :options="actoresPoliticosFiltrados" option-value="idpartido"  :disabled="!form.id_tipo_actor_politico || form.id_tipo_actor_politico === 0" required :error="form.errors.id_actor_politico"/>
                                    </div>
                                </div>

                                <!-- CARGO PARA EL QUE SE POSTULA -->
                                <div class="mb-4 rounded-lg p-4">
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
                                                  
                         </jaga_component_collapsible>
                    
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


                        <!-- red de mujeres electas  -->
                        <!-- SECCIÓN ESPECIAL PARA MUJERES -->
                        <!-- <div v-if="form.sexo === 'M'" class="border-4 border-pink-600 pb-4 _bg-pink-50 p-4 rounded-lg "> -->
                            <jaga_component_collapsible v-if="form.sexo === 'M'"
                                title="Registro en la Red de Mujeres Candidatas y Electas"
                                icon="<i class='fas fa-female'></i>"
                                border-color="border-pink-600"
                                _border-grosor="border-2"
                                _bg-color="bg-yellow-50"
                                header-bg-color="bg-pink-600"
                                header-text-color="text-white"
                                :isOpen="true"
                            >

                                <!-- <jaga_component_h3 text="♀️ Registro en la Red de Mujeres Candidatas y Electas"  /> -->
                                
                                <!-- Tipo de Participación y Vía de Postulación -->
                                <!-- <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mt-4  bg-pink-100 p-2 rounded-lg shadow-md ">
                                    <div class="col-span-2 border-r border-pink-300">
                                        <label class="block text-sm font-medium text-pink-700 mb-1">Soy mujer:</label>
                                        <div class="space-y-0">
                                            <label class="inline-flex items-center mr-4">
                                                <input type="radio" v-model="form.mujer_tipo_participacion" value="aspirante" class="form-radio">
                                                <span class="ml-2">Aspirante</span>
                                            </label>
                                            <label class="inline-flex items-center mr-4">
                                                <input type="radio" v-model="form.mujer_tipo_participacion" value="precandidta" class="form-radio">
                                                <span class="ml-2">Pre-candidata</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" v-model="form.mujer_tipo_participacion" value="candidata" class="form-radio">
                                                <span class="ml-2">Candidata</span>
                                            </label>
                                        </div>
                                        <div v-if="form.errors.mujer_tipo_participacion" class="text-red-500 text-sm mt-1">
                                            {{ form.errors.mujer_tipo_participacion }}
                                        </div>
                                    </div>

                                    <div class="col-span-2 border-r border-pink-300">
                                        <label class="block text-sm font-medium text-pink-700 mb-1">Vía de Postulación:</label>
                                        <div class="space-y-0">
                                            <label class="inline-flex items-center mr-4">
                                                <input type="radio" v-model="form.mujer_via_postulacion" value="partido" class="form-radio">
                                                <span class="ml-2">Por el Partido Político</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" v-model="form.mujer_via_postulacion" value="independiente" class="form-radio">
                                                <span class="ml-2">Independiente</span>
                                            </label>
                                        </div>
                                        <div v-if="form.errors.mujer_via_postulacion" class="text-red-500 text-sm mt-1">
                                            {{ form.errors.mujer_via_postulacion }}
                                        </div>
                                    </div>

                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-pink-700 mb-1">Calidad:</label>
                                        <div class="space-y-0">
                                            <label class="inline-flex items-center mr-4">
                                                <input type="radio" v-model="form.mujer_calidad" value="propietaria" class="form-radio">
                                                <span class="ml-2">Propietaria</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" v-model="form.mujer_calidad" value="suplente" class="form-radio">
                                                <span class="ml-2">Suplente</span>
                                            </label>
                                        </div>
                                        <div v-if="form.errors.mujer_calidad" class="text-red-500 text-sm mt-1">
                                            {{ form.errors.mujer_calidad }}
                                        </div>
                                    </div>
                                </div> -->

                                <!-- Rango de Edad -->
                                <!-- <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mt-4">
                                    <div class="col-span-6  bg-pink-100 p-2 rounded-lg shadow-md">
                                        <label class="block text-sm font-medium text-pink-700 mb-1">Rango de edad:</label>
                                        <div class="flex flex-wrap gap-4">
                                            <label class="inline-flex items-center">
                                                <input type="radio" v-model="form.mujer_rango_edad" value="18-30" class="form-radio">
                                                <span class="ml-2">18 a 30</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" v-model="form.mujer_rango_edad" value="31-40" class="form-radio">
                                                <span class="ml-2">31 a 40</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" v-model="form.mujer_rango_edad" value="41-50" class="form-radio">
                                                <span class="ml-2">41 a 50</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" v-model="form.mujer_rango_edad" value="51-60" class="form-radio">
                                                <span class="ml-2">51 a 60</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" v-model="form.mujer_rango_edad" value="mas-60" class="form-radio">
                                                <span class="ml-2">Más de 60</span>
                                            </label>
                                        </div>
                                        <div v-if="form.errors.mujer_rango_edad" class="text-red-500 text-sm mt-1">
                                            {{ form.errors.mujer_rango_edad }}
                                        </div>
                                    </div>
                                </div> -->

                                <!-- Discapacidad -->
                                <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mt-1">
                                    <div class="col-span-6  bg-pink-100 p-2 rounded-lg shadow-md">
                                        <label class="block text-sm font-medium text-pink-700 mb-1">¿Se encuentra en situación de discapacidad permanente?</label>
                                        <div class="flex items-center space-x-4 mb-3">
                                            <label class="inline-flex items-center">
                                                <input type="radio" v-model="form.mujer_discapacidad" :value="true" class="form-radio">
                                                <span class="ml-2">Sí</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" v-model="form.mujer_discapacidad" :value="false" class="form-radio">
                                                <span class="ml-2">No</span>
                                            </label>
                                        </div>

                                        <div v-if="form.mujer_discapacidad" class="ml-4 space-y-2">
                                            <label class="block text-sm font-medium text-gray-600 mb-">Tipo de discapacidad (puede seleccionar varias):</label>
                                            
                                            <div class="grid grid-cols-3 gap-2">
                                                <!-- Visual -->
                                                <div class="border-l-4 border-pink-300 pl-2 py-2">
                                                    <label class="inline-flex items-center mb-2">
                                                        <input 
                                                            type="checkbox" 
                                                            v-model="form.mujer_discapacidad_tipos.visual.seleccionada" 
                                                            class="form-checkbox h-5 w-5 text-pink-600"
                                                        >
                                                        <span class="ml-2 font-medium">Visual</span>
                                                    </label>
                                                    <jaga_component_input 
                                                        v-if="form.mujer_discapacidad_tipos.visual.seleccionada"
                                                        label="Descripción de la discapacidad visual"
                                                        placeholder="Describa el tipo de discapacidad visual"
                                                        maxlength="200"
                                                        v-model="form.mujer_discapacidad_tipos.visual.descripcion"
                                                        :error="form.errors['mujer_discapacidad_tipos.visual.descripcion']"
                                                    />
                                                </div>

                                                <!-- Comunicación -->
                                                <div class="border-l-4 border-pink-300 pl-2 py-2">
                                                    <label class="inline-flex items-center mb-2">
                                                        <input 
                                                            type="checkbox" 
                                                            v-model="form.mujer_discapacidad_tipos.comunicacion.seleccionada" 
                                                            class="form-checkbox h-5 w-5 text-pink-600"
                                                        >
                                                        <span class="ml-2 font-medium">Para comunicarse verbalmente</span>
                                                    </label>
                                                    <jaga_component_input 
                                                        v-if="form.mujer_discapacidad_tipos.comunicacion.seleccionada"
                                                        label="Descripción de la discapacidad de comunicación"
                                                        placeholder="Describa el tipo de discapacidad de comunicación"
                                                        maxlength="200"
                                                        v-model="form.mujer_discapacidad_tipos.comunicacion.descripcion"
                                                        :error="form.errors['mujer_discapacidad_tipos.comunicacion.descripcion']"
                                                    />
                                                </div>

                                                <!-- Auditiva -->
                                                <div class="border-l-4 border-pink-300 pl-2 py-2">
                                                    <label class="inline-flex items-center mb-2">
                                                        <input 
                                                            type="checkbox" 
                                                            v-model="form.mujer_discapacidad_tipos.auditiva.seleccionada" 
                                                            class="form-checkbox h-5 w-5 text-pink-600"
                                                        >
                                                        <span class="ml-2 font-medium">Auditiva</span>
                                                    </label>
                                                    <jaga_component_input 
                                                        v-if="form.mujer_discapacidad_tipos.auditiva.seleccionada"
                                                        label="Descripción de la discapacidad auditiva"
                                                        placeholder="Describa el tipo de discapacidad auditiva"
                                                        maxlength="200"
                                                        v-model="form.mujer_discapacidad_tipos.auditiva.descripcion"
                                                        :error="form.errors['mujer_discapacidad_tipos.auditiva.descripcion']"
                                                    />
                                                </div>

                                                <!-- Intelectual -->
                                                <div class="border-l-4 border-pink-300 pl-2 py-2">
                                                    <label class="inline-flex items-center mb-2">
                                                        <input 
                                                            type="checkbox" 
                                                            v-model="form.mujer_discapacidad_tipos.intelectual.seleccionada" 
                                                            class="form-checkbox h-5 w-5 text-pink-600"
                                                        >
                                                        <span class="ml-2 font-medium">Intelectual</span>
                                                    </label>
                                                    <jaga_component_input 
                                                        v-if="form.mujer_discapacidad_tipos.intelectual.seleccionada"
                                                        label="Descripción de la discapacidad intelectual"
                                                        placeholder="Describa el tipo de discapacidad intelectual"
                                                        maxlength="200"
                                                        v-model="form.mujer_discapacidad_tipos.intelectual.descripcion"
                                                        :error="form.errors['mujer_discapacidad_tipos.intelectual.descripcion']"
                                                    />
                                                </div>

                                                <!-- Motriz -->
                                                <div class="border-l-4 border-pink-300 pl-2 py-2">
                                                    <label class="inline-flex items-center mb-2">
                                                        <input 
                                                            type="checkbox" 
                                                            v-model="form.mujer_discapacidad_tipos.motriz.seleccionada" 
                                                            class="form-checkbox h-5 w-5 text-pink-600"
                                                        >
                                                        <span class="ml-2 font-medium">Motriz</span>
                                                    </label>
                                                    <jaga_component_input 
                                                        v-if="form.mujer_discapacidad_tipos.motriz.seleccionada"
                                                        label="Descripción de la discapacidad motriz"
                                                        placeholder="Describa el tipo de discapacidad motriz"
                                                        maxlength="200"
                                                        v-model="form.mujer_discapacidad_tipos.motriz.descripcion"
                                                        :error="form.errors['mujer_discapacidad_tipos.motriz.descripcion']"
                                                    />
                                                </div>

                                                <!-- Otra -->
                                                <div class="border-l-4 border-pink-300 pl-2 py-2">
                                                    <label class="inline-flex items-center mb-2">
                                                        <input 
                                                            type="checkbox" 
                                                            v-model="form.mujer_discapacidad_tipos.otra.seleccionada" 
                                                            class="form-checkbox h-5 w-5 text-pink-600"
                                                        >
                                                        <span class="ml-2 font-medium">Otra</span>
                                                    </label>
                                                    <jaga_component_input 
                                                        v-if="form.mujer_discapacidad_tipos.otra.seleccionada"
                                                        label="Descripción de otra discapacidad"
                                                        placeholder="Describa el tipo de discapacidad"
                                                        maxlength="200"
                                                        v-model="form.mujer_discapacidad_tipos.otra.descripcion"
                                                        :error="form.errors['mujer_discapacidad_tipos.otra.descripcion']"
                                                    />
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <!-- Afromexicana e Indígena -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                    <div class="bg-pink-100 p-2 rounded-lg shadow-md">
                                        <label class="block text-sm font-medium text-pink-700 mb-1">¿Se reconoce como mujer afromexicana?</label>
                                        <div class="flex items-center space-x-4">
                                            <label class="inline-flex items-center">
                                                <input type="radio" v-model="form.mujer_afromexicana" :value="true" class="form-radio">
                                                <span class="ml-2">Sí</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" v-model="form.mujer_afromexicana" :value="false" class="form-radio">
                                                <span class="ml-2">No</span>
                                            </label>
                                        </div>
                                        <div v-if="form.errors.mujer_afromexicana" class="text-red-500 text-sm mt-1">
                                            {{ form.errors.mujer_afromexicana }}
                                        </div>
                                    </div>

                                    <div class="bg-pink-100 p-2 rounded-lg shadow-md">
                                        <label class="block text-sm font-medium text-pink-700 mb-1">¿Se reconoce como mujer indígena?</label>
                                        <div class="flex items-center space-x-4">
                                            <label class="inline-flex items-center">
                                                <input type="radio" v-model="form.mujer_indigena" :value="true" class="form-radio">
                                                <span class="ml-2">Sí</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" v-model="form.mujer_indigena" :value="false" class="form-radio">
                                                <span class="ml-2">No</span>
                                            </label>
                                        </div>
                                        <div v-if="form.errors.mujer_indigena" class="text-red-500 text-sm mt-1">
                                            {{ form.errors.mujer_indigena }}
                                        </div>
                                        <!-- Lengua Indígena e Intérprete -->
                                        <div v-if="form.mujer_indigena" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                                            <jaga_component_input 
                                                label="¿Cuál lengua indígena u originaria habla?" 
                                                class="col-span-2"
                                                placeholder="Especifique la lengua indígena"
                                                maxlength="100"
                                                v-model="form.mujer_lengua_indigena"
                                                :error="form.errors.mujer_lengua_indigena"
                                            />

                                            <div class="col-span-2">
                                                <label class="block text-sm font-medium text-pink-700 mb-1">¿Requiere de intérprete?</label>
                                                <div class="flex items-center space-x-4 mb-2">
                                                    <label class="inline-flex items-center">
                                                        <input type="radio" v-model="form.mujer_requiere_interprete" :value="true" class="form-radio">
                                                        <span class="ml-2">Sí</span>
                                                    </label>
                                                    <label class="inline-flex items-center">
                                                        <input type="radio" v-model="form.mujer_requiere_interprete" :value="false" class="form-radio">
                                                        <span class="ml-2">No</span>
                                                    </label>
                                                </div>
                                                
                                                <jaga_component_input 
                                                    v-if="form.mujer_requiere_interprete"
                                                    label="¿De qué tipo?"
                                                    placeholder="Especifique el tipo de intérprete"
                                                    maxlength="100"
                                                    v-model="form.mujer_tipo_interprete"
                                                    :error="form.errors.mujer_tipo_interprete"
                                                />
                                            </div>
                                        </div>

                                    </div>

                                    

                                </div>



                                <!-- LGBTTTIQ+ -->
                                <div class="grid grid-cols-1  gap-4 mt-4">
                                    <div class=" bg-pink-100 p-2 rounded-lg shadow-md">
                                        <label class="block text-sm font-medium text-pink-700 mb-1">¿Pertenece a la población LGBTTTIQ+?</label>
                                        <div class="flex items-center space-x-4 mb-1">
                                            <label class="inline-flex items-center">
                                                <input type="radio" v-model="form.mujer_lgbtttiq" value="si" class="form-radio">
                                                <span class="ml-2">Sí</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" v-model="form.mujer_lgbtttiq" value="no" class="form-radio">
                                                <span class="ml-2">No</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" v-model="form.mujer_lgbtttiq" value="prefiero-no-contestar" class="form-radio">
                                                <span class="ml-2">Prefiero no contestar</span>
                                            </label>
                                        </div>
                                        
                                        <jaga_component_input 
                                            v-if="form.mujer_lgbtttiq === 'si'"
                                            label="Especifique"
                                            class="col-span-3"
                                            placeholder="Especifique"
                                            maxlength="100"
                                            v-model="form.mujer_lgbtttiq_especifique"
                                            :error="form.errors.mujer_lgbtttiq_especifique"
                                        />
                                    </div>
                                </div>
                            </jaga_component_collapsible>
                        <!-- </div> -->
                        <!-- FIN SECCIÓN ESPECIAL PARA MUJERES -->
                        <!-- red de mujeres electas  -->

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