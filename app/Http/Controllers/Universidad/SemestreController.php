<?php

namespace App\Http\Controllers\Universidad;

use App\Http\Controllers\Controller;
use App\Models\Universidad\Especialidad;
use App\Models\Tramites\PedidoCursos;
use App\Models\Universidad\Semestre;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class SemestreController extends Controller
{
    //
    public function index()
    {
        $per_page = request('per_page', 10);
        $since = request('since', null);
        $until = request('until', null);

        // Modifica la ordenación para que sea por anho y luego por periodo
        $semestres = Semestre::orderBy('anho', 'desc')
            ->orderBy('periodo', 'desc')  // Segundo criterio de ordenación: periodo
            ->when($since, function ($query) use ($since) {
                return $query->where('fecha_inicio', '>=', $since);
            })
            ->when($until, function ($query) use ($until) {
                return $query->where('fecha_inicio', '<=', $until);
            })
            ->paginate($per_page);

        return response()->json($semestres, 200);
    }


    public function indexAll()
    {
        $semestres = Semestre::orderBy('fecha_inicio', 'desc')->get();

        return response()->json($semestres, 200);
    }

    public function getLastSemestre()
    {
        // Obtener el semestre con el año más reciente y el periodo más reciente
        $semestre = Semestre::orderBy('anho', 'desc')
            ->orderBy('periodo', 'desc')
            ->first();

        if (!$semestre) {
            return response()->json(['message' => 'No se encontró el último semestre'], 404);
        }

        if ($semestre->periodo == 2) {
            $semestre->anho =  $semestre->anho + 1;
            $semestre->periodo = 0;
        } else {
            $semestre->periodo =  $semestre->periodo + 1;
        }


        $response = [
            'anho' => $semestre->anho,
            'periodo' => $semestre->periodo,
            'fecha_inicio' => $semestre->fecha_inicio,
            'fecha_fin' => $semestre->fecha_fin,
        ];

        return response()->json($response, 200);
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'anho' => 'required|string',
            'periodo' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'estado' => 'nullable|string',
        ]);

        $semestre = Semestre::find($id);

        if ($semestre) {
            $semestre->anho = $request->input('anho');
            $semestre->periodo = $request->input('periodo');
            $semestre->fecha_inicio = $request->input('fecha_inicio');
            $semestre->fecha_fin = $request->input('fecha_fin');
            if ($request->has('estado')) {
                $semestre->estado = $request->input('estado');
            }

            $semestre->save();
            return response()->json($semestre, 200);
        } else {
            return response()->json(['message' => 'Semestre no encontrado'], 404);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'anho' => 'required|string',
            'periodo' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'estado' => 'nullable|string',
        ]);

        $semestre = Semestre::create([
            'anho' => $request->input('anho'),
            'periodo' => $request->input('periodo'),
            'fecha_inicio' => $request->input('fecha_inicio'),
            'fecha_fin' => $request->input('fecha_fin'),
            'estado' => $request->input('estado') ?? 'activo',
        ]);

        $especialidades = Especialidad::all();
        $pedidosCreados = [];
        $numeroPedidosCreados = 0;

        foreach ($especialidades as $especialidad) {
            $planEstudioActivo = $especialidad->planEstudioActivo();

            if ($planEstudioActivo) {
                $pedidoExistente = PedidoCursos::where('semestre_id', $semestre->id)
                    ->where('plan_estudio_id', $planEstudioActivo->id)
                    ->exists();

                if (!$pedidoExistente) {
                    $pedidoCurso = PedidoCursos::create([
                        'estado' => 'No Enviado',
                        'observaciones' => null,
                        'enviado' => false,
                        'semestre_id' => $semestre->id,
                        'facultad_id' => $especialidad->facultad_id,
                        'especialidad_id' => $especialidad->id,
                        'plan_estudio_id' => $planEstudioActivo->id,
                    ]);

                    $pedidosCreados[] = $pedidoCurso;
                    $numeroPedidosCreados++;
                }
            }
        }

        Log::info('Se crearon ' . $numeroPedidosCreados . ' pedidos de curso para el semestre ' . $semestre->id);

        return response()->json([
            'semestre' => $semestre,
            'pedidos_cursos' => $pedidosCreados,
        ], 201);
    }


    public function show($id)
    {
        $semestre = Semestre::find($id);

        if ($semestre) {
            return response()->json($semestre, 200);
        } else {
            return response()->json(['message' => 'Semestre no encontrado sdf'], 404);
        }
    }

    public function destroy($id)
    {
        //Log::info("Llamado a destroy con ID: {$id}");
        $semestre = Semestre::find($id);

        if ($semestre) {
            $semestre->delete();
            return response()->json($semestre, 200);
        } else {
            return response()->json(['message' => 'Semestre no encontrado'], 404);
        }
    }

    public function destroyMultiple(Request $request)
    {
        //Log::info("Llamado a destroyMultiple con IDs: " . json_encode($request->input('ids')));
        $ids = $request->input('ids'); // Recibir una lista de IDs

        if ($ids && is_array($ids)) {
            // Eliminar todos los semestres que coincidan con los IDs
            Semestre::whereIn('id', $ids)->delete();

            return response()->json(['message' => 'Semestres eliminados con éxito'], 200);
        } else {
            return response()->json(['message' => 'IDs inválidos'], 400);
        }
    }
    public function obtenerSemestreActual()
    {
        $semestreActual = Semestre::where('estado', 'activo')
            ->orderBy('anho', 'desc')
            ->orderBy('periodo', 'desc')
            ->first(['id', 'anho', 'periodo']); // Selecciona solo los campos específicos

        if (!$semestreActual) {
            return response()->json(['error' => 'No se encontró un semestre activo'], 404);
        }

        return response()->json($semestreActual);
    }


}
