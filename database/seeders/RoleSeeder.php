<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Definir permisos (tareas)
        Permission::create(['name' => 'registrar personas']);
        Permission::create(['name' => 'subir documentos']);
        Permission::create(['name' => 'validar registros']);
        Permission::create(['name' => 'configurar catalogo']);

        // 2. Crear Roles y asignar permisos
        $admin = Role::create(['name' => 'administrador']);
        $admin->givePermissionTo(Permission::all());

        $capturista = Role::create(['name' => 'capturista']);
        $capturista->givePermissionTo(['registrar personas', 'subir documentos']);

        $validador = Role::create(['name' => 'validador']);
        $validador->givePermissionTo('validar registros');

        $especial = Role::create(['name' => 'especial']);
        // El perfil especial tendrá permisos según definas después
    }
}
