<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    // List Students

    public function indexStudents(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');

        $query = Student::with(['schoolClass', 'section', 'electiveSubject'])
            ->where('school_id', auth()->user()->school_id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('student_code', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $students->items(),
            'meta' => [
                'current_page' => $students->currentPage(),
                'from' => $students->firstItem(),
                'to' => $students->lastItem(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
                'last_page' => $students->lastPage(),
            ],
        ]);
    }

    // Store Student

    public function storeStudent(Request $request)
    {
        $request->validate([
            'student_code' => 'required|unique:students,student_code',
            'first_name' => 'required',
            'last_name' => 'required',
            'dob' => 'required|date',
            'gender' => 'required',
            'class_id' => 'required',
            'academic_year' => 'required',

            'username' => 'required|unique:students,username',
            'password' => 'required|min:6',

            'photo' => 'required|image|max:2048',
        ]);

        DB::beginTransaction();

        try {
            // Upload photo
            $photoPath = $request->file('photo')->store('students', 'public');

            // Create User
            $user = User::create([
                'name' => $request->first_name.' '.$request->last_name,
                'email' => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => 'student',
                'school_id' => auth()->user()->school_id,
            ]);

            // Create Student
            $student = Student::create([
                'student_code' => $request->student_code,
                'user_id' => $user->id,
                'school_id' => auth()->user()->school_id,

                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'dob' => $request->dob,
                'gender' => $request->gender,
                'religion' => $request->religion,
                'nationality' => $request->nationality,
                'email' => $request->email,
                'phone' => $request->phone,
                'photo' => $photoPath,

                'father_name' => $request->father_name,
                'father_phone' => $request->father_phone,
                'mother_name' => $request->mother_name,
                'mother_phone' => $request->mother_phone,

                'local_guardian_name' => $request->local_guardian_name,
                'local_guardian_phone' => $request->local_guardian_phone,
                'local_guardian_relationship' => $request->local_guardian_relationship,

                'present_address' => $request->present_address,
                'permanent_address' => $request->permanent_address,

                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'academic_year' => $request->academic_year,
                'shift' => $request->shift,
                'id_card_number' => $request->id_card_number,
                'roll_number' => $request->roll_number,
                'board_registration_number' => $request->board_registration_number,
                'elective_subject_id' => $request->elective_subject_id,

                // Access Info
                'username' => $request->username,
                'password' => Hash::make($request->password),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student admitted successfully',
                'data' => $student,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // Update Student

    public function updateStudent(Request $request, Student $student)
    {
        if ($student->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        $validated = $request->validate([
            // Personal Info
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'religion' => 'nullable|string',
            'nationality' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'extra_curricular' => 'nullable|string',
            'description' => 'nullable|string',

            // Guardian Info
            'father_name' => 'nullable|string',
            'father_phone' => 'nullable|string',
            'mother_name' => 'nullable|string',
            'mother_phone' => 'nullable|string',
            'local_guardian_name' => 'nullable|string',
            'local_guardian_phone' => 'nullable|string',
            'local_guardian_relationship' => 'nullable|string',

            // Address
            'present_address' => 'nullable|string',
            'permanent_address' => 'nullable|string',

            // Academic Info
            'class_id' => 'nullable|exists:school_classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'academic_year' => 'nullable|string',
            'shift' => 'nullable|string',
            'id_card_number' => 'nullable|string',
            'roll_number' => 'nullable|string',
            'board_registration_number' => 'nullable|string',
            'elective_subject_id' => 'nullable|exists:subjects,id',

            // Access Info
            'username' => 'nullable|unique:students,username,'.$student->id,
            'password' => 'nullable|min:6',

            // Photo
            'photo' => 'nullable|image|max:2048',
        ]);

        //  Photo update with delete old
        if ($request->hasFile('photo')) {

            // delete old photo if exists
            if ($student->photo && Storage::disk('public')->exists($student->photo)) {
                Storage::disk('public')->delete($student->photo);
            }

            // store new photo
            $validated['photo'] = $request->file('photo')
                ->store('students', 'public');
        }

        //  Password hash only if provided
        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $student->update($validated);

        return response()->json([
            'success' => true,
            'data' => $student,
            'message' => 'Student updated successfully',
        ]);
    }

    // Delete Student

    public function destroyStudent(Student $student)
    {
        if ($student->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully',
        ]);
    }
}
