<?php

namespace Database\Seeders;

use App\Models\Horario;
use App\Models\Estudiante;
use App\Models\HorarioEstudiante;
use App\Models\HorarioEstudianteJp;
use App\Models\JefePractica;
use App\Models\Usuario;

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

        foreach ($horarios as $horario) {
            $assignedEstudiantes = $estudiantes->random(5);

            foreach ($assignedEstudiantes as $estudiante) {
                HorarioEstudiante::create([
                    'estudiante_id' => $estudiante->id,
                    'horario_id' => $horario->id,
                    'encuestaDocente' => true,
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
                    'encuestaJP' => true,
                ]);
            }
        }
    }
}
