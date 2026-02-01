<?php

namespace App\Http\Controllers\Api;

use App\Models\Subject;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SubjectController extends Controller
{
    // List Subjects
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search  = $request->get('search');

        $query = Subject::with('schoolClass', 'teacher')
            ->where('school_id', auth()->user()->school_id);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $subjects = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $subjects->items(),
            'meta' => [
                'current_page' => $subjects->currentPage(),
                'from' => $subjects->firstItem(),
                'to' => $subjects->lastItem(),
                'per_page' => $subjects->perPage(),
                'total' => $subjects->total(),
                'last_page' => $subjects->lastPage(),
            ],
        ]);
    }

    // Create Subject
    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:core,elective,optional',
            'code' => 'nullable|string|max:50',
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $subject = Subject::create([
            'name' => $request->name,
            'class_id' => $request->class_id,
            'type' => $request->type,
            'code' => $request->code,
            'teacher_id' => $request->teacher_id,
            'school_id' => auth()->user()->school_id,
        ]);

        return response()->json([
            'success' => true,
            'data' => $subject,
            'message' => 'Subject created successfully',
        ], 201);
    }

    // Update Subject
    public function update(Request $request, $id)
    {
        $subject = Subject::where('school_id', auth()->user()->school_id)
            ->findOrFail($id);

        $request->validate([
            'class_id' => 'nullable|exists:school_classes,id',
            'name' => 'nullable|string|max:255',
            'type' => 'nullable|in:core,elective,optional',
            'code' => 'nullable|string|max:50',
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $subject->update(array_filter($request->only(['name','class_id','type','code','teacher_id']), fn($v) => $v !== null));

        return response()->json([
            'success' => true,
            'data' => $subject,
            'message' => 'Subject updated successfully',
        ]);
    }

    // Delete Subject
    public function destroy($id)
    {
        $subject = Subject::where('school_id', auth()->user()->school_id)
            ->findOrFail($id);

        $subject->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subject deleted successfully',
        ]);
    }
}
