<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class EventNotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->where('type', 'App\\Notifications\\EventCreatedNotification')
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($notification) {
                $event = $notification->data['event'] ?? null;

                return [
                    'id' => $notification->id,
                    'is_read' => $notification->read_at !== null,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                    'event' => $event,
                ];
            });

        return response()->json([
            'data' => $notifications,
            'unread_count' => $notifications->where('is_read', false)->count(),
        ]);
    }

    public function markAsRead(Request $request, string $eventNotification)
    {
        /** @var DatabaseNotification|null $notification */
        $notification = $request->user()
            ->notifications()
            ->where('id', $eventNotification)
            ->where('type', 'App\\Notifications\\EventCreatedNotification')
            ->first();

        if (! $notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return response()->json([
            'message' => 'Notification marked as read',
        ]);
    }
}
