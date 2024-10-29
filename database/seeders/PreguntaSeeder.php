<?php

namespace Database\Seeders;

use App\Models\Encuesta;
use App\Models\Pregunta;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Seeder;

class PreguntaSeeder extends Seeder
{

    public function run(): void
    {
        Pregunta::factory(10)->create();


    }
}
