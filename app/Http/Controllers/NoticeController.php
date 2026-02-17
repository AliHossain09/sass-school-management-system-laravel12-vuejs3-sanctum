<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    // List / Pagination
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Notice::where('school_id', $user->school_id);

        // Role based filtering
        if ($user->role === 'student') {

            $student = $user->student;

            $query->where(function ($q) use ($student) {
                $q->where('type', 'all')
                    ->orWhereJsonContains('class_ids', $student->class_id)
                    ->orWhereJsonContains('section_ids', $student->section_id);
            });
        }

        if ($user->role === 'teacher') {
            $query->where(function ($q) {
                $q->where('type', 'all')
                    ->orWhereNotNull('class_ids');
            });
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        $notices = $query->orderBy('publish_date', 'desc')->paginate(10);

        return response()->json($notices);
    }

    // Store Notice
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:all,class',
            'publish_date' => 'required|date',
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'exists:school_classes,id',
            'section_ids' => 'nullable|array',
            'section_ids.*' => 'exists:sections,id',
            'description' => 'nullable|string',
        ]);

        $notice = Notice::create([
            'school_id' => auth()->user()->school_id,
            'title' => $request->title,
            'type' => $request->type,
            'publish_date' => $request->publish_date,
            'class_ids' => $request->class_ids,
            'section_ids' => $request->section_ids,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'data' => $notice,
            'message' => 'Notice created successfully',
        ], 201);
    }

    // Update Notice
    public function update(Request $request, Notice $notice)
    {
        if ($notice->school_id !== auth()->user()->school_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'type' => 'nullable|in:all,class',
            'publish_date' => 'nullable|date',
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'exists:school_classes,id',
            'section_ids' => 'nullable|array',
            'section_ids.*' => 'exists:sections,id',
            'description' => 'nullable|string',
        ]);

        $notice->update(array_filter($request->only([
            'title', 'type', 'publish_date', 'class_ids', 'section_ids', 'description',
        ])));

        return response()->json([
            'success' => true,
            'data' => $notice,
            'message' => 'Notice updated successfully',
        ]);
    }

    // Delete Notice
    public function destroy(Notice $notice)
    {
        if ($notice->school_id !== auth()->user()->school_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notice deleted successfully',
        ]);
    }
}
