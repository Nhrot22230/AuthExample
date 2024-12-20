<?php

namespace Database\Seeders;

use App\Models\Encuestas\Encuesta;
use App\Models\Encuestas\Pregunta;
use Illuminate\Database\Seeder;

class EncuestaPreguntaSeeder extends Seeder
{

    public function run(): void
    {
        $encuestas = Encuesta::all();
        $preguntas = Pregunta::all();

        foreach ($encuestas as $encuesta) {
            $randomPreguntas = $preguntas->random(rand(2, 10))->pluck('id')->toArray();
            $encuesta->pregunta()->attach($randomPreguntas);
        }
    }
}
