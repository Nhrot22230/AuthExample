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
use App\Models\Usuarios\Administrativo;
use App\Models\Usuarios\Docente;
use App\Models\Usuarios\Estudiante;
use App\Models\Usuarios\Usuario;
use App\Models\Matricula\HorarioEstudiante;
use App\Models\Matricula\HorarioEstudianteJp;
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
        $facultad = Facultad::factory()->create([
            'nombre' => 'Facultad de Ciencias e Ingeniería',
        ]);
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
            'oculto' => false,
        ]))->all();

        $estudiantes = Estudiante::factory(50)->create(['especialidad_id' => $especialidad->id]);
        $estudiantes[] = Estudiante::factory()->create([
            'usuario_id' => Usuario::factory()->create([
                'nombre' => 'Gianluca',
                'apellido_paterno' => 'Gomocio',
                'apellido_materno' => 'Barrionuevo',
                'email' => 'gian.luca@gianluka.zzz',
                'picture' => 'https://random-d.uk/api/27.jpg',
                'password' => Hash::make('12345678'),
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
            $horario->estudiantes()->each(function ($estudiante) use ($horario, $estudiante_role) {
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

        Administrativo::create([
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

        $usuarioDocente = Usuario::create([
            'nombre' => 'David',
            'apellido_paterno' => 'Allasi',
            'apellido_materno' => '',
            'email' => 'david.allasi@gianluca.zzz',
            'picture' => 'https://random-d.uk/api/4.jpg',
            'estado' => 'activo',
            'password' => Hash::make('12345678'),
        ]);

        // Crear el docente
        $docente = Docente::factory()->create(['usuario_id' => $usuarioDocente->id, 'especialidad_id' => $especialidad->id]);

        // Asignar el rol de "docente" al usuario
        $role = Role::findByName('docente');  // Asegúrate de que el rol 'docente' existe
        $usuarioDocente->assignRole($role);

        // Crear el alcance (scope) para este docente, limitado solo a la especialidad
        RoleScopeUsuario::create([
            'role_id' => $role->id,
            'scope_id' => Scope::firstOrCreate([
                'name' => 'Especialidad',
                'entity_type' => Especialidad::class,
            ])->id,
            'usuario_id' => $usuarioDocente->id,
            'entity_type' => Especialidad::class,
            'entity_id' => $especialidad->id,
        ]);
        $gianluca = Estudiante::where('usuario_id', Usuario::where('email', 'gian.luca@gianluka.zzz')->first()->id)->first();

        // Obtener los horarios en los que está matriculado Gianluca
        $horariosDeGianluca = $gianluca->horarios;  // Aquí obtenemos todos los horarios
        $horario = $horariosDeGianluca->first();

        if ($horariosDeGianluca->isNotEmpty()) {
            // Seleccionamos el primer horario en el que Gianluca está matriculado (puedes ajustar esto si es necesario)
            $horario = $horariosDeGianluca->first();

            // Ahora, aseguramos que solo David Allasi sea el docente asignado a este horario
            // Sincronizamos los docentes (esto eliminará cualquier otro docente asignado)
            $horario->docentes()->sync([$docente->id]);

            // Asegurarnos de que Gianluca está matriculado en este horario en la tabla estudiante_horario
            $existeMatricula = HorarioEstudiante::where('estudiante_id', $gianluca->id)
                ->where('horario_id', $horario->id)
                ->exists();

            // Si no está matriculado, lo matriculamos
            if (!$existeMatricula) {
                HorarioEstudiante::create([
                    'estudiante_id' => $gianluca->id,
                    'horario_id' => $horario->id,
                ]);
            }
        }
        if ($gianluca) {
            // Obtener el rol de 'estudiante'
            $estudiante_role = Role::findByName('estudiante');  // Asegúrate de que este rol exista en tu base de datos
        
            // Asignar el rol al usuario
            if ($estudiante_role) {
                // Asignar el rol al estudiante
                $gianluca->usuario->assignRole($estudiante_role);  // Asignar el rol al usuario relacionado con Gianluca
            }
        }
        $usuarioSecretario = Usuario::create([
            'nombre' => 'Roberto',
            'apellido_paterno' => 'Palacios',
            'apellido_materno' => 'Lara',
            'email' => 'roberto.palacios@gianluca.zzz', // Puedes poner cualquier correo
            'picture' => 'https://random-d.uk/api/5.jpg', // Puedes poner una foto aleatoria o una URL real
            'estado' => 'activo',
            'password' => Hash::make('12345678'), // Puedes cambiar la contraseña si lo deseas
        ]);

        $administrativo = Administrativo::create([
            'usuario_id' => $usuarioSecretario->id,
            'codigoAdministrativo' => 'SEC123456',  // Código administrativo, puedes cambiarlo
            'lugarTrabajo' => 'Oficina de Secretaría Académica',
            'cargo' => 'Secretario Académico',
            'facultad_id' => $facultad->id,  // Asociar a la facultad existente
        ]);

        // Asignar el rol "secretario-academico" al usuario
        $role = Role::findByName('secretario-academico');  // Asegúrate de que el rol exista

        // Asignar el rol al usuario
        $usuarioSecretario->assignRole($role);

        // Crear el alcance (scope) para este secretario, limitado solo a la facultad
        RoleScopeUsuario::create([
            'role_id' => $role->id,
            'scope_id' => Scope::firstOrCreate([
                'name' => 'Facultad',
                'entity_type' => Facultad::class,
            ])->id,
            'usuario_id' => $usuarioSecretario->id,
            'entity_type' => Facultad::class,
            'entity_id' => $facultad->id,  // Asignar el ID de la facultad a la que pertenece
        ]);

    }



}
