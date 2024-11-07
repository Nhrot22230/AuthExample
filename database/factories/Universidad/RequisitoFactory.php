<?php

namespace Database\Factories\Universidad;

use App\Models\Universidad\Curso;
use App\Models\Universidad\PlanEstudio;
use App\Models\Universidad\Requisito;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Requisito>
 */
class RequisitoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plan_estudio_id' => PlanEstudio::factory(),
            'curso_id' => Curso::factory(),
            'tipo' => $this->faker->randomElement(['llevado', 'simultaneo']),
            'curso_requisito_id' => Curso::factory(),
            'notaMinima' => $this->faker->numberBetween(0, 11),
        ];
    }
}
