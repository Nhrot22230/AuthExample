<?php

namespace App\Http\Controllers\Matricula;

use App\Models\Matricula\Horario;
use App\Models\Matricula\HorarioActividad;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class HorarioActividadController extends Controller
{
    // Mostrar todas las actividades de un horario
    public function index($horarioId)
{
    // Buscar el horario con el ID y cargar la relación 'actividades'
    $horario = Horario::with('actividades')->find($horarioId);

    // Verificar si el horario existe
    if (!$horario) {
        return response()->json([
            'message' => 'Horario no encontrado.'
        ], 404);
    }

    // Verificar si el horario tiene actividades
    if ($horario->actividades->isEmpty()) {
        return response()->json([
            'message' => 'Este horario no tiene actividades asignadas.'
        ], 404);
    }

    // Devolver las actividades
    return response()->json([
        'actividades' => $horario->actividades
    ]);
}

    // Crear una nueva actividad para un horario
    public function store(Request $request, $horarioId)
    {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'actividades' => 'required|array', // El campo actividades debe ser un array
            'actividades.*.actividad' => 'required|string',
            'actividades.*.duracion_semanas' => 'required|integer',
            'actividades.*.semana_ocurre' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Obtener el horario
        $horario = Horario::findOrFail($horarioId);

        // Crear todas las actividades asociadas al horario
        $actividades = $request->input('actividades'); // Obtener el array de actividades

        foreach ($actividades as $actividad) {
            $horarioActividad = new HorarioActividad();
            $horarioActividad->horario_id = $horario->id;
            $horarioActividad->actividad = $actividad['actividad'];
            $horarioActividad->duracion_semanas = $actividad['duracion_semanas'];
            $horarioActividad->semana_ocurre = $actividad['semana_ocurre'];
            $horarioActividad->save();
        }

        return response()->json([
            'message' => 'Actividades creadas exitosamente',
            'actividades' => $actividades
        ], 201);
    }

    // Actualizar una actividad de un horario
    public function update(Request $request, $horarioId, $actividadId)
    {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'actividad' => 'required|string',
            'duracion_semanas' => 'required|integer',
            'semana_ocurre' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Obtener el horario y la actividad
        $horario = Horario::findOrFail($horarioId);
        $horarioActividad = HorarioActividad::findOrFail($actividadId);

        // Actualizar los valores de la actividad
        $horarioActividad->actividad = $request->actividad;
        $horarioActividad->duracion_semanas = $request->duracion_semanas;
        $horarioActividad->semana_ocurre = $request->semana_ocurre;
        $horarioActividad->save();

        // Responder con éxito
        return response()->json([
            'message' => 'Actividad actualizada exitosamente',
            'actividad' => $horarioActividad
        ]);
    }

    // Eliminar una actividad de un horario
    public function destroy($horarioId, $actividadId)
    {
        // Obtener el horario y la actividad
        $horario = Horario::findOrFail($horarioId);
        $horarioActividad = HorarioActividad::findOrFail($actividadId);

        // Eliminar la actividad
        $horarioActividad->delete();

        // Responder con éxito
        return response()->json([
            'message' => 'Actividad eliminada exitosamente'
        ]);
    }

    // Verificar si el horario tiene actividades
    public function verificarActividades($horarioId)
{
    // Buscar el horario por su ID
    $horario = Horario::find($horarioId);

    // Verificar si el horario no existe
    if (!$horario) {
        return response()->json([
            'message' => 'Horario no encontrado.'
        ], 404);
    }

    // Cargar explícitamente la relación 'actividades'
    $horario->load('actividades');

    // Verificar si existen actividades asociadas al horario
    $actividades = $horario->actividades;

    // Si no hay actividades
    if ($actividades->isEmpty()) {
        return response()->json([
            'message' => 'Este horario no tiene actividades asignadas. Solicite al profesor que las ingrese.'
        ], 202);
    }

    // Si hay actividades
    return response()->json([
        'message' => 'Este horario tiene actividades asignadas.',
        'actividades' => $actividades
    ]);
}

    // Solicitar al profesor que agregue actividades (solo si no hay actividades)
    public function solicitarActividades($horarioId)
    {
        // Obtener el horario
        $horario = Horario::findOrFail($horarioId);

        // Verificar si el horario ya tiene actividades
        if ($horario->actividades->isEmpty()) {
            // Aquí podrías agregar lógica para enviar un correo al profesor
            // o crear una solicitud para el profesor para que agregue actividades

            return response()->json([
                'message' => 'Se ha enviado una solicitud al profesor para que agregue actividades.'
            ]);
        }

        return response()->json([
            'message' => 'El horario ya tiene actividades asignadas.'
        ]);
    }
}