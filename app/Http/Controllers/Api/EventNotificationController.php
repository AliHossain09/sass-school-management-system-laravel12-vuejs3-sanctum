<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EventNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventNotificationController extends Controller
{
    public function __construct(protected EventNotificationService $eventNotificationService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $this->eventNotificationService->latestForUser($request->user())
        );
    }

    public function markAsRead(Request $request, string $eventNotification): JsonResponse
    {
        $ok = $this->eventNotificationService->markAsRead($request->user(), $eventNotification);

        if (! $ok) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        return response()->json([
            'message' => 'Notification marked as read',
        ]);
    }
}
