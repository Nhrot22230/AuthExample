<?php

namespace Database\Seeders;

use App\Models\Encuestas\Encuesta;
use Illuminate\Database\Seeder;

class EncuestaSeeder extends Seeder
{

    public function run(): void
    {
        Encuesta::factory(50)->create();
    }
}
