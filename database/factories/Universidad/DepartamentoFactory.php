<?php

namespace Database\Factories;

use App\Models\Universidad\Facultad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Universidad\Departamento>
 */
class DepartamentoFactory extends Factory
{
    public function definition(): array
    {
        $random_facultad = Facultad::inRandomOrder()->first() ?? Facultad::factory()->create();

        $grupo_1 = ['Ciencias', 'Humanidades', 'Tecnología', 'Estudios', 'Educación', 'Administración'];
        $grupo_2 = ['Aplicadas', 'Básicas', 'Avanzadas', 'Sociales', 'Interdisciplinarios'];
        $grupo_3 = ['y Humanísticas', 'y Ambientales', 'y de la Salud', 'y Administrativas', 'y Computacionales'];
        $grupo_4 = ['Avanzados', 'Aplicados', 'Interdisciplinarios', 'Integrales', 'Especializados'];

        $nombre_departamento = 'Departamento de ' .
                                $this->faker->randomElement($grupo_1) . ' ' .
                                $this->faker->randomElement($grupo_2) . ' ' .
                                $this->faker->randomElement($grupo_3) . ' ' .
                                $this->faker->randomElement($grupo_4);

        $descripciones = [
            "Encargado de promover el desarrollo de la {$this->faker->randomElement($grupo_1)} en la facultad.",
            "Responsable de coordinar actividades relacionadas con {$this->faker->randomElement($grupo_1)} {$this->faker->randomElement($grupo_2)}.",
            "Dedicado a la investigación y formación en áreas de {$this->faker->randomElement($grupo_1)} y {$this->faker->randomElement($grupo_3)}.",
            "Facilita programas avanzados en {$this->faker->randomElement($grupo_1)} {$this->faker->randomElement($grupo_3)} y {$this->faker->randomElement($grupo_4)}.",
            "Enfocado en el fortalecimiento de los conocimientos en {$this->faker->randomElement($grupo_1)} y en el desarrollo de {$this->faker->randomElement($grupo_3)}.",
        ];

        $descripcion = $this->faker->randomElement($descripciones);

        return [
            'nombre' => $nombre_departamento,
            'descripcion' => $descripcion,
            'facultad_id' => $random_facultad->id,
        ];
    }
}
