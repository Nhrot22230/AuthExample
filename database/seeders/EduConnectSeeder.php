<?php

namespace Database\Seeders;

use App\Models\Administrativo;
use App\Models\Area;
use App\Models\Departamento;
use App\Models\Docente;
use App\Models\Especialidad;
use App\Models\Estudiante;
use App\Models\Facultad;
use App\Models\Seccion;
use App\Models\Usuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EduConnectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Usuario::create([
            'nombre' => 'admin',
            'apellido_paterno' => 'admin',
            'apellido_materno' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'estado' => 'activo',
        ]);

        Administrativo::create([
            'usuario_id' => $admin->id,
            'codigoAdministrativo' => 'admin',
        ]);

        Departamento::factory(10)->create();
        Facultad::factory(10)->create();
        Especialidad::factory(25)->create();
        Seccion::factory(5)->create();
        Area::factory(10)->create();
        Docente::factory(30)->create();
        Estudiante::factory(50)->create();
        Administrativo::factory(5)->create();
    }
}
