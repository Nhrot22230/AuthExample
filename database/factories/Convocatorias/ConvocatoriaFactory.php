<?php

namespace Database\Factories\Convocatorias;

use App\Models\Convocatorias\Convocatoria;
use App\Models\Universidad\Seccion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AppModelsConvocatoriasConvocatoria>
 */
class ConvocatoriaFactory extends Factory
{
    protected $model = Convocatoria::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->word(),
            'descripcion' => $this->faker->sentence(),
            'fechaEntrevista' => $this->faker->dateTime(),
            'fechaInicio' => $this->faker->dateTime(),
            'fechaFin' => $this->faker->dateTime(),
            'estado' => $this->faker->randomElement(['abierta', 'cerrada', 'cancelada']),
            'seccion_id' => Seccion::factory()->create()->id,
        ];
    }
}
