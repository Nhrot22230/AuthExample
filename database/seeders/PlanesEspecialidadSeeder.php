<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Universidad\Especialidad;
use App\Models\Universidad\PlanEstudio;
use App\Models\Universidad\Curso;
use App\Models\Universidad\Semestre;
use App\Models\Universidad\Requisito;

class PlanesEspecialidadSeeder extends Seeder
{
    public function run()
    {
        // Obtener todas las especialidades existentes en la base de datos
        $especialidades = Especialidad::all();

        if ($especialidades->isEmpty()) {
            $this->command->info('No hay especialidades en la base de datos.');
            return;
        }

        foreach ($especialidades as $especialidad) {
            $this->command->info("Creando plan de estudios para la especialidad: {$especialidad->nombre}");

            // Crear un plan de estudios para la especialidad
            $planEstudio = PlanEstudio::factory()->create([
                'especialidad_id' => $especialidad->id,
                'estado' => 'activo',
            ]);

            // Crear cursos asociados al plan de estudios
            $cursos = Curso::factory(10)->create([
                'especialidad_id' => $especialidad->id, // Asociar cursos con la especialidad
            ]);

            // Asociar los cursos al plan de estudios con niveles y créditos
            foreach ($cursos as $index => $curso) {
                $planEstudio->cursos()->attach($curso->id, [
                    'nivel' => $index + 1,
                    'creditosReq' => random_int(3, 6),
                ]);
            }

            // Crear requisitos para los cursos
            foreach ($cursos as $curso) {
                // Escoge un curso aleatorio como requisito (evitando que sea el mismo)
                $cursoRequisito = $cursos->where('id', '!=', $curso->id)->random();

                Requisito::create([
                    'curso_id' => $curso->id,
                    'plan_estudio_id' => $planEstudio->id,
                    'curso_requisito_id' => $cursoRequisito->id,
                    'tipo' => ['simultaneo', 'llevado'][array_rand(['simultaneo', 'llevado'])],
                    'notaMinima' => random_int(10, 15),
                ]);
            }

            // Asociar el plan de estudios con semestres
            $semestres = Semestre::inRandomOrder()->limit(4)->get();
            if ($semestres->isEmpty()) {
                $semestres = Semestre::factory(4)->create();
            }
            $planEstudio->semestres()->attach($semestres);

            $this->command->info("Plan de estudios creado para la especialidad: {$especialidad->nombre}");
        }
    }
}
