<?php

namespace Database\Seeders;

use App\Models\Administrativo;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Usuario;
use App\Models\Area;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsuariosSeeder extends Seeder
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

        $administrativoRole = Role::findByName('Administrador');
        $docenteRole = Role::findByName('Docente');
        $admin->assignRole([$administrativoRole, $docenteRole]);

        Administrativo::create([
            'usuario_id' => $admin->id,
            'codigoAdministrativo' => 'admin',
            'lugarTrabajo' => 'admin',
            'cargo' => 'admin',
            'facultad_id' => null,
        ]);

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

        $secretarioRole = Role::findByName('Secretario Académico');
        $secretario->assignRole($secretarioRole);

        $director = Usuario::create([
            'nombre' => 'director',
            'apellido_paterno' => 'director',
            'apellido_materno' => 'director',
            'email' => 'director@gmail.com',
            'password' => Hash::make('12345678'),
            'estado' => 'activo',
        ]);

        $random_area_sistemas = Area::where('especialidad_id', 28)->inRandomOrder()->first();
        if (!$random_area_sistemas) {
            $random_area_sistemas = Area::create([
                'especialidad_id' => 28,
                'nombre' => 'Área de Ingeniería de Sistemas'
            ]);
        }

        Docente::create([
            'usuario_id' => $director->id,
            'codigoDocente' => 'director',
            'tipo' => 'TC',
            'especialidad_id' => 28,
            'seccion_id' => 45,
            'area_id' => $random_area_sistemas->id,
        ]);

        $factor = 10;
        Docente::factory(8 * $factor)->create();
        Docente::factory(10 * $factor)->fromFacultad(5)->create();
        Estudiante::factory(50 * $factor)->create();
        Administrativo::factory(1 * $factor)->create();
    }
}
