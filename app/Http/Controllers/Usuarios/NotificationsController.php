<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use App\Models\Usuarios\Notifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationsController extends Controller
{
    public function notifications(Request $request)
    {
        $request->validate([
            'authUser' => 'required'
        ]);

        $authUser = $request->authUser;

        try {
            $notifications = $authUser->notifications()->get();

            Log::channel('audit-log')->info('Notificaciones obtenidas', [
                'auth_user' => $authUser->id,
                'notifications_count' => $notifications->count(),
            ]);

            return response()->json($notifications);
        } catch (\Exception $e) {
            Log::channel('errors')->error('Error al obtener notificaciones', [
                'auth_user' => $authUser->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Error al obtener notificaciones'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'authUser' => 'required',
            'status' => 'required|boolean',
        ]);

        $authUser = $request->authUser;

        try {
            $notification = $authUser->notifications()->find($id);

            if (!$notification) {
                Log::channel('errors')->error('Notificación no encontrada para actualizar', [
                    'auth_user' => $authUser->id,
                    'notification_id' => $id,
                ]);
                return response()->json(['message' => 'Notification not found'], 404);
            }

            $notification->update($request->only('status'));

            Log::channel('audit-log')->info('Notificación actualizada', [
                'auth_user' => $authUser->id,
                'notification_id' => $id,
                'status' => $request->status,
            ]);

            return response()->json($notification);
        } catch (\Exception $e) {
            Log::channel('errors')->error('Error al actualizar notificación', [
                'auth_user' => $authUser->id,
                'notification_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Error al actualizar la notificación'], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $request->validate([
            'authUser' => 'required',
        ]);

        $authUser = $request->authUser;

        try {
            $notification = $authUser->notifications()->find($id);

            if (!$notification) {
                Log::channel('errors')->error('Notificación no encontrada para eliminación', [
                    'auth_user' => $authUser->id,
                    'notification_id' => $id,
                ]);
                return response()->json(['message' => 'Notification not found'], 404);
            }

            $notification->delete();

            Log::channel('audit-log')->info('Notificación eliminada', [
                'auth_user' => $authUser->id,
                'notification_id' => $id,
            ]);

            return response()->json($notification);
        } catch (\Exception $e) {
            Log::channel('errors')->error('Error al eliminar notificación', [
                'auth_user' => $authUser->id,
                'notification_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Error al eliminar la notificación'], 500);
        }
    }

    public function notifyToUsers(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:30',
            'message' => 'required|string|max:255',
            'message_type' => 'required|in:info,warning,error,success',
            'usuarios' => 'required|array',
            'usuarios.*' => 'exists:usuarios,id',
        ]);

        $authUser = $request->authUser;

        try {
            $notifications = [];

            foreach ($request->usuarios as $usuario_id) {
                $notification = Notifications::create([
                    'title' => $request->title,
                    'message' => $request->message,
                    'message_type' => $request->message_type,
                    'usuario_id' => $usuario_id,
                    'status' => false,
                ]);
                $notifications[] = $notification;
            }

            Log::channel('audit-log')->info('Notificaciones enviadas a usuarios', [
                'auth_user' => $authUser->id,
                'notified_users' => $request->usuarios,
                'notifications_count' => count($notifications),
                'title' => $request->title,
                'message_type' => $request->message_type,
            ]);

            return response()->json($notifications);
        } catch (\Exception $e) {
            Log::channel('errors')->error('Error al enviar notificaciones', [
                'auth_user' => $authUser->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Error al enviar notificaciones'], 500);
        }
    }
}
