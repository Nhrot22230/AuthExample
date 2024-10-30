<?php

namespace Database\Seeders;

use App\Models\Encuesta;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EncuestaSeeder extends Seeder
{

    public function run(): void
    {
        Encuesta::factory(5)->create();
    }
}
