<?php

namespace Database\Seeders;

use App\Models\Authorization\PermissionCategory;
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
        $categories = [
            'FACULTAD' => PermissionCategory::where('name', 'facultad')->first(),
            'ESPECIALIDAD' => PermissionCategory::where('name', 'especialidad')->first(),
        ];

        $scopes = [
            ['name' => 'Departamento', 'entity_type' => Departamento::class],
            ['name' => 'Facultad', 'entity_type' => Facultad::class, 'category_id' => $categories['FACULTAD']->id ?? null],
            ['name' => 'Especialidad', 'entity_type' => Especialidad::class, 'category_id' => $categories['ESPECIALIDAD']->id ?? null],
            ['name' => 'Seccion', 'entity_type' => Seccion::class],
            ['name' => 'Curso', 'entity_type' => Curso::class],
            ['name' => 'Area', 'entity_type' => Area::class],
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
            'asistente-seccion',
        ];

        foreach ($scopes as $scope) Scope::firstOrCreate($scope);
        foreach ($roles as $role) Role::firstOrCreate(['name' => $role]);

        Role::findByName('asistente')->scopes([])->attach(Scope::all());
        Role::findByName('secretario-academico')->scopes([])->attach(Scope::where('name', 'Facultad')->first());
        Role::findByName('director')->scopes([])->attach(Scope::where('name', 'Especialidad')->first());
        Role::findByName('coordinador')->scopes([])->attach(Scope::where('name', 'Area')->first());
        Role::findByName('asistente-seccion')->scopes([])->attach(Scope::where('name', 'Especialidad')->first());
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
