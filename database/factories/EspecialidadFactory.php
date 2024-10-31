<?php

namespace Database\Factories;

use App\Models\Facultad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Especialidad>
 */
class EspecialidadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $random_facultad = Facultad::inRandomOrder()->first() ?? Facultad::factory()->create();

        // Grupos de palabras para generar nombres de especialidades
        $grupo_1 = ['Ingeniería', 'Licenciatura', 'Tecnología', 'Ciencia', 'Estudios'];
        $grupo_2 = ['Ambiental', 'Computacional', 'Médica', 'Educativa', 'Administrativa', 'Social'];
        $grupo_3 = ['Aplicada', 'Experimental', 'Avanzada', 'Interdisciplinaria', 'Integral'];

        $nombre_especialidad = $this->faker->randomElement($grupo_1) . ' en ' .
                               $this->faker->randomElement($grupo_2) . ' ' .
                               $this->faker->randomElement($grupo_3);

        // Plantillas de descripciones
        $descripciones = [
            "Especialidad enfocada en la {$this->faker->randomElement($grupo_1)} para el desarrollo de competencias en {$this->faker->randomElement($grupo_2)}.",
            "Área de estudio dedicada a la formación en {$this->faker->randomElement($grupo_1)} y {$this->faker->randomElement($grupo_2)} aplicada.",
            "Programa orientado a la investigación y desarrollo en el ámbito de {$this->faker->randomElement($grupo_2)}.",
            "Especialización en {$this->faker->randomElement($grupo_1)}, con un enfoque en la aplicación {$this->faker->randomElement($grupo_3)} en el área de {$this->faker->randomElement($grupo_2)}.",
        ];

        $descripcion = $this->faker->randomElement($descripciones);

        return [
            'nombre' => $nombre_especialidad,
            'descripcion' => $descripcion,
            'facultad_id' => $random_facultad->id,
        ];
    }
}
