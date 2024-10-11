<?php

namespace App\Http\Controllers\Universidad;

use App\Http\Controllers\Controller;
use App\Models\Semestre;
use Illuminate\Http\Request;

class SemestreController extends Controller
{
    //
    public function index(){
        $per_page = 10;
        $semestres = Semestre::orderBy('fecha_inicio', 'desc')->paginate($per_page);

        return response()->json($semestres, 200);
    }

    public function indexAll(){
        $semestres = Semestre::all();

        return response()->json($semestres, 200);
    }

    public function update(Request $request, $id){
        $request->validate([
            'año' => 'required|string',
            'periodo' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'estado' => 'nullable|string',
        ]);

        $semestre = Semestre::find($id);

        if($semestre){
            $semestre->anho = $request->input('año');
            $semestre->periodo = $request->input('periodo');
            $semestre->fecha_inicio = $request->input('fecha_inicio');
            $semestre->fecha_fin = $request->input('fecha_fin');
            if ($request->has('estado')) {
                $semestre->estado = $request->input('estado');
            }

            $semestre->save();
            return response()->json($semestre, 200);
        }else{
            return response()->json(['message' => 'Semestre no encontrado'], 404);
        }
    }

    public function store(Request $request){
        $request->validate([
            'año' => 'required|string',
            'periodo' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'estado' => 'nullable|string',
        ]);

        $semestre = new Semestre();
        $semestre->anho = $request->input('año');
        $semestre->periodo = $request->input('periodo');
        $semestre->fecha_inicio = $request->input('fecha_inicio');
        $semestre->fecha_fin = $request->input('fecha_fin');
        $semestre->estado = $request->input('estado') ?? 'activo';    

        $semestre->save();

        return response()->json($semestre, 201);
    }

    public function show($id){
        $semestre = Semestre::find($id);

        if($semestre){
            return response()->json($semestre, 200);
        }else{
            return response()->json(['message' => 'Semestre no encontrado'], 404);
        }
    }

    public function destroy($id){
        $semestre = Semestre::find($id);

        if($semestre){
            $semestre->delete();
            return response()->json($semestre, 200);
        }else{
            return response()->json(['message' => 'Semestre no encontrado'], 404);
        }
    }
}
