<?php

namespace Database\Seeders;

use App\Models\Universidad\Especialidad;
use App\Models\Universidad\PlanEstudio;
use App\Models\Universidad\Requisito;
use App\Models\Universidad\Semestre;
use Illuminate\Database\Seeder;

class PlanEstudioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $especialidad = Especialidad::factory()->create();

        $plan_estudio = PlanEstudio::factory()->create([
            'especialidad_id' => $especialidad->id,
        ]);

        Requisito::factory(20)->create([
            'plan_estudio_id' => $plan_estudio->id,
        ]);

        $semestres = Semestre::inRandomOrder()->limit(random_int(1, 4))->get();
        if ($semestres->isEmpty()) {
            $semestres = Semestre::factory(4)->create();
        }
        $plan_estudio->semestres()->attach($semestres);
    }
}
