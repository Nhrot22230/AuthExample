<?php

namespace Database\Seeders;

use App\Models\Authorization\Role;
use App\Models\Authorization\RoleScopeUsuario;
use App\Models\Authorization\Scope;
use App\Models\Tramites\EstadoAprobacionTema;
use App\Models\Tramites\ProcesoAprobacionTema;
use App\Models\Tramites\TemaDeTesis;
use App\Models\Universidad\Area;
use App\Models\Universidad\Curso;
use App\Models\Universidad\Especialidad;
use App\Models\Universidad\Facultad;
use App\Models\Usuarios\Docente;
use App\Models\Usuarios\Estudiante;
use App\Models\Usuarios\Usuario;
use Database\Factories\Tramites\ProcesoAprobacionTemaFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FlujoTemaTesisSeeder extends Seeder
{

    public function run(): void
    {
        $facultad = Facultad::inRandomOrder()->firstOrCreate();
        $especialidad = Especialidad::factory()->create([
            'nombre' => "Ingeniería Informática y Prueba para Temas de Tesis",
            'descripcion' => "Especialidad enfocada en la Ingeniería para el desarrollo y pruebas de Temas de Tesis.",
            'facultad_id' => $facultad->id,
        ]);

        $areas = Area::factory(1)->create([
            'especialidad_id' => $especialidad->id,
        ]);

        $usuarioEstudiante = Usuario::create([
            'nombre' => 'Estudiante',
            'apellido_paterno' => 'Creado',
            'apellido_materno' => 'Recien',
            'email' => 'estudiante@gmail.com',
            'picture' => 'https://random-d.uk/api/2.jpg',
            'estado' => 'activo',
            'password' => Hash::make('password'),
        ]);
        $estudiante = Estudiante::factory()->create(['usuario_id' => $usuarioEstudiante->id, 'especialidad_id' => $especialidad->id]);
        $role = Role::findByName('estudiante');
        $usuarioEstudiante->assignRole($role);
        RoleScopeUsuario::create([
            'role_id' => $role->id,
            'scope_id' => Scope::firstOrCreate([
                'name' => 'Curso',
                'entity_type' => Curso::class,
            ])->id,
            'usuario_id' => $usuarioEstudiante->id,
            'entity_type' => Curso::class,
            'entity_id' => $especialidad->id,
        ]);

        $usuarioDocente = Usuario::create([
            'nombre' => 'Docente',
            'apellido_paterno' => 'Creado',
            'apellido_materno' => 'Recien',
            'email' => 'docente@gmail.com',
            'picture' => 'https://random-d.uk/api/2.jpg',
            'estado' => 'activo',
            'password' => Hash::make('password'),
        ]);
        $docente = Docente::factory()->create([
            'usuario_id' => $usuarioDocente->id,
            'especialidad_id' => $especialidad->id,
            'area_id' => $areas->first()->id
        ]);
        $role = Role::findByName('docente');
        $usuarioDocente->assignRole($role);
        RoleScopeUsuario::create([
            'role_id' => $role->id,
            'scope_id' => Scope::firstOrCreate([
                'name' => 'Curso',
                'entity_type' => Curso::class,
            ])->id,
            'usuario_id' => $usuarioDocente->id,
            'entity_type' => Curso::class,
            'entity_id' => $especialidad->id,
        ]);

        $usuarioCoordinador = Usuario::create([
            'nombre' => 'Docente Coordinador',
            'apellido_paterno' => 'Creado',
            'apellido_materno' => 'Recien',
            'email' => 'docenteCoordinador@gmail.com',
            'picture' => 'https://random-d.uk/api/2.jpg',
            'estado' => 'activo',
            'password' => Hash::make('password'),
        ]);
        
        Docente::factory()->create([
            'usuario_id' => $usuarioCoordinador->id,
            'especialidad_id' => $especialidad->id,
            'area_id' => $areas->first()->id
        ]);
        $role = Role::findByName('coordinador');
        $usuarioCoordinador->assignRole($role);
        RoleScopeUsuario::create([
            'role_id' => $role->id,
            'scope_id' => Scope::firstOrCreate([
                'name' => 'Area',
                'entity_type' => Area::class,
            ])->id,
            'usuario_id' => $usuarioCoordinador->id,
            'entity_type' => Area::class,
            'entity_id' => $areas->first()->id,
        ]);

        $usuarioDirector = Usuario::create([
            'nombre' => 'Director',
            'apellido_paterno' => 'Creado',
            'apellido_materno' => 'Recien',
            'email' => 'director@gmail.com',
            'picture' => 'https://random-d.uk/api/2.jpg',
            'estado' => 'activo',
            'password' => Hash::make('password'),
        ]);
        
        Docente::factory()->create([
            'usuario_id' => $usuarioDirector->id,
            'especialidad_id' => $especialidad->id,
            'area_id' => $areas->first()->id
        ]);
        $role = Role::findByName('director');
        $usuarioDirector->assignRole($role);
        RoleScopeUsuario::create([
            'role_id' => $role->id,
            'scope_id' => Scope::firstOrCreate([
                'name' => 'Especialidad',
                'entity_type' => Especialidad::class,
            ])->id,
            'usuario_id' => $usuarioDirector->id,
            'entity_type' => Especialidad::class,
            'entity_id' => $especialidad->id,
        ]);

        // SECRETARIO ACADEMICO
        $usuarioSecretario = Usuario::create([
            'nombre' => 'Secretario',
            'apellido_paterno' => 'Creado',
            'apellido_materno' => 'Recien',
            'email' => 'secretario@gmail.com',
            'picture' => 'https://random-d.uk/api/2.jpg',
            'estado' => 'activo',
            'password' => Hash::make('password'),
        ]);

        $role = Role::findByName('secretario-academico');
        $usuarioSecretario->assignRole($role);
        RoleScopeUsuario::create([
            'role_id' => $role->id,
            'scope_id' => Scope::firstOrCreate([
                'name' => 'Facultad',
                'entity_type' => Facultad::class,
            ])->id,
            'usuario_id' => $usuarioSecretario->id,
            'entity_type' => Facultad::class,
            'entity_id' => $facultad->id,
        ]);

        $temasTesis = TemaDeTesis::factory(1)->create([
            'area_id' => $areas->random()->id,
            'especialidad_id' => $especialidad->id,
            'estado' => 'pendiente',
            'fecha_enviado' => Now()
        ]);

        foreach($temasTesis as $tema){
            $tema->asesores()->attach($docente->id);
            $tema->estudiantes()->attach($estudiante->id);
        }

        $procesoAprobacion = ProcesoAprobacionTema::factory()->create([
            'tema_tesis_id' => $temasTesis->random()->id,
            'fecha_inicio' => Now(),
            'estado_proceso' => 'pendiente'
        ]);

        EstadoAprobacionTema::factory()->create([
            'proceso_aprobacion_id' => $procesoAprobacion->id,
            'usuario_id' => $usuarioDocente->id,
            'estado' => 'pendiente',
            'fecha_decision' => null,
            'comentarios' => null,
            'file_id' => null,
            'responsable' => 'asesor'
        ]);
    }
}
