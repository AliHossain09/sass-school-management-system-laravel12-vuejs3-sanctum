<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\EventService;

class EventController extends Controller
{
    public function __construct(protected EventService $eventService)
    {
    }

    // LIST EVENTS (WITH SEARCH & PAGINATION)

    public function index(Request $request): JsonResponse
    {
        $search = $request->input('search', '');
        if (! is_string($search)) {
            $search = '';
        }

        $schoolId = $request->user()->school_id ?? $request->input('school_id'); // if logged-in user's have school_id

        $events = $this->eventService->paginate(
            $schoolId,
            $search,
            (int) $request->input('per_page', 10)
        );

        return response()->json($events);
    }

    // Calendar only (NO pagination)
    public function calendarEvents(Request $request)
    {
        return $this->eventService->calendarEvents($request->user()->school_id);
    }

    // CREATE NEW EVENT

    public function store(StoreEventRequest $request): JsonResponse
    {
        $event = $this->eventService->store($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $event,
            'message' => 'Event created successfully',
        ], 201);
    }

    // UPDATE EVENT

    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        $updated = $this->eventService->update($request->user(), $event, $request->validated());

        if (! $updated) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $updated,
            'message' => 'Event updated successfully',
        ]);
    }

    //  DELETE EVENT

    public function destroy(Request $request, Event $event): JsonResponse
    {
        $ok = $this->eventService->destroy($request->user(), $event);

        if (! $ok) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully',
        ]);
    }
}
