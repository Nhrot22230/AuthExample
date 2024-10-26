<?php

namespace Database\Factories;

use App\Models\Especialidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Curso>
 */
class CursoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $random_especialidad = Especialidad::inRandomOrder()->first();
        
        // Definición de los grupos de palabras
        $grupo1 = ['Ingeniería', 'Historia', 'Ciencias', 'Psicología', 'Economía', 'Medicina', 'Arquitectura', 'Filosofía', 'Derecho', 'Matemáticas'];
        $grupo2 = ['Aplicada', 'Industrial', 'Clínica', 'Ambiental', 'Social', 'Digital', 'Avanzada', 'Computacional', 'Educativa', 'Política'];
        $grupo3 = ['del Arte', 'de Datos', 'de la Salud', 'en Redes', 'del Siglo XXI', 'en Telecomunicaciones', 'de la Inteligencia Artificial', 'del Comportamiento', 'del Medio Ambiente', 'en el siglo XIX'];
        $grupo4 = ['Básico', 'Avanzado', 'Profesional', 'Intermedio', 'Contemporáneo', 'Clásico', 'Moderno', 'Experimental', 'Aplicado', 'Abierto'];
        
        // Generación del nombre del curso combinando palabras de cada grupo
        $nombre_curso = $this->faker->randomElement($grupo1) . ' ' .
                        $this->faker->randomElement($grupo2) . ' ' .
                        $this->faker->randomElement($grupo3) . ' ' .
                        $this->faker->randomElement($grupo4);

        return [
            'especialidad_id' => $random_especialidad->id ?? Especialidad::factory(),
            'cod_curso' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'nombre' => $nombre_curso,
            'creditos' => $this->faker->numberBetween(1, 10),
            'estado' => $this->faker->randomElement(['activo', 'inactivo']),
        ];
    }
}
