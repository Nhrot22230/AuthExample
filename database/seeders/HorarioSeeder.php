<?php

namespace Database\Seeders;

use App\Models\Encuestas\Encuesta;
use App\Models\Matricula\Horario;
use App\Models\Matricula\HorarioEstudiante;
use App\Models\Matricula\HorarioEstudianteJp;
use App\Models\Usuarios\Docente;
use App\Models\Usuarios\Estudiante;
use App\Models\Usuarios\JefePractica;
use App\Models\Usuarios\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class HorarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Horario::factory(20)->create();
    }
}
