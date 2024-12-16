<?php

namespace Database\Seeders;

use App\Models\Authorization\Role;
use App\Models\Authorization\Scope;
use App\Models\Universidad\Area;
use App\Models\Universidad\Curso;
use App\Models\Universidad\Departamento;
use App\Models\Universidad\Especialidad;
use App\Models\Universidad\Facultad;
use App\Models\Universidad\Seccion;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{

    public function run(): void
    {
        $scopes = [
            ['name' => 'Departamento', 'entity_type' => Departamento::class],
            ['name' => 'Facultad', 'entity_type' => Facultad::class],
            ['name' => 'Especialidad', 'entity_type' => Especialidad::class],
            ['name' => 'Seccion', 'entity_type' => Seccion::class],
            ['name' => 'Curso', 'entity_type' => Curso::class],
            ['name' => 'Area', 'entity_type' => Area::class],
        ];

        $roles = [
            'administrador',
            'secretario-academico',
            'asistente-especialidad',
            'asistente-seccion',
            'director',
            'coordinador-area',
            'coordinador-seccion',
            'docente',
            'jefe-practica',
            'estudiante',
            'comite',
        ];

        foreach ($scopes as $scope) Scope::firstOrCreate($scope);
        foreach ($roles as $role) Role::firstOrCreate(['name' => $role]);

        Role::findByName('asistente-especialidad')->scopes([])->attach(Scope::where('name', 'Especialidad')->first());
        Role::findByName('asistente-seccion')->scopes([])->attach(Scope::where('name', 'Seccion')->first());
        Role::findByName('secretario-academico')->scopes([])->attach(Scope::where('name', 'Facultad')->first());
        Role::findByName('director')->scopes([])->attach(Scope::where('name', 'Especialidad')->first());
        Role::findByName('coordinador-area')->scopes([])->attach(Scope::where('name', 'Area')->first());
        Role::findByName('coordinador-seccion')->scopes([])->attach(Scope::where('name', 'Seccion')->first());
        Role::findByName('docente')->scopes([])->attach(Scope::orWhere('name', 'Curso')->first());
        Role::findByName('jefe-practica')->scopes([])->attach(Scope::where('name', 'Curso')->first());
        Role::findByName('estudiante')->scopes([])->attach(Scope::where('name', 'Curso')->first());
        Role::findByName('comite')->scopes([])->attach(Scope::where('name', 'Curso')->first());
    }
}
