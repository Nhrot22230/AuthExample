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
        $estado = $this->faker->randomElement(['aprobado', 'pendiente', 'desaprobado']);
        $estadoJurado = $estado == 'aprobado' ?
            $this->faker->randomElement(['enviado', 'no enviado', 'aprobado', 'pendiente', 'desaprobado', 'vencido']) :
            $estadoJurado = 'no enviado';


        $comentarios = $estadoJurado == 'desaprobado' ? $this->faker->paragraph() : null;

        return [
            'titulo' => $this->faker->sentence(),
            'resumen' => $this->faker->paragraph(),
            'documento' => null,
            'estado' => $estado,
            'estado_jurado' => $estadoJurado,
            'fecha_enviado' => $this->faker->date(),
            'especialidad_id' => Especialidad::factory(),
            'comentarios' => $comentarios,
        ];
    }
}
