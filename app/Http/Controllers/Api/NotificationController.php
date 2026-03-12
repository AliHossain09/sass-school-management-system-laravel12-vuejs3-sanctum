<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $kind = $request->get('kind');
        $onlyUnread = filter_var($request->get('unread'), FILTER_VALIDATE_BOOL);

        $query = $request->user()->notifications()->latest();

        if ($onlyUnread) {
            $query->whereNull('read_at');
        }

        if ($kind) {
            $query->where('data->kind', $kind);
        }

        $notifications = $query->limit(50)->get()->map(function (DatabaseNotification $n) {
            return [
                'id' => $n->id,
                'type' => $n->type,
                'data' => $n->data,
                'read_at' => $n->read_at,
                'created_at' => $n->created_at,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        /** @var DatabaseNotification $notification */
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
        ]);
    }
}

