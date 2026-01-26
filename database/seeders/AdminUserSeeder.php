<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = \App\Models\User::create([
            'name' => 'Antonio Grijalva',
            'email' => 'antoniogrijalvaamaya@gmail.com',
            'password' => bcrypt('12345678'),
        ]);

       // $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'administrador']);
        $user->assignRole('administrador');
    }
}
