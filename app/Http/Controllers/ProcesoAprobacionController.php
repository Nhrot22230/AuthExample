<?php

namespace App\Http\Controllers;

use App\Models\Tramites\ProcesoAprobacion;
use Illuminate\Http\Request;

class ProcesoAprobacionController extends Controller
{
    /**
     * Listar los procesos de aprobación de un tema de tesis.
     *
     * @param int $idTesis
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexByTema($idTesis)
    {
        try {
            $procesos = ProcesoAprobacion::where('tema_tesis_id', $idTesis)->get();

            return response()->json($procesos, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los procesos de aprobación',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function show($idProceso)
    {
        $proceso = ProcesoAprobacion::with('fases')->findOrFail($idProceso);

        return response()->json([
            'id' => $proceso->id,
            'titulo' => $proceso->titulo,
            'resumen' => $proceso->resumen,
            'estado_proceso' => $proceso->estado_proceso,
            'fases' => $proceso->fases->map(function ($fase) {
                return [
                    'fase' => $fase->fase,
                    'estado_fase' => $fase->estado_fase,
                    'observacion' => $fase->observacion,
                    'created_at' => $fase->created_at,
                ];
            }),
        ]);
    }
}
