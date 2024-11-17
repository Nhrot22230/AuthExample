<?php

namespace Database\Seeders;

use App\Models\Authorization\Permission;
use App\Models\Authorization\Role;
use App\Models\Authorization\RoleScopeUsuario;
use App\Models\Authorization\Scope;
use App\Models\Convocatorias\Convocatoria;
use App\Models\Convocatorias\GrupoCriterios;
use App\Models\Convocatorias\CandidatoConvocatoria;
use App\Models\Convocatorias\ComiteCandidatoConvocatoria;
use App\Models\Usuarios\Administrativo;
use App\Models\Usuarios\Docente;
use App\Models\Usuarios\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProcesoConvocatoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Crear 20 grupos de criterios
        $gruposCriterios = GrupoCriterios::factory(20)->create();

        // Crear 15 convocatorias
        $convocatorias = Convocatoria::factory(15)->create();

        $secciones = $convocatorias->pluck('seccion')->unique();
        $seccionesAleatorias = $secciones->random(2);
        $seccionFirst = $seccionesAleatorias->first();
        $seccionSecond = $seccionesAleatorias->last();

        $asistente = Administrativo::factory()->create([
            'usuario_id' => Usuario::factory()->create([
                'nombre' => 'Fernando',
                'apellido_paterno' => 'Candia',
                'apellido_materno' => 'Aroni',
                'email' => 'fernando.candia@gianluka.zzz',
                'password' => Hash::make('12345678'),
            ])
        ]);

        // el asistente es tiene poder sobre dos secciones como asistente
        $role_asistente = Role::findByName('asistente');
        $scope = Scope::where('name', 'Seccion')->first();
        $asistente->usuario->assignRole('asistente');

        RoleScopeUsuario::create([
            'usuario_id' => $asistente->usuario_id,
            'role_id' => $role_asistente->id,
            'scope_id' => $scope->id,
            'entity_id' => $seccionFirst->id,
            'entity_type' => $scope->entity_type,
        ]);

        RoleScopeUsuario::create([
            'usuario_id' => $asistente->usuario_id,
            'role_id' => $role_asistente->id,
            'scope_id' => $scope->id,
            'entity_id' => $seccionSecond->id,
            'entity_type' => $scope->entity_type,
        ]);

        // el asisnte es tiene poder sobre dos secciones como miembro del comite
        $role_comite = Role::findByName('comite');
        $scope = Scope::where('name', 'Seccion')->first();
        $asistente->usuario->assignRole('comite');

        RoleScopeUsuario::create([
            'usuario_id' => $asistente->usuario_id,
            'role_id' => $role_comite->id,
            'scope_id' => $scope->id,
            'entity_id' => $seccionFirst->id,
            'entity_type' => $scope->entity_type,
        ]);

        RoleScopeUsuario::create([
            'usuario_id' => $asistente->usuario_id,
            'role_id' => $role_comite->id,
            'scope_id' => $scope->id,
            'entity_id' => $seccionSecond->id,
            'entity_type' => $scope->entity_type,
        ]);



        // Asignar entre 1 y 3 grupos de criterios a cada convocatoria
        foreach ($convocatorias as $convocatoria) {
            $gruposAsignados = $gruposCriterios->random(rand(1, 3)); // Asigna entre 1 y 3 grupos
            $convocatoria->gruposCriterios()->attach($gruposAsignados);
        }

        // Crear entre 3 y 8 docentes para cada convocatoria
        foreach ($convocatorias as $convocatoria) {
            $docentes = Docente::factory(rand(3, 8))->create(); // Crear entre 3 y 8 docentes
            foreach ($docentes as $docente) {
                $convocatoria->comite()->attach($docente); // Asignar cada docente a la convocatoria
            }
        }

        // Crear CandidatoConvocatoria para cada convocatoria (mínimo 5 por convocatoria)
        foreach ($convocatorias as $convocatoria) {
            $usuarios = Usuario::factory(rand(5, 10))->create(); // Crear entre 5 y 10 candidatos

            foreach ($usuarios as $asistente) {
                CandidatoConvocatoria::create([
                    'convocatoria_id' => $convocatoria->id,
                    'candidato_id' => $asistente->id,
                    'estadoFinal' => 'pendiente cv',
                    'urlCV' => 'http://example.com/cv.pdf',
                ]);
            }
        }

        // Crear ComiteCandidatoConvocatoria para cada candidato y convocatoria
        foreach ($convocatorias as $convocatoria) {
            $candidatos = $convocatoria->candidatos; // Obtén todos los candidatos asignados a esta convocatoria

            // Obtener todos los docentes asignados a esta convocatoria
            $docentes = $convocatoria->comite;

            foreach ($candidatos as $candidato) {
                foreach ($docentes as $docente) {
                    ComiteCandidatoConvocatoria::create([
                        'docente_id' => $docente->id,
                        'candidato_id' => $candidato->id,
                        'convocatoria_id' => $convocatoria->id,
                        'estado' => 'pendiente cv',
                    ]);
                }
            }
        }
    }
}
