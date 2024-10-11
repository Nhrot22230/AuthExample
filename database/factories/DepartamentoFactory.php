<?php

namespace Database\Factories;

use App\Models\Facultad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Departamento>
 */
class DepartamentoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $random_facultad = Facultad::inRandomOrder()->first();

        return [
            'nombre' => $this->faker->word,
            'descripcion' => $this->faker->sentence,
            'facultad_id' => $random_facultad ?? Facultad::factory(),
        ];
    }
}
