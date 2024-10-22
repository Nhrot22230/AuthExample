<?php

namespace App\Http\Controllers\Universidad;

use App\Http\Controllers\Controller;
use App\Models\Institucion;
use App\Models\Semestre;
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
        $semestre = Semestre::latest()->first();

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

        $semestre = new Semestre();
        $semestre->anho = $request->input('anho');
        $semestre->periodo = $request->input('periodo');
        $semestre->fecha_inicio = $request->input('fecha_inicio');
        $semestre->fecha_fin = $request->input('fecha_fin');
        $semestre->estado = $request->input('estado') ?? 'activo';

        $semestre->save();

        return response()->json($semestre, 201);
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
        $semestre = Semestre::find($id);

        if ($semestre) {
            $semestre->delete();
            return response()->json($semestre, 200);
        } else {
            return response()->json(['message' => 'Semestre no encontrado'], 404);
        }
    }
}
