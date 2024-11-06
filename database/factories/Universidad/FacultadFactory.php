<?php

namespace Database\Factories\Universidad;

use App\Models\Universidad\Facultad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Facultad>
 */
class FacultadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $grupo_1 = ['Facultad de', 'Escuela de', 'Colegio de', 'Instituto de', 'Centro de'];
        $grupo_2 = ['Ciencias', 'Ingeniería', 'Humanidades', 'Salud', 'Educación', 'Administración',
        'Arquitectura', 'Derecho', 'Agronomía', 'Veterinaria', 'Economía', 'Comunicaciones', 'Arte', 'Música',
        'Diseño', 'Turismo', 'Deportes', 'Ciencias de la Computación', 'Ciencias de la Información',
        'Ciencias de la Comunicación', 'Ciencias de la Salud', 'Ciencias de la Educación', 'Ciencias de la Administración',
        'Ciencias de la Ingeniería', 'Ciencias de la Arquitectura', 'Ciencias de la Economía', 'Ciencias de la Agronomía',
        'Ciencias de la Veterinaria', 'Ciencias de la Comunicación', 'Ciencias de la Música', 'Ciencias de la Danza' ];
        $grupo_3 = ['Aplicadas', 'Sociales', 'Ambientales', 'Tecnológicas', 'Interdisciplinarias'];

        $nombre_facultad = $this->faker->randomElement($grupo_1) . ' ' .
                           $this->faker->randomElement($grupo_2) . ' ' .
                           $this->faker->randomElement($grupo_3);

        $abreviatura = strtoupper(preg_replace('/[^A-Z]/', '', $nombre_facultad));

        return [
            'nombre' => $nombre_facultad,
            'abreviatura' => $abreviatura,
            'anexo' => $this->faker->randomNumber(5)
        ];
    }
}
