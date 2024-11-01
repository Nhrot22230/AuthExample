<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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

    public function getAll()
    {
        // Carga ansiosa para obtener los datos relacionados
        $matriculas = MatriculaAdicional::with(['estudiante.usuario', 'especialidad'])->get();
        return response()->json($matriculas);
    }

    public function getByEspecialidad($id)
    {
            // Cargar las solicitudes filtradas por especialidad, junto con las relaciones necesarias
            $matriculas = MatriculaAdicional::with([
                'estudiante.usuario', 
                'especialidad', 
                'curso', 
                'horario', 
                'horario.docentes.usuario:id,nombre,apellido_paterno' // Incluye los docentes a través de horario
            ])
            ->where('especialidad_id', $id)
            ->get();

            // Procesar los resultados para adaptarlos a la estructura deseada
            $result = $matriculas->map(function ($matricula) {
                $estudiante = $matricula->estudiante;
                return [
                    'codigo' => $estudiante->codigoEstudiante, // Código del estudiante
                    'nombres' => $estudiante->usuario->nombre . ' ' . $estudiante->usuario->apellido_paterno . ' ' . $estudiante->usuario->apellido_materno, // Nombres completos
                    'ultimaModificacion' => Carbon::parse($matricula->updated_at)->format('d-m-Y'), // Formato día-mes-año
                    'curso' => $matricula->curso->nombre, // Nombre del curso
                    'horario' => $matricula->horario->nombre, // Nombre del horario
                    
                    'estado' => $matricula->estado, // Estado
                ];
            });

            return response()->json($result);
    }
    
    public function getByEstudiante($estudianteId)
    {
        $matriculas = MatriculaAdicional::with([
            'estudiante.usuario', 
            'especialidad', 
            'curso', 
            'horario', 
            'horario.docentes.usuario:id,nombre,apellido_paterno',// Incluye los docentes a través de horario
        ])
        ->where('estudiante_id', $estudianteId)
        ->get();
    
        $result = $matriculas->map(function ($matricula) {
        return [
            'clave' => $matricula->curso->cod_curso,
            'curso' => $matricula->curso->nombre,
            'horario' => $matricula->horario->nombre,
            'profesor' => isset($matricula->horario->docentes->first()->usuario) 
    ? $matricula->horario->docentes->first()->usuario->nombre . ' ' . $matricula->horario->docentes->first()->usuario->apellido_paterno : 'Sin Profesor',

            'ultimaModificacion' => Carbon::parse($matricula->updated_at)->format('d-m-Y'), // Formato día-mes-año
            'estado' => $matricula->estado,
        ];
    });

    return response()->json($result);
    }

    public function getByFacultad($facultadId)
{
    $matriculas = MatriculaAdicional::with([
        'estudiante.usuario',
        'especialidad', // Asegúrate de incluir especialidad
        'curso',
        'horario',
        'horario.docentes.usuario',
    ])
    ->whereHas('especialidad', function ($query) use ($facultadId) {
        $query->where('facultad_id', $facultadId);
    })
    ->get();

    $result = $matriculas->map(function ($matricula) {
        return [
            'codigo' => $matricula->estudiante->codigoEstudiante, // Código del estudiante
            'nombres' => $matricula->estudiante->usuario->nombre . ' ' . $matricula->estudiante->usuario->apellido_paterno, // Nombre completo
            'ultimaModificacion' => $matricula->updated_at->format('d/m/Y'), // Formato de fecha
            'curso' => $matricula->curso->nombre, // Nombre del curso
            'especialidad' => $matricula->especialidad->nombre, // Nombre de la especialidad
            'estado' => $matricula->estado, // Estado de la solicitud
        ];
    });

    return response()->json($result);
}
}