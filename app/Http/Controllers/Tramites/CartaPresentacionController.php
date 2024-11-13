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
        $query = CartaPresentacion::where('idEstudiante', $idEstudiante);

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




    public function create($idEstudiante)
    {
        $estudiante = Estudiante::findOrFail($idEstudiante);

        $horarios = $estudiante->horarios()->with('curso')->get();

        $cursos = $horarios->map(function ($horario) {
            return [
                'horario_id' => $horario->id,       // ID del horario
                'curso_id' => $horario->curso->id,  // ID del curso
                'curso_nombre' => $horario->curso->nombre, // Nombre del curso
                'curso_codigo' => $horario->curso->cod_curso  // Código del curso
            ];
        })->unique('curso_id'); // Remover duplicados por curso

        return response()->json([
            'estudiante' => $estudiante,
            'cursos' => $cursos,
        ]);
    }


    public function store(Request $request, $idEstudiante)
    {
        $request->validate([
            'idHorario' => 'required|exists:horarios,id',
            'Motivo' => 'required|string',
        ]);

        $carta = CartaPresentacion::create([
            'idEstudiante' => $idEstudiante,
            'idHorario' => $request->idHorario,
            'Motivo' => $request->Motivo,
            'Estado' => 'Pendiente',
        ]);

        return response()->json([
            'message' => 'Solicitud de carta de presentación creada con éxito.',
            'carta' => $carta
        ], 201);
    }
}
