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
use App\Models\Matricula\HorarioEstudianteJp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Usuarios\Administrativo;

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
        $estudiantes[] = Estudiante::factory()->create([
            'usuario_id' => Usuario::factory()->create([
                'nombre' => 'Gianluca',
                'apellido_paterno' => 'Gomocio',
                'apellido_materno' => 'Barrionuevo',
                'email' => 'gian.luca@gianluka.zzz',
                'picture' => 'https://random-d.uk/api/27.jpg',
            ])->id,
            'especialidad_id' => $especialidad->id,
        ]);
        collect($horarios)->each(function ($horario) use ($estudiantes) {
            $estudiantesSeleccionados = $estudiantes->random(rand(5, 15));
            foreach ($estudiantesSeleccionados as $estudiante) {
                $existeMatricula = HorarioEstudiante::where('estudiante_id', $estudiante->id)
                    ->whereHas('horario', function ($query) use ($horario) {
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

        $estudiante_role = Role::with('scopes')->where('name', 'estudiante')->first();
        $horarios = Horario::with('curso', 'estudiantes')
            ->where('semestre_id', Semestre::where('estado', 'activo')->first()->id)
            ->orderBy('curso_id')
            ->get();
        $horarios->each(function ($horario) use ($estudiante_role) {
            $horario->estudiantes->each(function ($estudiante) use ($horario, $estudiante_role) {
                RoleScopeUsuario::create([
                    'usuario_id' => $estudiante->usuario_id,
                    'role_id' => $estudiante_role->id,
                    'scope_id' => $estudiante_role->scopes->first()->id,
                    'entity_id' => $horario->curso_id,
                    'entity_type' => Curso::class,
                ]);
            });
        });

        collect($horarios)->each(function ($horario) {
            $jefePractica = $horario->jefePracticas()->first();
            if ($jefePractica) {
                $horario->estudiantes->each(function ($estudiante) use ($jefePractica, $horario) {
                    $horarioEstudiante = HorarioEstudiante::where('estudiante_id', $estudiante->id)
                        ->where('horario_id', $horario->id)
                        ->first();
        
                    if ($horarioEstudiante) {
                        HorarioEstudianteJp::create([
                            'estudiante_horario_id' => $horarioEstudiante->id,
                            'jp_horario_id' => $jefePractica->id,
                            'encuestaJP' => false,
                        ]);
                    }
                });
            }
        });

        $usuarioAdministrativo = Usuario::create([
            'nombre' => 'Fernando',
            'apellido_paterno' => 'Fernández',
            'apellido_materno' => 'López',
            'email' => 'fernandino@gianluca.zzz',
            'picture' => 'https://random-d.uk/api/3.jpg',
            'estado' => 'activo',
            'password' => Hash::make('12345678'),
        ]);

        $administrativo = Administrativo::create([
            'usuario_id' => $usuarioAdministrativo->id,
            'codigoAdministrativo' => 'ADM123456',
            'lugarTrabajo' => 'Oficina administrativa',
            'cargo' => 'Jefe Administrativo',
            'facultad_id' => $facultad->id,  // La facultad a la que pertenece
        ]);
        
        $role = Role::findByName('asistente');  // Asegúrate de que existe un rol llamado 'administrativo'
        $usuarioAdministrativo->assignRole($role);

        // Crear el alcance para este administrativo, limitado solo a la especialidad
        RoleScopeUsuario::create([
            'role_id' => $role->id,
            'scope_id' => Scope::firstOrCreate([  // Aquí creamos un alcance a la especialidad
                'name' => 'Especialidad',
                'entity_type' => Especialidad::class,  // El alcance es de tipo Especialidad
            ])->id,
            'usuario_id' => $usuarioAdministrativo->id,
            'entity_type' => Especialidad::class,  // Tipo de entidad es Especialidad
            'entity_id' => $especialidad->id,  // Asignamos el ID de la especialidad específica
        ]);
            }
}
