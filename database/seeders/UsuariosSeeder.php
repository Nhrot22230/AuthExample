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
use Spatie\Permission\Models\Role;

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

        // asignar roles de administrativo y docente a admin
        $administrativoRole = Role::findByName('administrativo');
        $docenteRole = Role::findByName('docente');
        $admin->assignRole([$administrativoRole, $docenteRole]);

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

        // Asignar el rol de secretario académico
        $secretarioRole = Role::findByName('secretarioAcademico');
        $secretario->assignRole($secretarioRole);


        $random_especialidad = Especialidad::where('facultad_id', 5)->inRandomOrder()->first() ?? Especialidad::factory()->create(['facultad_id' => 5]);
        $random_area = Area::where('especialidad_id', $random_especialidad->id)->inRandomOrder()->first() ?? Area::factory()->create(['especialidad_id' => $random_especialidad->id]);
        
        // Crear usuario director de carrera
        $director = Usuario::create([
            'nombre' => 'director',
            'apellido_paterno' => 'director',
            'apellido_materno' => 'director',
            'email' => 'director@gmail.com',
            'password' => Hash::make('12345678'),
            'estado' => 'activo',
        ]);

        // Crear el usuario director de carrera en Ingeniería de Sistemas
        Docente::create([
            'usuario_id' => $director->id,
            'codigoDocente' => 'director',
            'tipo' => 'TC',
            'especialidad_id' => $random_especialidad->id,
            // 'seccion_id' => ,
            'area_id' => $random_area->id,
        ]);

        $directorRol = Role::findByName('directorCarrera');
        $director->assignRole($directorRol);

        // Factor de multiplicación para la creación masiva de registros
        $factor = 30;

        // Crear docentes en general
        Docente::factory(10 * $factor)->create();

        // Crear docentes que pertenezcan a FACI
        Docente::factory(10 * $factor)->fromFacultad(5)->create();

        // Crear estudiantes y administrativos en cantidades definidas por el factor
        Estudiante::factory(50 * $factor)->create();
        Administrativo::factory(1 * $factor)->create();
    }
}
