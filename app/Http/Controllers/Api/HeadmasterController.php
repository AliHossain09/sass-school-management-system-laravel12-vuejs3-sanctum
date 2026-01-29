<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HeadmasterController extends Controller
{
    public function indexTeachers(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        // Headmaster can only see teachers of their school
        $teachers = Teacher::with('user', 'school')
            ->where('school_id', auth()->user()->school_id)
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $teachers->items(),
            'meta' => [
                'current_page' => $teachers->currentPage(),
                'from' => $teachers->firstItem(),
                'to' => $teachers->lastItem(),
                'per_page' => $teachers->perPage(),
                'total' => $teachers->total(),
                'last_page' => $teachers->lastPage(),
            ],
        ]);
    }

    public function storeTeacher(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')
                ->store('teachers', 'public');
        }

        $teacher = Teacher::create([
            'teacher_code' => 'T-'.strtoupper(Str::random(6)),
            'user_id' => auth()->id(),
            'school_id' => auth()->user()->school_id,

            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'gender' => $request->gender,
            'dob' => $request->dob,

            'designation' => $request->designation,
            'department' => $request->department,

            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,

            'guardian_name' => $request->guardian_name,
            'guardian_phone' => $request->guardian_phone,

            'photo' => $photoPath,
        ]);

        return response()->json([
            'success' => true,
            'data' => $teacher,
            'message' => 'Teacher created successfully',
        ], 201);
    }

    public function updateTeacher(Request $request, Teacher $teacher)
    {
        // Validate all fields
        $request->validate([
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'gender' => 'nullable|in:male,female,other',
            'dob' => 'nullable|date',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'nid' => 'nullable|string',

            'subjects' => 'nullable|array',
            'class_assigned' => 'nullable|string',
            'joining_date' => 'nullable|date',
            'grade' => 'nullable|string',
            'employment_type' => 'nullable|in:full-time,part-time',
            'department' => 'nullable|string',

            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',

            'emergency_contact' => 'nullable|string',
            'qualification' => 'nullable|string',
            'experience' => 'nullable|integer',
            'salary' => 'nullable|numeric',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($teacher->photo) {
                Storage::disk('public')->delete($teacher->photo);
            }

            $teacher->photo = $request->file('photo')->store('teachers', 'public');
        }

        // Update other fields
        $teacher->fill(
            array_filter(
                $request->only([
                    'first_name',
                    'last_name',
                    'gender',
                    'dob',
                    'nid',
                    'subjects',
                    'class_assigned',
                    'joining_date',
                    'grade',
                    'employment_type',
                    'department',
                    'phone',
                    'email',
                    'address',
                    'emergency_contact',
                    'qualification',
                    'experience',
                    'salary',
                ]),
                fn ($value) => ! is_null($value)
            )
        );

        $teacher->save();

        return response()->json([
            'success' => true,
            'data' => $teacher,
            'message' => 'Teacher updated successfully',
        ]);
    }

    public function destroyTeacher(Teacher $teacher)
{
    // Security: same school check (recommended)
    if ($teacher->school_id !== auth()->user()->school_id) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Delete photo if exists
    if ($teacher->photo && Storage::disk('public')->exists($teacher->photo)) {
        Storage::disk('public')->delete($teacher->photo);
    }

    $teacher->delete();

    return response()->json([
        'success' => true,
        'message' => 'Teacher deleted successfully',
    ]);
}

}
