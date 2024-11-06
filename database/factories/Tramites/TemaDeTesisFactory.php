<?php

namespace Database\Factories\Tramites;

use App\Models\Tramites\TemaDeTesis;
use App\Models\Universidad\Especialidad;
use App\Models\Usuarios\Docente;
use App\Models\Usuarios\Estudiante;
use Illuminate\Database\Eloquent\Factories\Factory;

class TemaDeTesisFactory extends Factory
{
    protected $model = TemaDeTesis::class;

    public function definition(): array
    {
        $especialidadId = Especialidad::where('facultad_id', 5)->inRandomOrder()->first()->id;

        // Obtiene un estado aleatorio
        $estado = $this->faker->randomElement(['aprobado', 'pendiente', 'desaprobado']);

        // Obtiene un estado de jurado aleatorio
        if ($estado == 'aprobado') {
            $estadoJurado = $this->faker->randomElement(['enviado', 'no enviado', 'aprobado', 'pendiente', 'desaprobado', 'vencido']);
        } else {
            $estadoJurado = 'no enviado';
        }

        $comentarios = $estadoJurado == 'desaprobado' ? $this->faker->paragraph() : null;

        return [
            'titulo' => $this->faker->sentence(),
            'resumen' => $this->faker->paragraph(),
            'documento' => null,  // Documento vacío
            'estado' => $estado,
            'estado_jurado' => $estadoJurado,
            'fecha_enviado' => $this->faker->date(),
            'especialidad_id' => $especialidadId,
            'comentarios' => $comentarios,
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
