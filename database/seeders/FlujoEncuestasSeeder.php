<?php

namespace Database\Seeders;

use App\Models\Authorization\Role;
use App\Models\Authorization\RoleScopeUsuario;
use App\Models\Authorization\Scope;
use App\Models\Matricula\Horario;
use App\Models\Universidad\Curso;
use App\Models\Universidad\Especialidad;
use App\Models\Universidad\Facultad;
use App\Models\Universidad\Semestre;
use App\Models\Usuarios\Docente;
use App\Models\Usuarios\Estudiante;
use App\Models\Usuarios\Usuario;
use App\Models\Matricula\HorarioEstudiante;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FlujoEncuestasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $facultad = Facultad::inRandomOrder()->firstOrCreate();
        $especialidad = Especialidad::factory()->create([
            'nombre' => "Ingeniería Informática y de Sistemas Industriales",
            'descripcion' => "Especialidad enfocada en la Ingeniería para el desarrollo de competencias en Computacional y Experimental.",
            'facultad_id' => $facultad->id,
        ]);
        $cursos = Curso::factory(20)->create(['especialidad_id' => $especialidad->id]);

        $semestre = Semestre::updateOrCreate(
            ['anho' => 2024, 'periodo' => 2],
            ['estado' => 'activo']
        );

        $horarios = $cursos->flatMap(fn($curso) => Horario::factory(random_int(1, 3))->create([
            'curso_id' => $curso->id,
            'semestre_id' => $semestre->id,
        ]))->all();

        $estudiantes = Estudiante::factory(50)->create(['especialidad_id' => $especialidad->id]);
        
        collect($horarios)->each(function ($horario) use ($estudiantes) {
            $estudiantesSeleccionados = $estudiantes->random(rand(5, 15));
            foreach ($estudiantesSeleccionados as $estudiante) {
                $existeMatricula = HorarioEstudiante::where('estudiante_id', $estudiante->id)
                    ->whereHas('horario', function($query) use ($horario) {
                        $query->where('curso_id', $horario->curso_id);
                    })
                    ->exists();
                if (!$existeMatricula) {
                    HorarioEstudiante::create([
                        'estudiante_id' => $estudiante->id,
                        'horario_id' => $horario->id,
                    ]);
                }
            }
        });
        
        $jefes = $estudiantes->random(min(2 * count($horarios), $estudiantes->count()))
            ->map(fn($predocente) => Docente::factory()->create(['usuario_id' => $predocente->usuario_id]))
            ->values();
        collect($horarios)->each(function ($horario, $key) use ($jefes) {
            $docente = $jefes->get($key % $jefes->count());
            $horario->docentes()->attach($docente);
            $horario->jefePracticas()->create(['usuario_id' => $docente->usuario_id]);
        });

        $usuario = Usuario::create([
            'nombre' => 'Sofia',
            'apellido_paterno' => 'Escajadillo',
            'apellido_materno' => 'Bazán',
            'email' => 'sofia.escajadillo@gianluca.zzz',
            'picture' => 'https://random-d.uk/api/2.jpg',
            'estado' => 'activo',
            'password' => Hash::make('12345678'),
        ]);
        Docente::factory()->create(['usuario_id' => $usuario->id, 'especialidad_id' => $especialidad->id]);
        $role = Role::findByName('director');
        $usuario->assignRole($role);
        RoleScopeUsuario::create([
            'role_id' => $role->id,
            'scope_id' => Scope::firstOrCreate([
                'name' => 'Especialidad',
                'entity_type' => Especialidad::class,
            ])->id,
            'usuario_id' => $usuario->id,
            'entity_type' => Especialidad::class,
            'entity_id' => $especialidad->id,
        ]);
    }
}
