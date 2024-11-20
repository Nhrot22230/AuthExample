<?php

namespace Database\Factories\Tramites;

use App\Models\Tramites\TemaDeTesis;
use App\Models\Universidad\Area;
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
            'file_id' => null,
            'file_firmado_id' => null,
            'estado' => $estado,
            'estado_jurado' => $estadoJurado,
            'fecha_enviado' => Now(),
            'especialidad_id' => Especialidad::factory(),
            'comentarios' => $comentarios,
            'area_id' => Area::factory(),
            'tema_original_id' => null
        ];
    }
}
