<?php

namespace App\Http\Controllers;

use App\Models\Documentos\TemaTesis;
use Illuminate\Http\Request;

class TemaTesisController extends Controller
{
    public function indexPaginated(Request $request)
    {
        $estudiante_cod = $request->get('estudiante_cod', null);
        $especialidad_id = $request->get('especialidad_id', null);
        $estado = $request->get('estado', '');
        $per_page = $request->get('per_page', 10);
        $search = $request->get('search', '');

        $temas = TemaTesis::with('autores', 'especialidad')
            ->whereHas('autores', function ($query) use ($estudiante_cod) {
                $query->where('estudiantes.codigo', $estudiante_cod);
            })
            ->whereHas('especialidad', function ($query) use ($especialidad_id) {
                $query->where('especialidades.id', $especialidad_id);
            })
            ->where('estado', 'like', "%$estado%")
            ->where('titulo', 'like', "%$search%")
            ->paginate($per_page);

        return response()->json($temas, 200);
    }

    public function show($id)
    {
        $tema = TemaTesis::with('autores', 'especialidad', 'observaciones.usuario')->find($id);

        if (!$tema) {
            return response()->json(['message' => 'Tema de tesis no encontrado'], 404);
        }

        return response()->json($tema, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'resumen' => 'required|string',
            'documento_url' => 'string|max:255',
            'estado' => 'required|pendiente|aprobado|desaprobado',
            'especialidad_id' => 'required|exists:especialidades,id',
            'estadoJurado' => 'required|enviado|no enviado|pendiente|aprobado|desaprobado|vencido',
            'fechaEnvio' => 'required|date',
            'autores' => 'required|array',
            'autores.*.id' => 'required|exists:estudiantes,id',
            'asesores' => 'nullable|array',
            'asesores.*.id' => 'required|exists:docentes,id',
            'jurados' => 'nullable|array',
            'jurados.*.id' => 'required|exists:docentes,id',
            'observaciones' => 'nullable|array',
            'observaciones.*.usuario_id' => 'required|exists:usuarios,id',
            'observaciones.*.descripcion' => 'required|string',
            'observaciones.*.estado' => 'required|pendiente|aprobado|desaprobado',
            'observaciones.*.fechaEnvio' => 'required|date',
            'observaciones.*.documento_url' => 'nullable|string|max:255',
        ]);

        $tema = TemaTesis::create($request->all());

        return response()->json($tema, 201);
    }
}
