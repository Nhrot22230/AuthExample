<?php

namespace Database\Factories;

use App\Models\Estudiante;
use App\Models\Horario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HorarioEstudiante>
 */
class HorarioEstudianteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     * 
     * @var string
     */
    protected $model = \App\Models\HorarioEstudiante::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'estudiante_id' => Estudiante::inRandomOrder()->first() ?? Estudiante::factory(),
            'horario_id' => Horario::inRandomOrder()->first() ?? Horario::factory(),
            'encuestaDocente' => $this->faker->boolean(),
        ];
    }
}
