<?php

namespace Database\Factories;

use App\Models\EstudianteRiesgo\EstudianteRiesgo;
use App\Models\EstudianteRiesgo\InformeRiesgo;
use Illuminate\Database\Eloquent\Factories\Factory;

class InformeRiesgoFactory extends Factory
{
    protected $model = InformeRiesgo::class;

    public function definition()
    {
        return [
            'semana' => $this->faker->numberBetween(1, 10),
            'codigo_alumno_riesgo' => function () {
                return EstudianteRiesgo::factory()->create()->id; // Crear un estudiante al mismo tiempo
            },
            'fecha' => $this->faker->date(),
            'desempenho' => $this->faker->randomElement(['Mal', 'Regular', 'Bien']),
            'observaciones' => $this->faker->sentence(),
            'estado' => $this->faker->randomElement(['Respondida', 'Pendiente']),
            'nombre_profesor' => $this->faker->name(),
        ];
    }
}
