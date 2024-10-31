<?php

namespace Database\Seeders;

use App\Models\Administrativo;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Usuario;
use App\Models\Area;
use App\Models\Especialidad;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador
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
            'lugarTrabajo' => 'admin',
            'cargo' => 'admin',
            'facultad_id' => null,
        ]);

        // Crear usuario secretario
        $secretario = Usuario::create([
            'nombre' => 'secretario',
            'apellido_paterno' => 'secretario',
            'apellido_materno' => 'secretario',
            'email' => 'secretario@gmail.com',
            'password' => Hash::make('12345678'),
            'estado' => 'activo',
        ]);

        Administrativo::create([
            'usuario_id' => $secretario->id,
            'codigoAdministrativo' => 'secretario',
            'lugarTrabajo' => 'secretario',
            'cargo' => 'Secretario',
            'facultad_id' => 5,
        ]);

        // Crear usuario director de carrera
        $director = Usuario::create([
            'nombre' => 'director',
            'apellido_paterno' => 'director',
            'apellido_materno' => 'director',
            'email' => 'director@gmail.com',
            'password' => Hash::make('12345678'),
            'estado' => 'activo',
        ]);

        // Seleccionar o crear un área específica para Ingeniería de Sistemas (ID 28)
        $random_area_sistemas = Area::where('especialidad_id', 28)->inRandomOrder()->first();
        if (!$random_area_sistemas) {
            $random_area_sistemas = Area::create([
                'especialidad_id' => 28,
                'nombre' => 'Área de Ingeniería de Sistemas'
            ]);
        }

        // Crear el usuario director de carrera en Ingeniería de Sistemas
        Docente::create([
            'usuario_id' => $director->id,
            'codigoDocente' => 'director',
            'tipo' => 'TC',
            'especialidad_id' => 28,
            'seccion_id' => 45,
            'area_id' => $random_area_sistemas->id,
        ]);

        // Factor de multiplicación para la creación masiva de registros
        $factor = 30;

        // Crear docentes en general
        Docente::factory(8 * $factor)->create();

        // Crear docentes que pertenezcan a FACI
        Docente::factory(10 * $factor)->fromFacultad(5)->create();

        // Crear estudiantes y administrativos en cantidades definidas por el factor
        Estudiante::factory(50 * $factor)->create();
        Administrativo::factory(1 * $factor)->create();
    }
}
