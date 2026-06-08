<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'unreadCount' => $request->user()->unreadNotifications()->count(),
            'notifications' => $request->user()
                ->notifications()
                ->latest()
                ->limit(30)
                ->get()
                ->map(fn ($notification) => [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'readAt' => optional($notification->read_at)->toISOString(),
                    'createdAt' => optional($notification->created_at)->toISOString(),
                ]),
        ]);
    }

    public function markAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}
