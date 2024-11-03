<?php

namespace App\Http\Controllers;

use App\Models\Notifications;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function notifications(Request $request)
    {
        $request->validate([
            'authUser' => 'required'
        ]);

        $notifications = $request->authUser->notifications()->get();
        return response()->json($notifications);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'authUser' => 'required',
            'status' => 'required|boolean',
        ]);

        $notification = $request->authUser->notifications()->find($id);
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->update($request->all());
        return response()->json($notification);
    }

    public function destroy(Request $request, $id)
    {
        $request->validate([
            'authUser' => 'required',
        ]);

        $notification = $request->authUser->notifications()->find($id);
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->delete();
        return response()->json($notification);
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

        return response()->json($notifications);
    }
}
