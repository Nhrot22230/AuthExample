<?php

namespace Database\Factories\Convocatorias;

use App\Models\Convocatorias\CandidatoConvocatoria;
use App\Models\Convocatorias\Convocatoria;
use App\Models\Usuarios\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AppModelsConvocatoriasCandidatoConvocatoria>
 */
class CandidatoConvocatoriaFactory extends Factory
{
    protected $model = CandidatoConvocatoria::class;

    public function definition()
    {
        return [
            'convocatoria_id' => Convocatoria::factory()->create()->id,
            'candidato_id' => Usuario::factory()->create()->id,
            'estadoFinal' => $this->faker->randomElement([
                'pendiente cv', 
                'desaprobado cv', 
                'aprobado cv', 
                'culminado entrevista', 
                'desaprobado entrevista', 
                'aprobado entrevista'
            ]),
            'urlCV' => $this->faker->url(),
        ];
    }
}
