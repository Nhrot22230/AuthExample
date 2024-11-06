<?php

namespace Database\Seeders;

use App\Models\Tramites\TemaDeTesis;
use Illuminate\Database\Seeder;

class TemaDeTesisSeeder extends Seeder
{
    public function run(): void
    {

        $cantidad = 400;
        TemaDeTesis::factory($cantidad)->create();
    }
}
