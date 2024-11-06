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
        $this->call(AssignRoles::class);
        $this->call(TemaDeTesisSeeder::class);
        $this->call(PlanEstudioSeeder::class);
        $this->call(EstudianteRiesgoSeeder::class);
        $this->call(HorarioSeeder::class);
        $this->call(MatriculaAdicionalSeeder::class);
        $this->call(EncuestaSeeder::class);
        $this->call(PreguntaSeeder::class);
        $this->call(EncuestaPreguntaSeeder::class);

    }
}
