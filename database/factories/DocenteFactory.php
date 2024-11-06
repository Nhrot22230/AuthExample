<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Departamento;
use App\Models\Especialidad;
use App\Models\Facultad;
use App\Models\Seccion;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocenteFactory extends Factory
{
    protected $model = \App\Models\Docente::class;

    public function definition(): array
    {
        $random_seccion = Seccion::inRandomOrder()->first();
        if (!$random_seccion) {
            $random_departamento = Departamento::inRandomOrder()->first() ?? Departamento::factory()->create();
            $random_seccion = Seccion::factory()->create(['departamento_id' => $random_departamento->id]);
        }

        $facultad = $random_seccion->departamento->facultad;
        $random_especialidad = Especialidad::where('facultad_id', $facultad->id)->inRandomOrder()->first() ?? $random_especialidad = Especialidad::factory()->create(['facultad_id' => $facultad->id]);        

        $random_area = Area::where('especialidad_id', $random_especialidad->id)->inRandomOrder()->first() ?? Area::factory()->create(['especialidad_id' => $random_especialidad->id]);

        return [
            'usuario_id' => Usuario::factory(),
            'codigoDocente' => $this->faker->unique()->randomNumber(8),
            'tipo' => $this->faker->randomElement(['TPA', 'TC']),
            'seccion_id' => $random_seccion->id,
            'especialidad_id' => $random_especialidad->id,
            'area_id' => $random_area->id ?? Area::factory()->create(['especialidad_id' => $random_especialidad->id]),
        ];
    }

    public function fromFacultad($facultadId)
    {
        return $this->state(function (array $attributes) use ($facultadId) {
            $random_seccion = Seccion::whereHas('departamento.facultad', function ($query) use ($facultadId) {
                $query->where('id', $facultadId);
            })->inRandomOrder()->first();

            if (!$random_seccion) {
                $random_departamento = Departamento::where('facultad_id', $facultadId)->inRandomOrder()->first() 
                    ?? Departamento::factory()->create(['facultad_id' => $facultadId]);
                $random_seccion = Seccion::factory()->create(['departamento_id' => $random_departamento->id]);
            }

            $random_especialidad = Especialidad::where('facultad_id', $facultadId)->inRandomOrder()->first();
            if (!$random_especialidad) {
                $random_especialidad = Especialidad::factory()->create(['facultad_id' => $facultadId]);
            }

            $random_area = Area::where('especialidad_id', $random_especialidad->id)->inRandomOrder()->first()
                ?? Area::factory()->create(['especialidad_id' => $random_especialidad->id]);

            return [
                'seccion_id' => $random_seccion->id,
                'especialidad_id' => $random_especialidad->id,
                'area_id' => $random_area->id,
            ];
        });
    }
}
