<?php

namespace Database\Factories;

use App\Models\Especialidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Curso>
 */
class CursoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $random_especialidad = Especialidad::inRandomOrder()->first();
        return [
            'especialidad_id' => $random_especialidad->id ?? Especialidad::factory(),
            'cod_curso' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'nombre' => $this->faker->sentence(3),
            'creditos' => $this->faker->numberBetween(1, 10),
            'estado' => $this->faker->randomElement(['activo', 'inactivo']),
        ];
    }
}
