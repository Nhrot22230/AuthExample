<?php

namespace Database\Factories;

use App\Models\TemaDeTesis;
use App\Models\Especialidad;
use App\Models\Docente;
use App\Models\Estudiante;
use Illuminate\Database\Eloquent\Factories\Factory;

class TemaDeTesisFactory extends Factory
{
    protected $model = TemaDeTesis::class;

    public function definition(): array
    {
        // Obtiene una especialidad aleatoria o usa el ID 28 (Ingeniería de Sistemas)
        $especialidadId = random_int(0, 1) ? 28 : Especialidad::inRandomOrder()->first()->id;

        // Obtiene un estado aleatorio
        $estado = $this->faker->randomElement(['aprobado', 'pendiente', 'desaprobado']);

        // Obtiene un estado de jurado aleatorio
        $estadoJurado = $this->faker->randomElement(['enviado', 'no enviado', 'aprobado', 'pendiente', 'desaprobado', 'vencido']);


        return [
            'titulo' => $this->faker->sentence(),
            'resumen' => $this->faker->paragraph(),
            'documento' => null,  // Documento vacío
            'estado' => $estado,
            'estado_jurado' => $estadoJurado,
            'fecha_enviado' => $this->faker->date(),
            'especialidad_id' => $especialidadId,
        ];
    }

    // Método personalizado para asignar estudiantes y docentes a un tema de tesis
    public function configure()
    {
        return $this->afterCreating(function (TemaDeTesis $tema) {
            // Filtra estudiantes que pertenezcan a la misma especialidad del tema de tesis
            $estudiantes = Estudiante::where('especialidad_id', $tema->especialidad_id)
                ->inRandomOrder()
                ->take(random_int(1, 3))
                ->pluck('id');

            // Filtra docentes que pertenezcan a la misma especialidad para asesores
            $asesores = Docente::where('especialidad_id', $tema->especialidad_id)
                ->inRandomOrder()
                ->take(random_int(1, 2))
                ->pluck('id');

            $jurados = [];
            if (in_array($tema->estado_jurado, ['aprobado', 'desaprobado', 'pendiente'])) {
                $jurados = Docente::where('especialidad_id', $tema->especialidad_id)
                    ->inRandomOrder()
                    ->take(3)
                    ->pluck('id');
            }

            // Asigna estudiantes, asesores y jurados al tema de tesis
            $tema->estudiantes()->attach($estudiantes);
            $tema->asesores()->attach($asesores);
            $tema->jurados()->attach($jurados);
        });
    }
}
