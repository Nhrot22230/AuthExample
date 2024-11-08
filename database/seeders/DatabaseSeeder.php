<?php

namespace Database\Seeders;


// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        $this->call(PermissionSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(UniversidadSeeder::class);
        $this->call(UsuariosSeeder::class);
        // Flujo para Sofia
        $this->call(FlujoEncuestasSeeder::class);
        $this->call(AssignRoles::class);
    }
}
