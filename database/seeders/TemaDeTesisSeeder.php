<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\TemaDeTesis;
use Illuminate\Database\Seeder;

class TemaDeTesisSeeder extends Seeder
{
    public function run(): void
    {

        $cantidad = 10;
        TemaDeTesis::factory($cantidad)->create(
            [
                'estado' => 'aprobado',
            ]
        );

        TemaDeTesis::factory($cantidad)->create(
            [
                'estado' => 'aprobado',
                'estado_jurado' => 'aprobado',

            ]
        );

        TemaDeTesis::factory($cantidad)->create(
            [
                'estado' => 'aprobado',
                'estado_jurado' => 'desaprobado',
            ]
        );

        TemaDeTesis::factory($cantidad)->create(
            [
                'estado' => 'aprobado',
                'estado_jurado' => 'pendiente',
            ]
        ); 
    }
}
