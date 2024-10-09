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
        $random_departamento = Departamento::whereHas('facultades')
                                           ->whereHas('secciones')
                                           ->inRandomOrder()
                                           ->first();
        if (!$random_departamento) {
            $random_departamento = Departamento::factory()->create();
        }
        $random_facultad = $random_departamento->facultades->isNotEmpty() 
                            ? $random_departamento->facultades->random() 
                            : null;
        if (!$random_facultad) {
            $random_facultad = Facultad::factory()->create(['departamento_id' => $random_departamento->id]);
        }
        $random_especialidad = Especialidad::where('facultad_id', $random_facultad->id)
                                           ->inRandomOrder()
                                           ->first();
        if (!$random_especialidad) {
            $random_especialidad = Especialidad::factory()->create(['facultad_id' => $random_facultad->id]);
        }
        $random_seccion = Seccion::where('departamento_id', $random_departamento->id)
                                  ->inRandomOrder()
                                  ->first();
        if (!$random_seccion) {
            $random_seccion = Seccion::factory()->create(['departamento_id' => $random_departamento->id]);
        }
        return [
            'usuario_id' => Usuario::factory(),
            'codigoDocente' => $this->faker->unique()->randomNumber(8),
            'tipo' => $this->faker->randomElement(['TPA', 'TC']),
            'seccion_id' => $random_seccion->id ?? null,
            'especialidad_id' => $random_especialidad->id ?? null,
            'area_id' => Area::factory(),
        ];
    }
}
