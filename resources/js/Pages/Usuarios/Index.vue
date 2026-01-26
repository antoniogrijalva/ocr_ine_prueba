<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
const props = defineProps({
    users: Array,
    all_roles: Array,
    all_permissions: Array
});

const showModal = ref(false);
const userSelected = ref(null);

const form = useForm({
    roles: [],
    permissions: []
});

/* crear nuevo usuario */
// ... (mantenemos lo anterior y añadimos esto)
const showCreateModal = ref(false);

const createForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: [],
    permissions: []
});



const confirmDelete = (user) => {
    if (confirm(`¿Estás seguro de que deseas eliminar a ${user.name}? Esta acción no se puede deshacer.`)) {
        router.delete(route('users.destroy', user.id));
    }
};

const submitCreate = () => {
    createForm.post(route('users.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
};
/* */

const openEditModal = (user) => {
    userSelected.value = user;
    form.roles = [...user.roles];
    form.permissions = [...user.permissions];
    showModal.value = true;
};

const submit = () => {
    form.put(route('users.update', userSelected.value.id), {
        onSuccess: () => closeModal(),
    });
};

const closeModal = () => {
    showModal.value = false;
    userSelected.value = null;
    form.reset();
};
</script>

<template>
    <Head title="Gestión de Usuarios" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Usuarios</h2>
                <button @click="showCreateModal = true" class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700">
                    + Nuevo Usuario
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Roles</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Permisos Especiales</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="user in users" :key="user.id">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900">{{ user.name }}</div>
                                    <div class="text-xs text-gray-500">{{ user.email }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span v-for="role in user.roles" :key="role" class="inline-block bg-blue-100 text-blue-800 text-[10px] px-2 py-1 rounded mr-1">
                                        {{ role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span v-for="perm in user.permissions" :key="perm" class="inline-block bg-purple-100 text-purple-800 text-[10px] px-2 py-1 rounded mr-1">
                                        {{ perm }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <button @click="openEditModal(user)" class="text-indigo-600 hover:text-indigo-900 font-bold text-sm">
                                        Editar
                                    </button>
                                    
                                    <button 
                                        v-if="user.id !== $page.props.auth.user.id"
                                        @click="confirmDelete(user)" 
                                        class="text-red-600 hover:text-red-900 font-bold text-sm ml-3"
                                    >
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg w-full max-w-2xl p-6 shadow-xl">
                <h3 class="text-lg font-bold mb-4 border-b pb-2">Editando permisos de: {{ userSelected?.name }}</h3>
                
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-bold text-sm text-gray-700 mb-3 uppercase">Roles Asignados</h4>
                            <div v-for="role in all_roles" :key="role" class="flex items-center mb-2">
                                <!-- <input type="checkbox" :id="role" :value="role" v-model="form.roles" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"> -->
                                 <input 
                                    type="checkbox" 
                                    :value="role" 
                                    v-model="form.roles"
                                    :disabled="role === 'administrador' && userSelected.id === $page.props.auth.user.id"
                                >
                                <label :for="role" class="ml-2 text-sm text-gray-600 capitalize">{{ role }}</label>
                            </div>
                        </div>

                        <div>
                            <h4 class="font-bold text-sm mb-3 uppercase text-purple-600">Acciones Especiales (Permisos)</h4>
                            <div v-for="perm in all_permissions" :key="perm" class="flex items-center mb-2">
                                <input type="checkbox" :id="perm" :value="perm" v-model="form.permissions" class="rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-500">
                                <label :for="perm" class="ml-2 text-sm text-gray-600 italic">{{ perm.replace(/_/g, ' ') }}</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end space-x-3 border-t pt-4">
                        <button type="button" @click="closeModal" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">Cancelar</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition shadow-lg">
                            {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>




        <!-- crear nuevo usuario -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div class="bg-white rounded-lg w-full max-w-3xl p-6 shadow-xl">
                <h3 class="text-lg font-bold mb-4 border-b pb-2 text-indigo-700">Registrar Nuevo Usuario</h3>
                
                <form @submit.prevent="submitCreate">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                                <input v-model="createForm.name" type="text" class="mt-1 block w-full rounded-md border-gray-300" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                                <input v-model="createForm.email" type="email" class="mt-1 block w-full rounded-md border-gray-300" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Contraseña</label>
                                <input v-model="createForm.password" type="password" class="mt-1 block w-full rounded-md border-gray-300" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                                <input v-model="createForm.password_confirmation" type="password" class="mt-1 block w-full rounded-md border-gray-300" required />
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-bold text-xs uppercase text-gray-500 mb-3 text-center">Asignación Inicial</h4>
                            <div class="grid grid-cols-1 gap-2">
                                <p class="text-[10px] font-bold text-indigo-600">ROLES:</p>
                                <div v-for="role in all_roles" :key="role" class="flex items-center">
                                    <input type="checkbox" :value="role" v-model="createForm.roles" class="rounded border-gray-300 text-indigo-600">
                                    <label class="ml-2 text-sm text-gray-600 capitalize">{{ role }}</label>
                                </div>
                                
                                <hr class="my-2">
                                
                                <p class="text-[10px] font-bold text-purple-600">ACCIONES ESPECIALES:</p>
                                <div v-for="perm in all_permissions" :key="perm" class="flex items-center">
                                    <input type="checkbox" :value="perm" v-model="createForm.permissions" class="rounded border-gray-300 text-purple-600">
                                    <label class="ml-2 text-[11px] text-gray-600">{{ perm.replace(/_/g, ' ') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3 border-t pt-4">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-gray-600 hover:underline">Cancelar</button>
                        <button type="submit" :disabled="createForm.processing" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 shadow-lg">
                            Crear Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>


    </AuthenticatedLayout>
</template>