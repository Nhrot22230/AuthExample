<?php

namespace Database\Factories;

use App\Models\EstudianteRiesgo;
use Illuminate\Database\Eloquent\Factories\Factory;

class EstudianteRiesgoFactory extends Factory
{
    protected $model = EstudianteRiesgo::class;

    public function definition()
    {
        return [
            'codigo_estudiante' => $this->faker->unique()->numberBetween(10000000, 99999999),
            'codigo_curso' => $this->faker->numberBetween(1, 10),
            'codigo_docente' => $this->faker->numberBetween(1, 10),
            'horario' => $this->faker->time(),
            'codigo_especialidad' => $this->faker->numberBetween(1, 100),
            'riesgo' => $this->faker->word(),
            'estado' => $this->faker->randomElement(['Activo', 'Inactivo']),
            'fecha' => $this->faker->date(),
            'desempenho' => $this->faker->randomElement(['Bien', 'Regular', 'Mal']),
            'observaciones' => $this->faker->sentence(),
            'nombre' => $this->faker->name(),
            'ciclo' => $this->faker->numberBetween(1, 10),
        ];
    }
}
