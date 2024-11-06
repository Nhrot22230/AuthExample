<?php

namespace Database\Seeders;

use App\Models\Universidad\Curso;
use App\Models\Universidad\Especialidad;
use App\Models\Universidad\PlanEstudio;
use App\Models\Universidad\Semestre;
use Illuminate\Database\Seeder;

class PlanEstudioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $especialidad = Especialidad::inRandomOrder()->first();

        $plan_estudio = PlanEstudio::factory()->create([
            'especialidad_id' => $especialidad->id,
        ]);

        // Obtener los cursos asociados a la especialidad
        $cursos = $especialidad->cursos;

        // Si hay menos de 20 cursos, crear los faltantes
        if ($cursos->count() < 20) {
            $cursosFaltantes = Curso::factory(20 - $cursos->count())->create([
                'especialidad_id' => $especialidad->id
            ]);
            $cursos = $cursos->merge($cursosFaltantes);
        }

        // Seleccionar 20 cursos aleatorios
        $cursosAleatorios = $cursos->random(min(20, $cursos->count()));

        foreach ($cursosAleatorios as $curso) {
            $plan_estudio->cursos()->attach($curso, [
                'nivel' => random_int(0, 10),
                'creditosReq' => random_int(1, 20),
            ]);
        }

        // Obtener o crear semestres
        $semestres = Semestre::inRandomOrder()->limit(random_int(1, 4))->get();
        if ($semestres->isEmpty()) {
            $semestres = Semestre::factory(4)->create();
        }
        $plan_estudio->semestres()->attach($semestres);
    }
}
