<?php

namespace Database\Factories\Convocatorias;

use App\Models\Convocatorias\ComiteCandidatoConvocatoria;
use App\Models\Convocatorias\Convocatoria;
use App\Models\Usuarios\Docente;
use App\Models\Usuarios\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AppModelsConvocatoriasComiteCandidatoConvocatoria>
 */
class ComiteCandidatoConvocatoriaFactory extends Factory
{
    protected $model = ComiteCandidatoConvocatoria::class;

    public function definition()
    {
        return [
            'docente_id' => Docente::factory()->create()->id,
            'candidato_id' => Usuario::factory()->create()->id,
            'convocatoria_id' => Convocatoria::factory()->create()->id,
            'estadoFinal' => $this->faker->randomElement([
                'pendiente cv',
                'desaprobado cv',
                'aprobado cv',
                'culminado entrevista',
                'desaprobado entrevista',
                'aprobado entrevista',
            ]),
        ];
    }
}
