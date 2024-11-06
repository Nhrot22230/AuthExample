<?php

namespace Database\Factories;

use App\Models\Semestre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Semestre>
 */
class SemestreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $anho = $this->faker->numberBetween(2022, 2025);
        $periodo = $this->faker->randomElement(['0', '1', '2']);
        
        while (Semestre::where('anho', $anho)->where('periodo', $periodo)->exists()) {
            $anho = $this->faker->numberBetween(2022, 2025);
            $periodo = $this->faker->randomElement(['0', '1', '2']);
        }

        return [
            'anho' => $anho,
            'periodo' => $periodo,
            'fecha_inicio' => $this->faker->date(),
            'fecha_fin' => $this->faker->date(),
            'estado' => $this->faker->randomElement(['activo', 'inactivo']),
        ];
    }
}
