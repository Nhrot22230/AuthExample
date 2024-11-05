<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\Estudiante;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function sendNotificationToEspecialidad(Request $request)
    {
        $request->validate([
            'especialidad_id' => 'required|exists:especialidades,id',
            'mensaje' => 'required|string',
        ]);

        // Obtener todos los estudiantes de la especialidad
        $estudiantes = Estudiante::where('especialidad_id', $request->especialidad_id)->get();

        // Enviar una notificación a cada estudiante
        foreach ($estudiantes as $estudiante) {
            Notificacion::create([
                'estudiante_id' => $estudiante->id,
                'especialidad_id' => $request->especialidad_id,
                'mensaje' => $request->mensaje,
                'leida' => false,
            ]);
        }

        return response()->json([
            'message' => 'Notificación enviada a todos los estudiantes de la especialidad.',
        ], 201);
    }

    public function getUnreadNotificationsForStudent($estudianteId)
{
    // Obtener las notificaciones no leídas para el estudiante
    $notificaciones = Notificacion::where('estudiante_id', $estudianteId)
                                    ->where('leida', false)
                                    ->get();

    // Extraer solo los mensajes en un array
    $mensajes = $notificaciones->pluck('mensaje');

    return response()->json([
        'mensajes' => $mensajes,
    ], 200);
}


    public function marcarComoLeida($id)
    {
        $notificacion = Notificacion::findOrFail($id);
        $notificacion->leida = true;
        $notificacion->save();

        return response()->json([
            'message' => 'Notificación marcada como leída.',
            'notificacion' => $notificacion,
        ], 200);
    }
}
