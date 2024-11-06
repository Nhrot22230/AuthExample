<?php

namespace Database\Seeders;

use App\Models\Administrativo;
use App\Models\Authorization\Permission;
use App\Models\Authorization\RoleScopeUsuario;
use App\Models\Authorization\Scope;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AssignRoles extends Seeder
{
    public function run(): void
    {
        $admin_role = Role::findByName('Administrador');
        $admin_role->syncPermissions(Permission::all());

        $asistente_role = Role::findByName('Asistente');
        $asistente_role->syncPermissions(Permission::all());

        $secretario_role = Role::findByName('Secretario Académico');
        $secretario_role->syncPermissions(Permission::where('name', 'like', '% facultades')->get());

        $coordinador_role = Role::findByName('Coordinador');
        $coordinador_role->syncPermissions(Permission::where('name', 'like', '% departamentos')->get());

        $director_role = Role::findByName('Director de Carrera');
        $director_role->syncPermissions(Permission::where('name', 'like', '% especialidades')->get());

        $docente_role = Role::findByName('Docente');
        $docente_role->syncPermissions(Permission::where('name', 'like', '% cursos')->get());

        $jefe_role = Role::findByName('Jefe de Práctica');
        $jefe_role->syncPermissions(Permission::where('name', 'like', '% secciones')->get());

        $estudiante_role = Role::findByName('Estudiante');
        $estudiante_role->syncPermissions(Permission::where('name', 'ver cursos')->get());
        
        
        $administrador = Usuario::where('email', 'admin@gmail.com')->first();
        $administrador->assignRole('Administrador');
        

        $docentes = Docente::inRandomOrder()->limit(10)->get();
        foreach ($docentes as $docente) {
            $docente->usuario->assignRole('Docente');
            $random_cursos = Curso::inRandomOrder()->limit(3)->get();
            foreach ($random_cursos as $curso) {
                RoleScopeUsuario::create([
                    'role_id' => $docente_role->id,
                    'scope_id' => Scope::where('name', 'Curso')->first()->id,
                    'usuario_id' => $docente->usuario->id,
                    'entity_type' => Curso::class,
                    'entity_id' => $curso->id,
                ]);
            }
        }

        $estudiantes = Estudiante::inRandomOrder()->limit(10)->get();
        foreach ($estudiantes as $estudiante) {
            $estudiante->usuario->assignRole('Estudiante');
            $random_cursos = Curso::inRandomOrder()->limit(6)->get();
            foreach ($random_cursos as $curso) {
                RoleScopeUsuario::create([
                    'role_id' => $estudiante_role->id,
                    'scope_id' => Scope::where('name', 'Curso')->first()->id,
                    'usuario_id' => $estudiante->usuario->id,
                    'entity_type' => Curso::class,
                    'entity_id' => $curso->id,
                ]);
            }
        }

        $directores = Docente::inRandomOrder()->limit(5)->get();
        foreach ($directores as $director) {
            $director->usuario->assignRole('Director de Carrera');
            $random_especialidad = Curso::inRandomOrder()->first();
            RoleScopeUsuario::create([
                'role_id' => $director_role->id,
                'scope_id' => Scope::where('name', 'Especialidad')->first()->id,
                'usuario_id' => $director->usuario->id,
                'entity_type' => Curso::class,
                'entity_id' => $random_especialidad->id,
            ]);
        }

        $asistentes = Administrativo::inRandomOrder()->limit(5)->get();
        foreach ($asistentes as $asistente) {
            $asistente->usuario->assignRole('Asistente');
            $random_scopes = Scope::inRandomOrder()->limit(2)->get();
            foreach ($random_scopes as $scope) {
                $random_entities = $scope->entity_type::inRandomOrder()->limit(3)->get();
                foreach ($random_entities as $entity) {
                    RoleScopeUsuario::create([
                        'role_id' => $asistente_role->id,
                        'scope_id' => $scope->id,
                        'usuario_id' => $asistente->usuario->id,
                        'entity_type' => $scope->entity_type,
                        'entity_id' => $entity->id,
                    ]);
                }
            }
        }
    }
}
