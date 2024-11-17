<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Universidad\Especialidad;
use App\Models\Universidad\PlanEstudio;
use App\Models\Universidad\Curso;
use App\Models\Universidad\Semestre;
use App\Models\Universidad\Facultad;
use App\Models\Tramites\PedidoCursos;
use App\Models\Usuarios\Docente;
use App\Models\Usuarios\Usuario;
use App\Models\Authorization\Role;
use App\Models\Authorization\RoleScopeUsuario;
use App\Models\Authorization\Scope;
use App\Models\Matricula\Horario;
use App\Models\Usuarios\Administrativo;
use Illuminate\Support\Facades\Hash;

class PedidoCursosSeeder extends Seeder
{
    public function run()
    {
        // Obtener la especialidad, facultad y semestre activos
        $especialidad = Especialidad::first();
        $facultad = $especialidad ? $especialidad->facultad : Facultad::first();
        $semestre = Semestre::where('estado', 'Activo')->latest('fecha_inicio')->first();

        // Crear o encontrar un plan de estudios
        $planEstudio = PlanEstudio::firstOrCreate([
            'cantidad_semestres' => 10,
            'especialidad_id' => $especialidad ? $especialidad->id : null,
            'estado' => 'Activo',
        ]);

        // Verificar si existen suficientes cursos para la especialidad seleccionada
        $minCursos = 10;
        $cursosDisponibles = Curso::where('estado', 'Activo')
            ->where('especialidad_id', $especialidad ? $especialidad->id : null)
            ->get();

        // Crear cursos adicionales si no hay suficientes
        if ($cursosDisponibles->count() < $minCursos) {
            $faltantes = $minCursos - $cursosDisponibles->count();
            for ($i = 0; $i < $faltantes; $i++) {
                $nuevoCurso = Curso::create([
                    'especialidad_id' => $especialidad ? $especialidad->id : null,
                    'cod_curso' => 'CURSO' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'nombre' => 'Curso de Ejemplo ' . ($i + 1),
                    'creditos' => rand(2, 4),
                    'estado' => 'Activo',
                    'ct' => rand(1, 3),
                    'pa' => rand(1, 2),
                    'pb' => rand(0, 1),
                    'me' => rand(1, 3),
                ]);
                $cursosDisponibles->push($nuevoCurso);
            }
        }

        // Seleccionar múltiples cursos obligatorios y electivos, asegurando que no se repitan
        $numObligatorios = 5;  // Define la cantidad de cursos obligatorios
        $numElectivos = 2;     // Define la cantidad de cursos electivos

        $cursosDisponibles = $cursosDisponibles->shuffle(); // Mezcla los cursos para aleatoriedad
        $cursosObligatorios = $cursosDisponibles->take($numObligatorios);
        $cursosElectivos = $cursosDisponibles->slice($numObligatorios, $numElectivos);

        // Asignar cursos obligatorios al plan de estudios con niveles aleatorios
        foreach ($cursosObligatorios as $cursoObligatorio) {
            $nivelAleatorio = rand(1, $planEstudio->cantidad_semestres);
            $planEstudio->cursos()->syncWithoutDetaching([$cursoObligatorio->id => ['nivel' => (string)($nivelAleatorio), 'creditosReq' => $cursoObligatorio->creditos]]);
        }

        // Asignar cursos electivos al plan de estudios con nivel 'E'
        foreach ($cursosElectivos as $cursoElectivo) {
            $planEstudio->cursos()->syncWithoutDetaching([$cursoElectivo->id => ['nivel' => 'E', 'creditosReq' => $cursoElectivo->creditos]]);
        }

        // Crear el pedido de cursos
        $pedido = PedidoCursos::create([
            'estado' => 'No Enviado',
            'observaciones' => 'Pedido de cursos para el semestre actual',
            'enviado' => false,
            'semestre_id' => $semestre ? $semestre->id : null,
            'facultad_id' => $facultad ? $facultad->id : null,
            'especialidad_id' => $especialidad ? $especialidad->id : null,
            'plan_estudio_id' => $planEstudio->id,
        ]);

        // Asociar los cursos obligatorios al pedido
        /*
        foreach ($cursosObligatorios as $cursoObligatorio) {
            $pedido->cursosObligatorios()->attach($cursoObligatorio->id);
        }
        */

        // Asociar los cursos electivos al pedido
        foreach ($cursosElectivos as $cursoElectivo) {
            $pedido->cursosElectivosSeleccionados()->attach($cursoElectivo->id, [
                'nivel' => 'E',
                'creditosReq' => $cursoElectivo->creditos
            ]);
        }

        // Crear horarios para algunos cursos del pedido
        $cursosParaHorarios = $cursosObligatorios->merge($cursosElectivos)->random(3); // Selecciona 3 cursos al azar
        foreach ($cursosParaHorarios as $curso) {
            Horario::factory(rand(1, 3))->create([
                'curso_id' => $curso->id,
                'semestre_id' => $semestre->id,
                'oculto' => random_int(0,1), // Puedes cambiar este valor según tus necesidades
            ]);
        }        

        // Crear el usuario "Daniel Rivas" como director de carrera
        $usuario = Usuario::create([
            'nombre' => 'Daniel',
            'apellido_paterno' => 'Rivas',
            'apellido_materno' => 'Pareja',
            'email' => 'daniel.rivas@gianluca.zzz',
            'picture' => 'https://random-d.uk/api/2.jpg',
            'estado' => 'activo',
            'password' => Hash::make('12345678'),  // Cambia la contraseña si es necesario
        ]);

        // Crear el perfil de Docente y asociarlo a la especialidad
        Docente::factory()->create([
            'usuario_id' => $usuario->id,
            'especialidad_id' => $especialidad ? $especialidad->id : null,
        ]);

        // Asignar el rol de director
        $role = Role::findByName('director');
        $usuario->assignRole($role);

        // Crear el scope de rol para la especialidad
        RoleScopeUsuario::create([
            'role_id' => $role->id,
            'scope_id' => Scope::firstOrCreate([
                'name' => 'Especialidad',
                'entity_type' => Especialidad::class,
            ])->id,
            'usuario_id' => $usuario->id,
            'entity_type' => Especialidad::class,
            'entity_id' => $especialidad ? $especialidad->id : null,
        ]);       
        
        //Usuario secretario academico
        $usuario = Usuario::create([
            'nombre' => 'Daniel Secretario',
            'apellido_paterno' => 'Rivas',
            'apellido_materno' => 'Pareja',
            'email' => 'daniel.rivas.secretario@gianluca.zzz',
            'picture' => 'https://random-d.uk/api/2.jpg',
            'estado' => 'activo',
            'password' => Hash::make('12345678'),  // Cambia la contraseña si es necesario
        ]);        

        Administrativo::factory()->create([
            'usuario_id' => $usuario->id,
            'facultad_id' => $facultad ? $facultad->id : null,
        ]);

        $role = Role::findByName('secretario-academico');
        $usuario->assignRole($role);

        RoleScopeUsuario::create([
            'role_id' => $role->id,
            'scope_id' => Scope::firstOrCreate([
                'name' => 'Facultad',
                'entity_type' => Facultad::class,
            ])->id,
            'usuario_id' => $usuario->id,
            'entity_type' => Facultad::class,
            'entity_id' => $facultad ? $facultad->id : null,
        ]);
    }
}
