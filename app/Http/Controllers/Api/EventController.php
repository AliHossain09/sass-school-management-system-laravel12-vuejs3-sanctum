<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    
     // LIST EVENTS (WITH SEARCH & PAGINATION)
     
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $schoolId = $request->user()->school_id ?? $request->input('school_id'); // if logged-in user's have school_id 

        $events = Event::query()
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->when($search, fn($q) => $q->where('title', 'like', "%$search%"))
            ->orderBy('start_date', 'desc')
            ->paginate($request->input('per_page', 10));

        return response()->json($events);
    }


     // CREATE NEW EVENT

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $event = Event::create([
            'title' => $request->title,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'school_id' => auth()->user()->school_id, // secure, from logged-in user
        ]);

        return response()->json([
            'success' => true,
            'data' => $event,
            'message' => 'Event created successfully',
        ], 201);
    }

    
     // UPDATE EVENT
     
    public function update(Request $request, Event $event)
    {
        // Security: ensure same school
        if ($event->school_id !== auth()->user()->school_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $event->update(array_filter($request->only(['title', 'start_date', 'end_date'])));

        return response()->json([
            'success' => true,
            'data' => $event,
            'message' => 'Event updated successfully',
        ]);
    }

    
    //  DELETE EVENT
     
    public function destroy(Event $event)
    {
        // Security: ensure same school
        if ($event->school_id !== auth()->user()->school_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully',
        ]);
    }
}
