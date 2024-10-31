<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\TemaDeTesis;
use Illuminate\Database\Seeder;

class TemaDeTesisSeeder extends Seeder
{
    public function run(): void
    {
        TemaDeTesis::factory()->count(20)->create(); // Genera 20 temas de tesis
    }
}
