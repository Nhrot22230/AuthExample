<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Universidad\Especialidad;
use App\Models\Universidad\PlanEstudio;
use App\Models\Universidad\Curso;
use App\Models\Universidad\Semestre;
use App\Models\Universidad\Facultad;
use App\Models\Tramites\PedidoCursos;

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
            $nivelAleatorio = rand(0, $planEstudio->cantidad_semestres);
            $planEstudio->cursos()->syncWithoutDetaching([$cursoObligatorio->id => ['nivel' => $nivelAleatorio, 'creditosReq' => $cursoObligatorio->creditos]]);
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
        foreach ($cursosObligatorios as $cursoObligatorio) {
            $pedido->cursosObligatorios()->attach($cursoObligatorio->id);
        }

        // Asociar los cursos electivos al pedido
        foreach ($cursosElectivos as $cursoElectivo) {
            $pedido->cursosElectivosSeleccionados()->attach($cursoElectivo->id);
        }
    }
}
