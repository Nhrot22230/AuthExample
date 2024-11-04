<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MatriculaAdicionalSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\MatriculaAdicional::factory()->count(1000)->create();
    }
}
