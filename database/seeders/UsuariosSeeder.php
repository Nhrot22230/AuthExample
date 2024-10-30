<?php

namespace Database\Seeders;

use App\Models\Administrativo;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Usuario;
use App\Models\Area;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuarios necesarios para el sistema
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
            'facultad_id' => null, // Relación opcional
        ]);
        

        // Secretario de la facultad de ingeniería
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
            'facultad_id' => 5, // Relación opcional
        ]);

        // Director de carrera ingeniería en sistemas
        $director = Usuario::create([
            'nombre' => 'director',
            'apellido_paterno' => 'director',
            'apellido_materno' => 'director',
            'email' => 'director@gmail.com',
            'password' => Hash::make('12345678'),
            'estado' => 'activo',
        ]);

        Docente::create([
            'usuario_id' => $director->id,
            'codigoDocente' => 'director',
            'tipo' => 'TC',
            'especialidad_id' => 28,
            'seccion_id' => 45,
            'area_id' => Area::create(['especialidad_id' => 28, 'nombre' => 'Ciencias de la computación'])->id,
        ]);
           
        $factor = 2;
        Docente::factory(8 * $factor)->create();
        Estudiante::factory(50 * $factor)->create();
        Administrativo::factory(1*$factor)->create();
    }
}
