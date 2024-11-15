<?php

namespace Database\Seeders;

use App\Models\Convocatorias\Convocatoria;
use App\Models\Convocatorias\GrupoCriterios;
use App\Models\Convocatorias\CandidatoConvocatoria;
use App\Models\Convocatorias\ComiteCandidatoConvocatoria;
use App\Models\Usuarios\Docente;
use App\Models\Usuarios\Usuario;
use Illuminate\Database\Seeder;

class ProcesoConvocatoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Crear 20 grupos de criterios
        $gruposCriterios = GrupoCriterios::factory(10)->create();

        // Crear 15 convocatorias
        $convocatorias = Convocatoria::factory(15)->create();

        // Asignar entre 1 y 3 grupos de criterios a cada convocatoria
        foreach ($convocatorias as $convocatoria) {
            $gruposAsignados = $gruposCriterios->random(rand(1, 3)); // Asigna entre 1 y 3 grupos
            $convocatoria->gruposCriterios()->attach($gruposAsignados);
        }

        // Crear entre 3 y 8 docentes para cada convocatoria
        foreach ($convocatorias as $convocatoria) {
            $docentes = Docente::factory(rand(3, 8))->create(); // Crear entre 3 y 8 docentes
            foreach ($docentes as $docente) {
                $convocatoria->comite()->attach($docente); // Asignar cada docente a la convocatoria
            }
        }

        // Crear CandidatoConvocatoria para cada convocatoria (mínimo 5 por convocatoria)
        foreach ($convocatorias as $convocatoria) {
            $usuarios = Usuario::factory(rand(5, 10))->create(); // Crear entre 5 y 10 candidatos

            foreach ($usuarios as $usuario) {
                CandidatoConvocatoria::create([
                    'convocatoria_id' => $convocatoria->id,
                    'candidato_id' => $usuario->id,
                    'estadoFinal' => 'pendiente cv',
                    'urlCV' => 'http://example.com/cv.pdf',
                ]);
            }
        }

        // Crear ComiteCandidatoConvocatoria para cada candidato y convocatoria
        foreach ($convocatorias as $convocatoria) {
            $candidatos = $convocatoria->candidatos; // Obtén todos los candidatos asignados a esta convocatoria

            // Obtener todos los docentes asignados a esta convocatoria
            $docentes = $convocatoria->comite;

            foreach ($candidatos as $candidato) {
                foreach ($docentes as $docente) {
                    ComiteCandidatoConvocatoria::create([
                        'docente_id' => $docente->id,
                        'candidato_id' => $candidato->id,
                        'convocatoria_id' => $convocatoria->id,
                        'estadoFinal' => 'pendiente cv',
                    ]);
                }
            }
        }
    }
}