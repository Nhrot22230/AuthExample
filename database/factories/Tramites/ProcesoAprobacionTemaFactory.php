<?php

namespace Database\Factories\Tramites;

use App\Models\Tramites\ProcesoAprobacionTema;
use App\Models\Tramites\TemaDeTesis;
use App\Models\Universidad\Area;
use App\Models\Universidad\Especialidad;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProcesoAprobacionTemaFactory extends Factory {
    protected $model = ProcesoAprobacionTema::class;

    public function definition(): array
    {
        $estado_proceso = $this->faker->randomElement(['aprobado', 'pendiente', 'rechazado']);
        return [
            'tema_tesis_id' => TemaDeTesis::factory(),
            'fecha_inicio' => $this->faker->date(),
            'estado_proceso' => $estado_proceso,
            'fecha_fin' => $estado_proceso !== 'pendiente' ? $this->faker->date() : null,
        ];
    }
}
