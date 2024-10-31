<?php

namespace Database\Seeders;

use App\Models\Encuesta;
use App\Models\Horario;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EncuestaSeeder extends Seeder
{

    public function run(): void
    {
        //Encuesta::factory(5)->create();
        $horarios = Horario::all();
        $encuestas = Encuesta::all();

        // Asociar encuestas a horarios en la tabla pivote
        foreach ($horarios as $horario) {
            // Asociar entre 1 y 3 encuestas aleatorias por horario
            $encuestasAleatorias = $encuestas->random(rand(1, 3));

            foreach ($encuestasAleatorias as $encuesta) {
                DB::table('encuesta_horario')->insert([
                    'encuesta_id' => $encuesta->id,
                    'horario_id' => $horario->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
