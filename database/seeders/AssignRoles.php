<?php

namespace Database\Seeders;

use App\Models\Authorization\Permission;
use App\Models\Usuarios\Usuario;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AssignRoles extends Seeder
{
    public function run(): void
    {
        $admin_role = Role::findByName('administrador');
        $admin_role->syncPermissions(Permission::all());
        Usuario::find(1)->assignRole('administrador');


        $director_role = Role::findByName('director');
        $permisos_director = Permission::where('name', 'like', '%especialidades%')
            ->orWhere('name', 'like', '%solicitudes%')
            ->orWhere('name', 'like', '%encuestas%')
            ->orWhere('name', 'like', '%temas%')
            ->orWhere('name', 'like', '%unidades%')
            ->get();
        $director_role->syncPermissions($permisos_director);

        $coordinador_role = Role::findByName('coordinador');
        $permisos_coordinador = Permission::where('name', 'like', '%especialidades%')
            ->orWhere('name', 'like', '%solicitudes%')
            ->orWhere('name', 'like', '%encuestas%')
            ->orWhere('name', 'like', '%temas%')
            ->orWhere('name', 'like', '%unidades%')
            ->get();
        $coordinador_role->syncPermissions($permisos_coordinador);


        $secretario_role = Role::findByName('secretario-academico');
        $permisos_secretario = Permission::where('name', 'like', '%solicitudes%')
            ->orWhere('name', 'like', '%temas de tesis%')
            ->orWhere('name', 'like', '%observaciones%')
            ->orWhere('name', 'like', '%jurados%')
            ->orWhere('name', 'like', '%especialidades%')
            ->orWhere('name', 'like', '%mis unidades%')
            ->orWhere('name', 'like', '%matricula%')
            ->get();
        $secretario_role->syncPermissions($permisos_secretario);
        // Encontramos el rol de "docente"
        $docente_role = Role::findByName('docente');

        // Obtenemos los permisos que correspondan a los docentes
        $permisos_docente = Permission::where('name', 'like', '%cursos%')
            ->orWhere('name', 'like', '%encuestas%')
            ->orWhere('name', 'like', '%mis cursos%')
            ->orWhere('name', 'like', '%solicitudes de encuestas%')
            ->orWhere('name', 'like', '%horarios%')
            ->orWhere('name', 'like', '%solicitudes%')
            ->get();

        // Asignamos los permisos al rol de "docente"
        $docente_role->syncPermissions($permisos_docente);
        $estudiante_role = Role::findByName('estudiante');

        // Obtenemos los permisos que correspondan a los estudiantes
        $permisos_estudiante = Permission::where('name', 'like', '%mis cursos%')
            ->orWhere('name', 'like', '%mis encuestas%')
            ->orWhere('name', 'like', '%solicitudes%')
            ->orWhere('name', 'like', '%matriculas%')
            ->orWhere('name', 'like', '%mis unidades%')
            ->orWhere('name', 'like', '%candidaturas%')
            ->orWhere('name', 'like', '%temas de tesis%')
            ->get();
    
        // Asignamos los permisos al rol de "estudiante"
        $estudiante_role->syncPermissions($permisos_estudiante);
        $asistente_role = Role::findByName('asistente');

    // Obtenemos los permisos que correspondan a los asistentes
    $permisos_asistente = Permission::where('name', 'like', '%mis unidades%')
        ->orWhere('name', 'like', '%unidades%')
        ->orWhere('name', 'like', '%cursos%')
        ->orWhere('name', 'like', '%horarios%')
        ->orWhere('name', 'like', '%docentes%')
        ->orWhere('name', 'like', '%departamentos%')
        ->orWhere('name', 'like', '%facultades%')
        ->orWhere('name', 'like', '%especialidades%')
        ->orWhere('name', 'like', '%solicitudes%')
        ->orWhere('name', 'like', '%convocatorias%')
        ->orWhere('name', 'like', '%roles%')
        ->orWhere('name', 'like', '%usuarios%')
        ->orWhere('name', 'like', '%postulante%')
        ->orWhere('name', 'like', '%comite%')
        ->get();

    // Asignamos los permisos al rol de "asistente"
    $asistente_role->syncPermissions($permisos_asistente);
    }
}
