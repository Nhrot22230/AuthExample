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
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $random_especialidad = Especialidad::where('facultad_id', 5)->inRandomOrder()->first() 
                             ?? Especialidad::factory()->create(['facultad_id' => 5]);
        
        $grupo1 = ['Ingeniería', 'Historia', 'Ciencias', 'Psicología', 'Economía', 'Medicina', 'Arquitectura', 'Filosofía', 'Derecho', 'Matemáticas'];
        $grupo2 = ['Aplicada', 'Industrial', 'Clínica', 'Ambiental', 'Social', 'Digital', 'Avanzada', 'Computacional', 'Educativa', 'Política'];
        $grupo3 = ['del Arte', 'de Datos', 'de la Salud', 'en Redes', 'del Siglo XXI', 'en Telecomunicaciones', 'de la Inteligencia Artificial', 'del Comportamiento', 'del Medio Ambiente', 'en el siglo XIX'];
        $grupo4 = ['Básico', 'Avanzado', 'Profesional', 'Intermedio', 'Contemporáneo', 'Clásico', 'Moderno', 'Experimental', 'Aplicado', 'Abierto'];
        
        $nombre_curso = $this->faker->randomElement($grupo1) . ' ' .
                        $this->faker->randomElement($grupo2) . ' ' .
                        $this->faker->randomElement($grupo3) . ' ' .
                        $this->faker->randomElement($grupo4);

        return [
            'especialidad_id' => $random_especialidad->id,
            'cod_curso' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'nombre' => $nombre_curso,
            'creditos' => $this->faker->randomElement([0, 1, 2, 3, 4, 5]) + $this->faker->randomElement([0, 0.25, 0.5, 0.75]),
            'estado' => $this->faker->randomElement(['activo', 'inactivo']),
            'ct' => $this->faker->randomFloat(2, 0, 20),
            'pa' => $this->faker->randomFloat(2, 0, 4),
            'pb' => $this->faker->randomFloat(2, 0, 4),
            'me' => $this->faker->randomElement([0, 1, 2, 3, 4, 5]),
        ];
    }

    /**
     * Define un estado personalizado para asociar cursos a una facultad específica.
     *
     * @param int $facultadId
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function paraFacultad(int $facultadId)
    {
        return $this->state(function (array $attributes) use ($facultadId) {
            $especialidad = Especialidad::where('facultad_id', $facultadId)->inRandomOrder()->first()
                ?? Especialidad::factory()->create(['facultad_id' => $facultadId]);
            
            return [
                'especialidad_id' => $especialidad->id,
            ];
        });
    }
}
