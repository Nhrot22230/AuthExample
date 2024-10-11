<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Departamento;
use App\Models\Especialidad;
use App\Models\Facultad;
use App\Models\Seccion;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Docente>
 */
class DocenteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $random_facultad = Facultad::whereHas('departamentos.secciones')->whereHas('especialidades.areas')->inRandomOrder()->first();

        if (!$random_facultad) {
            $random_facultad = Facultad::factory()->create();
        }

        $random_departamento = Departamento::factory()->create(['facultad_id' => $random_facultad->id]);
        $random_seccion = Seccion::factory()->create(['departamento_id' => $random_departamento->id]);
        $random_especialidad = Especialidad::factory()->create(['facultad_id' => $random_facultad->id]);
        $random_area = Area::factory()->create(['especialidad_id' => $random_especialidad->id]);

        return [
            'usuario_id' => Usuario::factory(),
            'codigoDocente' => $this->faker->unique()->randomNumber(8),
            'tipo' => $this->faker->randomElement(['TPA', 'TC']),
            'seccion_id' => $random_seccion ?? Seccion::factory(),
            'especialidad_id' => $random_especialidad ?? Especialidad::factory(),
            'area_id' => $random_area ?? Area::factory(),
        ];
    }
}
