<?php

namespace Database\Seeders;

use App\Models\Encuesta;
use App\Models\Horario;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EncuestaSeeder extends Seeder
{

    public function run(): void
    {
        //Encuesta::factory(5)->create();
        Encuesta::create([
            'fecha_inicio' => Carbon::create(2024, 1, 15),
            'fecha_fin' => Carbon::create(2024, 2, 15),
            'nombre_encuesta' => '2024-0',
            'tipo_encuesta' => 'docente',
            'disponible' => false,
            'especialidad_id' => 1,
        ]);

        Encuesta::create([
            'fecha_inicio' => Carbon::create(2024, 1, 10),
            'fecha_fin' => Carbon::create(2024, 2, 10),
            'nombre_encuesta' => '2024-0',
            'tipo_encuesta' => 'jefe_practica',
            'disponible' => false,
            'especialidad_id' => 1,
        ]);

        Encuesta::create([
            'fecha_inicio' => Carbon::create(2024, 4, 15),
            'fecha_fin' => Carbon::create(2024, 5, 15),
            'nombre_encuesta' => '2024-1',
            'tipo_encuesta' => 'docente',
            'disponible' => true,
            'especialidad_id' => 1,
        ]);

        Encuesta::create([
            'fecha_inicio' => Carbon::create(2024, 4, 10),
            'fecha_fin' => Carbon::create(2024, 5, 10),
            'nombre_encuesta' => '2024-1',
            'tipo_encuesta' => 'jefe_practica',
            'disponible' => true,
            'especialidad_id' => 1,
        ]);

        Encuesta::create([
            'fecha_inicio' => Carbon::create(2024, 1, 15),
            'fecha_fin' => Carbon::create(2024, 2, 15),
            'nombre_encuesta' => '2024-0',
            'tipo_encuesta' => 'docente',
            'disponible' => false,
            'especialidad_id' => 2,
        ]);

        Encuesta::create([
            'fecha_inicio' => Carbon::create(2024, 1, 10),
            'fecha_fin' => Carbon::create(2024, 2, 10),
            'nombre_encuesta' => '2024-0',
            'tipo_encuesta' => 'jefe_practica',
            'disponible' => false,
            'especialidad_id' => 2,
        ]);

        Encuesta::create([
            'fecha_inicio' => Carbon::create(2024, 5, 15),
            'fecha_fin' => Carbon::create(2024, 6, 15),
            'nombre_encuesta' => '2024-1',
            'tipo_encuesta' => 'docente',
            'disponible' => true,
            'especialidad_id' => 2,
        ]);

        Encuesta::create([
            'fecha_inicio' => Carbon::create(2024, 5, 10),
            'fecha_fin' => Carbon::create(2024, 6, 10),
            'nombre_encuesta' => '2024-1',
            'tipo_encuesta' => 'jefe_practica',
            'disponible' => true,
            'especialidad_id' => 2,
        ]);

    }
}
