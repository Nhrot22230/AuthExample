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
            ['name' => 'Departamento'],
            ['name' => 'Facultad'],
            ['name' => 'Especialidad'],
            ['name' => 'Seccion'],
            ['name' => 'Curso'],
            ['name' => 'Area'],
        ];

        $roles = [
            'administrador',
            'secretario-academico',
            'asistente',
            'director',
            'coordinador',
            'docente',
            'jefe-practica',
            'estudiante',
            'coordinador',
            'comite',
        ];

        foreach ($scopes as $scope) Scope::firstOrCreate($scope);
        foreach ($roles as $role) Role::firstOrCreate(['name' => $role]);

        Role::findByName('asistente')->scopes([])->attach(Scope::all());
        Role::findByName('secretario-academico')->scopes([])->attach(Scope::where('name', 'Facultad')->first());
        Role::findByName('director')->scopes([])->attach(Scope::where('name', 'Especialidad')->first());
        Role::findByName('coordinador')->scopes([])->attach(Scope::where('name', 'Area')->first());
        Role::findByName('docente')->scopes([])->attach(
            Scope::orWhere('name', 'Curso')
            ->orWhere('name', 'Seccion')
            ->orWhere('name', 'Area')
            ->get()
        );
        Role::findByName('jefe-practica')->scopes([])->attach(Scope::where('name', 'Curso')->first());
        Role::findByName('estudiante')->scopes([])->attach(Scope::where('name', 'Curso')->first());
        Role::findByName('comite')->scopes([])->attach(Scope::where('name', 'Curso')->first());
    }
}
