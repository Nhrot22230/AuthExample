<?php

namespace Database\Seeders;


// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(UniversidadSeeder::class);
        $this->call(UsuariosSeeder::class);
        $this->call(RolePermissionSeeder::class);
        $this->call(AssignRolesAndPermissionsSeeder::class);
        $this->call(EncuestaSeeder::class);
        $this->call(PreguntaSeeder::class);
        $this->call(EncuestaPreguntaSeeder::class);
    }
}
