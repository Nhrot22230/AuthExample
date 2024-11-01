<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Horario;

class HorariosSeeder extends Seeder
{
    public function run()
    {
        // Inserciones en la tabla horarios
        Horario::insert([
            ['curso_id' => 1, 'semestre_id' => 1, 'nombre' => 'Horario de ID Básico A', 'codigo' => 'H-101-A', 'vacantes' => 15, 'created_at' => now(), 'updated_at' => now()],
            ['curso_id' => 1, 'semestre_id' => 1, 'nombre' => 'Horario de ID Básico B', 'codigo' => 'H-101-B', 'vacantes' => 15, 'created_at' => now(), 'updated_at' => now()],
            ['curso_id' => 1, 'semestre_id' => 2, 'nombre' => 'Horario de ID Básico C', 'codigo' => 'H-101-C', 'vacantes' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['curso_id' => 2, 'semestre_id' => 1, 'nombre' => 'Horario de Desarrollo Web A', 'codigo' => 'H-202-A', 'vacantes' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['curso_id' => 2, 'semestre_id' => 1, 'nombre' => 'Horario de Desarrollo Web B', 'codigo' => 'H-202-B', 'vacantes' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['curso_id' => 2, 'semestre_id' => 2, 'nombre' => 'Horario de Desarrollo Web C', 'codigo' => 'H-202-C', 'vacantes' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['curso_id' => 3, 'semestre_id' => 2, 'nombre' => 'Horario de Ciencia de Datos A', 'codigo' => 'H-303-A', 'vacantes' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['curso_id' => 3, 'semestre_id' => 2, 'nombre' => 'Horario de Ciencia de Datos B', 'codigo' => 'H-303-B', 'vacantes' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['curso_id' => 3, 'semestre_id' => 2, 'nombre' => 'Horario de Ciencia de Datos C', 'codigo' => 'H-303-C', 'vacantes' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['curso_id' => 1, 'semestre_id' => 1, 'nombre' => 'Horario de ID Básico D', 'codigo' => 'H-101-D', 'vacantes' => 12, 'created_at' => now(), 'updated_at' => now()],
            ['curso_id' => 2, 'semestre_id' => 2, 'nombre' => 'Horario de Desarrollo Web D', 'codigo' => 'H-202-D', 'vacantes' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['curso_id' => 3, 'semestre_id' => 1, 'nombre' => 'Horario de Ciencia de Datos D', 'codigo' => 'H-303-D', 'vacantes' => 7, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}