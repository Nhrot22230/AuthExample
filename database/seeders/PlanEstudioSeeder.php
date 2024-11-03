<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\Especialidad;
use App\Models\PlanEstudio;
use App\Models\Semestre;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        
        $cursos = Curso::inRandomOrder()->limit(20)->get() ?? Curso::factory(20)->create();

        foreach ($cursos as $curso) {
            $plan_estudio->cursos()->attach($curso, [
                'nivel' => random_int(0, 10),
                'creditosReq' => random_int(1, 20),
            ]);
        }

        $semestres = Semestre::inRandomOrder()->limit(random_int(1,4))->get() ?? Semestre::factory(4)->create();
        $plan_estudio->semestres()->attach($semestres);
    }
}
