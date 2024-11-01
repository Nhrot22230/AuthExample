<?php

namespace Database\Factories;

use App\Models\Especialidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Area>
 */
class AreaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $random_especialidad = Especialidad::inRandomOrder()->first() ?? Especialidad::factory()->create();

        $grupo_1 = ['Gestión', 'Desarrollo', 'Investigación', 'Estudios', 'Administración', 'Producción'];
        $grupo_2 = ['Ambiental', 'Social', 'Tecnológica', 'Científica', 'Educativa', 'Salud'];
        $grupo_3 = ['Avanzada', 'Aplicada', 'Sostenible', 'Experimental', 'Interdisciplinaria', 'Teórica'];

        $nombre_area = $this->faker->randomElement($grupo_1) . ' ' .
                       $this->faker->randomElement($grupo_2) . ' ' .
                       $this->faker->randomElement($grupo_3);

        $descripciones = [
            "Área dedicada a la {$this->faker->randomElement($grupo_1)} en el ámbito de {$this->faker->randomElement($grupo_2)}.",
            "Responsable de actividades de {$this->faker->randomElement($grupo_1)} y {$this->faker->randomElement($grupo_2)} en especialidad.",
            "Enfocada en la {$this->faker->randomElement($grupo_1)} para el avance de la {$this->faker->randomElement($grupo_2)} {$this->faker->randomElement($grupo_3)}.",
            "Promueve la {$this->faker->randomElement($grupo_1)} a través de prácticas {$this->faker->randomElement($grupo_3)} en el área de {$this->faker->randomElement($grupo_2)}.",
            "Contribuye al desarrollo de competencias en {$this->faker->randomElement($grupo_1)} y {$this->faker->randomElement($grupo_2)}.",
        ];

        $descripcion = $this->faker->randomElement($descripciones);

        return [
            'nombre' => $nombre_area,
            'descripcion' => $descripcion,
            'especialidad_id' => $random_especialidad->id,
        ];
    }
}
