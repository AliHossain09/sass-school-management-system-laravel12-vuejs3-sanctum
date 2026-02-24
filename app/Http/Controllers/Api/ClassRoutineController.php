<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassRoutine;
use Illuminate\Http\Request;

class ClassRoutineController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = \App\Models\ClassRoutine::where('school_id', $user->school_id);

        // STUDENT → Own class & section only

        if ($user->role === 'student') {

            $student = \App\Models\Student::where('user_id', $user->id)->first();

            if (! $student) {
                return response()->json([
                    'message' => 'Student record not found',
                ], 404);
            }

            $query->where('class_id', $student->class_id)
                ->where('section_id', $student->section_id);
        }

        // TEACHER → Own routines + optional class & subject filter

        elseif ($user->role === 'teacher') {

            $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();

            if (! $teacher) {
                return response()->json([
                    'message' => 'Teacher record not found',
                ], 404);
            }

            $query->where('teacher_id', $teacher->id);

            // Optional Filters
            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }

            if ($request->filled('subject_id')) {
                $query->where('subject_id', $request->subject_id);
            }
        }

        // HEADMASTER → Full filter control

        elseif ($user->role === 'headmaster') {

            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }

            if ($request->filled('section_id')) {
                $query->where('section_id', $request->section_id);
            }

            if ($request->filled('teacher_id')) {
                $query->where('teacher_id', $request->teacher_id);
            }

            if ($request->filled('subject_id')) {
                $query->where('subject_id', $request->subject_id);
            }
        }

        // Eager Load + Sorting

        $query->with(['schoolClass', 'section', 'subject', 'teacher'])
            ->orderBy('start_time');

        // Pagination Logic

        if ($request->boolean('all')) {
            return response()->json($query->get());
        }

        $perPage = $request->get('per_page', 10);

        return response()->json(
            $query->paginate($perPage)
        );
    }

    // Store a routine entry
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_break' => 'boolean',
            'other_days' => 'nullable|array',
            'other_days.*' => 'in:Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday,Friday',
            'class_room' => 'nullable|string|max:100',
        ]);

        $validated['school_id'] = $user->school_id;

        $routine = ClassRoutine::create($validated);

        return response()->json([
            'success' => true,
            'data' => $routine,
            'message' => 'Class routine created successfully',
        ], 201);
    }

    // Update a routine entry
    public function update(Request $request, ClassRoutine $classRoutine)
    {
        if ($classRoutine->school_id !== $request->user()->school_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'class_id' => 'sometimes|exists:school_classes,id',
            'section_id' => 'sometimes|exists:sections,id',
            'subject_id' => 'sometimes|exists:subjects,id',
            'teacher_id' => 'sometimes|exists:teachers,id',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'is_break' => 'sometimes|boolean',
            'other_days' => 'nullable|array',
            'other_days.*' => 'in:Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday,Friday',
            'class_room' => 'nullable|string|max:100',
        ]);

        $classRoutine->update($validated);

        return response()->json([
            'success' => true,
            'data' => $classRoutine,
            'message' => 'Class routine updated successfully',
        ]);
    }

    // Delete a routine entry
    public function destroy(Request $request, ClassRoutine $classRoutine)
    {
        if ($classRoutine->school_id !== $request->user()->school_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $classRoutine->delete();

        return response()->json([
            'success' => true,
            'message' => 'Class routine deleted successfully',
        ]);
    }
}
