<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Especialidad;
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
        return [
            'usuario_id' => Usuario::factory(),
            'codigoDocente' => $this->faker->unique()->randomNumber(8),
            'especialidad_id' => Especialidad::all()->random()->id ?? null,
            'area_id' => Area::factory(),
        ];
    }
}
