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
        
        $cursos = $especialidad->cursos->random(20);

        if ($cursos->count() < 20) {
            $cursos = Curso::factory(20 - $cursos->count())->create(
                ['especialidad_id' => $especialidad->id]
            );
        }

        $rand_nivel = rand(0, $plan_estudio->cantidad_semestres);
        $plan_estudio->cursos()->attach($cursos, [ 
                'nivel' => $rand_nivel,
                'creditosReq' => rand(20 * max($rand_nivel - 1, 0), 20 * ($rand_nivel + 1)),          
        ]);

        $semestres = Semestre::random(8)->pluck('id') ?? Semestre::factory(8)->create()->pluck('id');
        $plan_estudio->semestres()->attach($semestres);
    }
}
