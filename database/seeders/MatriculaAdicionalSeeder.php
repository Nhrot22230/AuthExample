<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MatriculaAdicionalSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Solicitudes\MatriculaAdicional::factory()->count(1000)->create();
    }
}
