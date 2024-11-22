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

    public function indexByArea($idArea)
    {
        try {
            $procesos = ProcesoAprobacion::with([
                'temaTesis' => function ($query) use ($idArea) {
                    $query->where('area_id', $idArea);
                }
            ])->where('fases_aprobadas', '=', 1)
                ->get();

            return response()->json($procesos, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los procesos de aprobación' . $e->getMessage(),
            ], 500);
        }
    }

    public function indexByEspecialidad($idEspecialidad)
    {
        try {
            $procesos = ProcesoAprobacion::with([
                'temaTesis.area' => function ($query) use ($idEspecialidad) {
                    $query->where('especialidad_id', $idEspecialidad);
                }
            ])->where('fases_aprobadas', '=', 2)
                ->get();

            return response()->json($procesos, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los procesos de aprobación',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function indexByAsesor(Request $request) {
        $asesor = $request->authUser;
    
        $procesos = ProcesoAprobacion::whereHas('temaTesis.asesores', function ($query) use ($asesor) {
            $query->where('usuario_id', $asesor->id);
        })
        ->with(['temaTesis.asesores', 'temaTesis.autores', 'temaTesis.area'])
        ->get();
    
        return response()->json($procesos, 200);
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
