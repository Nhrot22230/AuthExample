<?php

namespace App\Http\Controllers\Tramites;
use App\Http\Controllers\Controller;
use App\Models\Usuarios\Estudiante;
use App\Models\Solicitudes\CartaPresentacion;

use Illuminate\Http\Request;

class CartaPresentacionController extends Controller
{
    //
    public function index(Request $request, $idEstudiante)
    {
        $estado = $request->input('estado', null);

        $query = CartaPresentacion::where('estudiante_id', $idEstudiante);

        if ($estado) {
            $query->where('Estado', $estado);
        }

        $cartas = $query->get();

        if ($cartas->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron cartas de presentación para este estudiante con el estado especificado.'
            ], 404);
        }

        return response()->json($cartas);
    }

    public function indexDocente($idDocente, Request $request)
    {
        $estado = $request->input('estado', null);

        $query = CartaPresentacion::whereHas('horario', function($q) use ($idDocente) {
            $q->whereHas('docentes', function($q) use ($idDocente) {
                $q->where('docente_id', $idDocente);
            });
        });
        
        if ($estado) {
            $query->where('Estado', $estado);
        }

        $cartas = $query->get();

        if ($cartas->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron cartas de presentación para este docente con el estado especificado.'
            ], 404);
        }

        return response()->json($cartas);
    }



    public function create($idEstudiante)
    {
        $estudiante = Estudiante::findOrFail($idEstudiante);

        $horarios = $estudiante->horarios()->with('curso')->get();

        $cursos = $horarios->map(function ($horario) {
            return [
                'horario_id' => $horario->id,
                'curso_id' => $horario->curso->id,
                'curso_nombre' => $horario->curso->nombre,
                'curso_codigo' => $horario->curso->cod_curso
            ];
        })->unique('curso_id');

        return response()->json([
            'estudiante' => $estudiante,
            'cursos' => $cursos,
        ]);
    }


    public function store(Request $request, $idEstudiante)
    {
        $request->validate([
            'horario_id' => 'required|exists:horarios,id',
            'motivo' => 'required|string',
        ]);
        
        $carta = CartaPresentacion::create([
            'estudiante_id' => $idEstudiante,
            'horario_id' => $request->horario_id,
            'motivo' => $request->motivo,
            'estado' => 'Pendiente',
        ]);

        return response()->json([
            'message' => 'Solicitud de carta de presentación creada con éxito.',
            'carta' => $carta
        ], 201);
    }


}
