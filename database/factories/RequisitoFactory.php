<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\PlanEstudio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Requisito>
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
        $random_plan_estudio = PlanEstudio::inRandomOrder()->first() ?? PlanEstudio::factory()->create();
        
        return [
            'plan_estudio_id' => $random_plan_estudio,
            'curso_id' => $random_plan_estudio->cursos()->inRandomOrder()->first() ?? Curso::factory(
                [
                    'especialidad_id' => $random_plan_estudio->especialidad_id,
                ]
            )->create(),
            'tipo' => $this->faker->randomElement(['llevado', 'simultaneo']),
            'curso_requisito_id' => $random_plan_estudio->cursos()->inRandomOrder()->first() ?? Curso::factory(
                [
                    'especialidad_id' => $random_plan_estudio->especialidad_id,
                ]
            )->create(),
            'notaMinima' => $this->faker->random_int(0, 11),
        ];
    }
}
