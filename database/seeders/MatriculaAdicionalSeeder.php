<?php

namespace Database\Seeders;

use App\Models\Solicitudes\MatriculaAdicional;
use Illuminate\Database\Seeder;

class MatriculaAdicionalSeeder extends Seeder
{
    public function run(): void
    {
        MatriculaAdicional::factory(10)->create();
    }
}
