<?php

namespace App\Http\Controllers;

use App\Models\TemaDeTesis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TemaDeTesisController extends Controller
{
    // Método para listar temas de tesis con filtros y paginación
    public function indexPaginated(Request $request)
    {
        $search = $request->input('search', '');
        $per_page = $request->input('per_page', 10);
        $facultad_id = $request->input('facultad_id', null);
        $especialidad_id = $request->input('especialidad_id', null);

        $query = TemaDeTesis::with([
                'especialidad',
                'jurados.usuario',      // Cargar los datos del usuario de cada jurado
                'asesores.usuario',     // Cargar los datos del usuario de cada asesor
                'estudiantes.usuario'   // Cargar los datos del usuario de cada estudiante
            ])
            ->when($facultad_id, function ($query) use ($facultad_id) {
                $query->whereHas('especialidad', function ($q) use ($facultad_id) {
                    $q->where('facultad_id', $facultad_id);
                });
            })
            ->when($especialidad_id, function ($query) use ($especialidad_id) {
                $query->where('especialidad_id', $especialidad_id);
            })
            ->where(function ($query) use ($search) {
                $query->where('titulo', 'like', "%$search%")
                    ->orWhere('resumen', 'like', "%$search%");
            });

        $temasDeTesis = $query->paginate($per_page);

        return response()->json($temasDeTesis, 200);
    }

    // Método para mostrar un tema de tesis específico
    public function show($id)
    {
        $temaDeTesis = TemaDeTesis::with([
                'especialidad',
                'jurados.usuario',      // Cargar los datos del usuario de cada jurado
                'asesores.usuario',     // Cargar los datos del usuario de cada asesor
                'estudiantes.usuario',  // Cargar los datos del usuario de cada estudiante
                'observaciones'
            ])
            ->findOrFail($id);

        return response()->json($temaDeTesis, 200);
    }

    // Método para actualizar el estado y estado del jurado de un tema de tesis
    public function update(Request $request, $id)
    {
        $request->validate([
            'estado' => 'nullable|in:aprobado,pendiente,desaprobado',
            'estado_jurado' => 'nullable|in:enviado,no enviado,aprobado,pendiente,desaprobado,vencido',
            'jurados' => 'nullable|array',
            'jurados.*' => 'exists:docentes,id',
        ]);

        $temaDeTesis = TemaDeTesis::findOrFail($id);

        // Actualización del estado y estado_jurado
        $temaDeTesis->update($request->only('estado', 'estado_jurado'));

        // Actualización de jurados, si se proveen
        if ($request->has('jurados')) {
            $temaDeTesis->jurados()->sync($request->jurados);
        }

        return response()->json(['message' => 'Tema de Tesis actualizado exitosamente', 'tema' => $temaDeTesis], 200);
    }
}
