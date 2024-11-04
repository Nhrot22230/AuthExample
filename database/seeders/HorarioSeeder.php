<?php

namespace Database\Seeders;

use App\Models\Horario;
use App\Models\Estudiante;
use App\Models\HorarioEstudiante;
use App\Models\HorarioEstudianteJp;
use App\Models\JefePractica;
use App\Models\Docente;
use App\Models\Usuario;
use App\Models\Encuesta;

use Illuminate\Support\Facades\DB;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HorarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Horario::factory(50)->create();

        $horarios = Horario::all();
        $estudiantes = Estudiante::all();

        $encuestas = Encuesta::all();

        foreach ($horarios as $horario) {
            $encuestaDocente = $encuestas->where('tipo_encuesta', 'docente')->random();
            $encuestaJefePractica = $encuestas->where('tipo_encuesta', 'jefe_practica')->random();
        
            DB::table('encuesta_horario')->insert([
                'encuesta_id' => $encuestaDocente->id,
                'horario_id' => $horario->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        
            DB::table('encuesta_horario')->insert([
                'encuesta_id' => $encuestaJefePractica->id,
                'horario_id' => $horario->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        

        foreach ($horarios as $horario) {
            $assignedEstudiantes = $estudiantes->random(5);

            foreach ($assignedEstudiantes as $estudiante) {
                HorarioEstudiante::create([
                    'estudiante_id' => $estudiante->id,
                    'horario_id' => $horario->id,
                    'encuestaDocente' => false,
                ]);
            }
        }
        $usuarios = Usuario::all();

        foreach ($horarios as $horario) {
            $numJps = rand(2, 4);

            $assignedUsuarios = $usuarios->random($numJps);

            foreach ($assignedUsuarios as $usuario) {
                JefePractica::create([
                    'usuario_id' => $usuario->id,
                    'horario_id' => $horario->id,
                ]);
            }
        }

        $horarioEstudiantes = HorarioEstudiante::all();

        foreach ($horarioEstudiantes as $horarioEstudiante) {
            $jefesPractica = JefePractica::where('horario_id', $horarioEstudiante->horario_id)->get();

            foreach ($jefesPractica as $jefePractica) {
                HorarioEstudianteJp::create([
                    'estudiante_horario_id' => $horarioEstudiante->id,
                    'jp_horario_id' => $jefePractica->id,
                    'encuestaJP' => false,
                ]);
            }
        }

        $docentes = Docente::all();

        foreach ($horarios as $horario) {
            $docente = $docentes->random();

            DB::table('docente_horario')->insert([
                'docente_id' => $docente->id,
                'horario_id' => $horario->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
