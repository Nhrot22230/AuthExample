<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Curso;
use App\Models\Departamento;
use App\Models\Especialidad;
use App\Models\Facultad;
use App\Models\Institucion;
use App\Models\PlanEstudio;
use App\Models\Seccion;
use App\Models\Semestre;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UniversidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {   
        Institucion::factory(5)->create();
        Facultad::factory(20)->create();
        Departamento::factory(30)->create();
        Especialidad::factory(100)->create();
        Seccion::factory(50)->create();
        Area::factory(10)->create();
        
        $int_year_now = date('Y');
        $anhos = range(
            $int_year_now - 10,
            $int_year_now
        );
        foreach ($anhos as $anho) {
            $fechaInicio = $anho . '-01-15';
            $fechaFin = $anho . '-02-25';
            Semestre::factory()->create([
                'anho' => $anho,
                'periodo' => '0',
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'estado' => 'inactivo',
            ]);
            $fechaInicio = $anho . '-03-15';
            $fechaFin = $anho . '-06-01';
            Semestre::factory()->create([
                'anho' => $anho,
                'periodo' => '1',
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'estado' => 'inactivo',
            ]);
            $fechaInicio = $anho . '-06-15';
            $fechaFin = $anho . '-12-20';
            Semestre::factory()->create([
                'anho' => $anho,
                'periodo' => '2',
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'estado' => 'inactivo',
            ]);
        }
        Semestre::latest('id')->first()->update(['estado' => 'activo']);

        Curso::factory(100)->create();
    }
}
