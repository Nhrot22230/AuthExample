<?php

namespace Database\Seeders;


// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(RolePermissionSeeder::class);
        $this->call(UniversidadSeeder::class);
        $this->call(UsuariosSeeder::class);
        $this->call(AssignRolesAndPermissionsSeeder::class);
        $this->call(TemaDeTesisSeeder::class);

        
        $this->call(EncuestaSeeder::class);
        $this->call(PreguntaSeeder::class);
        $this->call(EncuestaPreguntaSeeder::class);
        $this->call(PlanEstudioSeeder::class);
        $this->call(EstudianteRiesgoSeeder::class);
        $this->call(HorariosSeeder::class);
        $this->call(
            MatriculaAdicionalSeeder::class,
            // Otros seeders que puedas tener
        );
        
        $this->call(HorarioSeeder::class);
    }
}
