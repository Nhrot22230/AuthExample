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
        // Lógica por defecto sin filtro de facultad
        $random_seccion = Seccion::inRandomOrder()->first();
        if (!$random_seccion) {
            $random_departamento = Departamento::inRandomOrder()->first() ?? Departamento::factory()->create();
            $random_seccion = Seccion::factory()->create(['departamento_id' => $random_departamento->id]);
        }

        $facultad = $random_seccion->departamento->facultad;
        $random_especialidad = Especialidad::where('facultad_id', $facultad->id)->inRandomOrder()->first();

        if (!$random_especialidad) {
            $random_especialidad = Especialidad::factory()->create(['facultad_id' => $facultad->id]);
        }

        $random_area = Area::where('especialidad_id', $random_especialidad->id)->inRandomOrder()->first();

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
            // Obtiene una seccion aleatoria de la facultad especificada
            $random_seccion = Seccion::whereHas('departamento.facultad', function ($query) use ($facultadId) {
                $query->where('id', $facultadId);
            })->inRandomOrder()->first();

            if (!$random_seccion) {
                // Crea un departamento y sección si no existen para la facultad
                $random_departamento = Departamento::where('facultad_id', $facultadId)->inRandomOrder()->first() 
                    ?? Departamento::factory()->create(['facultad_id' => $facultadId]);
                $random_seccion = Seccion::factory()->create(['departamento_id' => $random_departamento->id]);
            }

            // Obtiene una especialidad aleatoria de la facultad especificada
            $random_especialidad = Especialidad::where('facultad_id', $facultadId)->inRandomOrder()->first();
            if (!$random_especialidad) {
                $random_especialidad = Especialidad::factory()->create(['facultad_id' => $facultadId]);
            }

            // Obtiene un área aleatoria de la especialidad especificada
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
