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
        $random_seccion = Seccion::inRandomOrder()->first() ?? Seccion::factory()->create();

        $factultad = $random_seccion->departamento->facultad;
        $random_especialidad = Especialidad::where('facultad_id', $factultad->id)->inRandomOrder()->first() ?? 
                                Especialidad::factory()->create(['facultad_id' => $factultad->id]);

        $random_area = Area::where('especialidad_id', $random_especialidad->id)->inRandomOrder()->first() ?? 
                                Area::factory()->create(['especialidad_id' => $random_especialidad->id]);

        return [
            'usuario_id' => Usuario::factory(),
            'codigoDocente' => $this->faker->unique()->randomNumber(8),
            'tipo' => $this->faker->randomElement(['TPA', 'TC']),
            'seccion_id' => $random_seccion,
            'especialidad_id' => $random_especialidad,
            'area_id' => $random_area,
        ];
    }
}
