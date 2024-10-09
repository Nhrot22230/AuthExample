<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Departamento;
use App\Models\Especialidad;
use App\Models\Facultad;
use App\Models\Seccion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UniversidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Departamento::factory(10)->create();
        Facultad::factory(15)->create();
        Especialidad::factory(50)->create();
        Seccion::factory(10)->create();
        Area::factory(10)->create();
    }
}
