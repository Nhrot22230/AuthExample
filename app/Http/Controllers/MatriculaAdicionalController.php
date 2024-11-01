<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\MatriculaAdicional; // Asegúrate de importar tu modelo
use Illuminate\Support\Facades\Validator;

class MatriculaAdicionalController extends Controller
{
    public function store(Request $request)
    {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'codigoEstudiante' => 'required|string',
            'clase_especialidad' => 'required|string',
            'motivo' => 'required|string',
            'justificacion' => 'required|string',
            'motivo_rechazo' => 'nullable|string',
        ]);

        // Si la validación falla, retorna un error
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Crear una nueva matrícula adicional
        $matricula = MatriculaAdicional::create([
            'estudiante_id' => $request->codigoEstudiante,
            'especialidad_id' => $request->clase_especialidad,
            'motivo' => $request->motivo,
            'justificacion' => $request->justificacion,
            'estado' => 'pendiente',
            'motivo_rechazo' => $request->motivo_rechazo,
        ]);

        // Retornar la respuesta
        return response()->json($matricula, 201);
    }
}