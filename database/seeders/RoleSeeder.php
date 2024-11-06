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
    /**
     * Run the database seeds.
     */
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
            'Administrador',
            'Secretario Académico',
            'Coordinador',
            'Asistente',
            'Director de Carrera',
            'Docente',
            'Jefe de Práctica',
            'Estudiante',
        ];

        foreach ($scopes as $scope) Scope::firstOrCreate($scope);
        foreach ($roles as $role) Role::firstOrCreate(['name' => $role]);

        Role::findByName('Asistente')->scopes()->attach(Scope::all());
        Role::findByName('Secretario Académico')->scopes()->attach(Scope::where('name', 'Facultad')->first());
        Role::findByName('Coordinador')->scopes()->attach(Scope::where('name', 'Departamento')->first());
        Role::findByName('Director de Carrera')->scopes()->attach(Scope::where('name', 'Especialidad')->first());
        Role::findByName('Docente')->scopes()->attach(
            Scope::orWhere('name', 'Curso')
            ->orWhere('name', 'Seccion')
            ->orWhere('name', 'Area')
            ->get()
        );

        Role::findByName('Jefe de Práctica')->scopes()->attach(Scope::where('name', 'Curso')->first());
        Role::findByName('Estudiante')->scopes()->attach(Scope::where('name', 'Curso')->first());
    }
}
