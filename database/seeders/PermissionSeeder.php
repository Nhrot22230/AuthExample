<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'ver instituciones', 'category' => 'instituciones'],
            ['name' => 'manage instituciones', 'category' => 'instituciones'],

            ['name' => 'ver ciclos', 'category' => 'ciclos'],
            ['name' => 'manage ciclos', 'category' => 'ciclos'],

            ['name' => 'ver semestres', 'category' => 'semestres'],
            ['name' => 'manage semestres', 'category' => 'semestres'],

            ['name' => 'ver areas', 'category' => 'areas'],
            ['name' => 'manage areas', 'category' => 'areas'],

            ['name' => 'ver facultades', 'category' => 'facultades'],
            ['name' => 'manage facultades', 'category' => 'facultades'],

            ['name' => 'ver departamentos', 'category' => 'departamentos'],
            ['name' => 'manage departamentos', 'category' => 'departamentos'],

            ['name' => 'ver especialidades', 'category' => 'especialidades'],
            ['name' => 'manage especialidades', 'category' => 'especialidades'],

            ['name' => 'ver secciones', 'category' => 'secciones'],
            ['name' => 'manage secciones', 'category' => 'secciones'],

            ['name' => 'ver cursos', 'category' => 'cursos'],
            ['name' => 'manage cursos', 'category' => 'cursos'],

            ['name' => 'ver planes de estudio', 'category' => 'planes de estudio'],
            ['name' => 'manage planes de estudio', 'category' => 'planes de estudio'],

            ['name' => 'ver horarios', 'category' => 'horarios'],
            ['name' => 'manage horarios', 'category' => 'horarios'],

            ['name' => 'ver usuarios', 'category' => 'usuarios'],
            ['name' => 'manage usuarios', 'category' => 'usuarios'],

            ['name' => 'ver roles', 'category' => 'roles'],
            ['name' => 'manage roles', 'category' => 'roles'],

            ['name' => 'ver permisos', 'category' => 'permisos'],
            ['name' => 'manage permisos', 'category' => 'permisos'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
