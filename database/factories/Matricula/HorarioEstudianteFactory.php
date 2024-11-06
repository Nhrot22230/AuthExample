<?php

namespace Database\Factories\Matricula;

use App\Models\Matricula\Horario;
use App\Models\Matricula\HorarioEstudiante;
use App\Models\Usuarios\Estudiante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HorarioEstudiante>
 */
class HorarioEstudianteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = HorarioEstudiante::class;

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
