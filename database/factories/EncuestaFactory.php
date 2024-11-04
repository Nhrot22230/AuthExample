<?php

namespace Database\Factories;

use App\Models\Especialidad;
use Illuminate\Database\Eloquent\Factories\Factory;


class EncuestaFactory extends Factory
{


    public function definition(): array

    {
        $especialidadRandom = Especialidad::where('facultad_id', 5)->inRandomOrder()->first();
        return [
            'fecha_inicio' => $this->faker->date(),
            'fecha_fin' => $this->faker->date(),
            'nombre_encuesta' => $this->faker->sentence(3),
            'tipo_encuesta' => $this->faker->randomElement(['docente', 'jefe_practica']),
            'disponible' => $this->faker->boolean(),
            'especialidad_id' => $especialidadRandom->id,
        ];
    }
}
