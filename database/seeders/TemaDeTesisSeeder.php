<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\TemaDeTesis;
use Illuminate\Database\Seeder;

class TemaDeTesisSeeder extends Seeder
{
    public function run(): void
    {

        $cantidad = 400;
        TemaDeTesis::factory($cantidad)->create(); 
    }
}
