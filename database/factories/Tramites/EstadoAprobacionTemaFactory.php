<?php

namespace Database\Factories\Tramites;

use App\Models\Tramites\EstadoAprobacionTema;
use App\Models\Tramites\ProcesoAprobacionTema;
use App\Models\Tramites\TemaDeTesis;
use App\Models\Universidad\Area;
use App\Models\Universidad\Especialidad;
use App\Models\Usuarios\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class EstadoAprobacionTemaFactory extends Factory {
    protected $model = EstadoAprobacionTema::class;

    public function definition(): array
    {
        $estado = $this->faker->randomElement(['aprobado', 'pendiente', 'rechazado']);
        return [
            'proceso_aprobacion_id' => ProcesoAprobacionTema::factory(),
            'usuario_id' => Usuario::factory()->create(),
            'estado' => $estado,
            'fecha_decision' => $estado !== 'pendiente' ? $this->faker->date() : null,
            'comentarios' => $estado !== 'pendiente' ? $this->faker->text() : null,
        ];
    }
}
