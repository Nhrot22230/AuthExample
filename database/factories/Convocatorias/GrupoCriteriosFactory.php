<?php

namespace Database\Factories\Convocatorias;

use App\Models\Convocatorias\GrupoCriterios;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AppModelsConvocatoriasGrupoCriterios>
 */
class GrupoCriteriosFactory extends Factory
{
    protected $model = GrupoCriterios::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->word(),
            'obligatorio' => $this->faker->boolean(),
            'descripcion' => $this->faker->sentence(),
        ];
    }
}
