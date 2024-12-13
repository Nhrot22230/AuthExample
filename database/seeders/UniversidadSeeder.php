<?php

namespace Database\Seeders;

use App\Models\Institucion;
use App\Models\Universidad\Area;
use App\Models\Universidad\Curso;
use App\Models\Universidad\Departamento;
use App\Models\Universidad\Especialidad;
use App\Models\Universidad\Facultad;
use App\Models\Universidad\Seccion;
use App\Models\Universidad\Semestre;
use Illuminate\Database\Seeder;

class UniversidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Institucion::factory(5)->create();
        $int_year_now = date('Y');
        $anhos = range($int_year_now - 10, $int_year_now);
        $periodos = [
            ['periodo' => '0', 'fecha_inicio' => '-01-15', 'fecha_fin' => '-02-25'],
            ['periodo' => '1', 'fecha_inicio' => '-03-15', 'fecha_fin' => '-06-01'],
            ['periodo' => '2', 'fecha_inicio' => '-06-15', 'fecha_fin' => '-12-20'],
        ];

        foreach ($anhos as $anho) {
            foreach ($periodos as $periodo) {
                Semestre::factory()->create([
                    'anho' => $anho,
                    'periodo' => $periodo['periodo'],
                    'fecha_inicio' => $anho . $periodo['fecha_inicio'],
                    'fecha_fin' => $anho . $periodo['fecha_fin'],
                    'estado' => 'inactivo',
                ]);
            }
        }
        Facultad::factory()->create([
            'nombre' => "Facultad de Gastronomía Espacial",
            'abreviatura' => "FDF",
            'anexo' => 123
        ]
        );

        Semestre::latest('id')->first()->update(['estado' => 'activo']);
        Curso::factory(50)->create();
    }
}
