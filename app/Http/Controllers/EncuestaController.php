<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\Semestre;
use Illuminate\Http\Request;

class EncuestaController extends Controller
{
    public function indexEncuestaDocente(Request $request){
//        $validatedData = $request->validate([
//            'fecha_inicio' => 'required|date|before_or_equal:fecha_fin',
//            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
//            'nombre_encuesta' => 'required|string|max:255',
//            'tipo_encuesta' => 'required|in:docente,jefe_practica',
//            'disponible' => 'required|boolean',
//            'curso_id' => 'required|array|min:1',
//            'curso_id.*' => 'integer|exists:cursos,id',
//        ]);
        $encuestas = Encuesta::where('tipo_encuesta', 'docente')->select('id', 'fecha_inicio', 'fecha_fin')->get();
        return response()->json($encuestas);



        // Crear la encuesta
//        $encuesta = Encuesta::create($validatedData);

        //$idSemestre = Semestre::where('estado', 'activo')->pluck('id')->first();

//        if ($idSemestre) {
//            return response()->json([
//                'id' => $idSemestre
//            ]);
//        } else {
//            return response()->json(['message' => 'No se encontró ningún semestre activo'], 404);
//        }

//        // Asociar la encuesta a los horarios
//        $encuesta->horarios()->attach($validatedData['horario_id']);
//
//        return response()->json([
//            'message' => 'Encuesta creada exitosamente',
//            'encuesta' => $encuesta,
//        ], 201);

    }
    public function indexEncuestaJefePractica(Request $request){
        $encuestas = Encuesta::where('tipo_encuesta', 'jefe_practica')->select('id', 'fecha_inicio', 'fecha_fin')->get();
        return response()->json($encuestas);
    }
}
