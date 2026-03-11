<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\StudentService;

class StudentController extends Controller
{
    public function __construct(protected StudentService $studentService)
    {
    }

    // List Students

    public function indexStudents(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');

        $students = $this->studentService->paginate($request->user(), (int) $perPage, $search);

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

    public function storeStudent(StoreStudentRequest $request): JsonResponse
    {
        $student = $this->studentService->store(
            $request->user(),
            $request->validated(),
            $request->file('photo')
        );

        return response()->json([
            'success' => true,
            'message' => 'Student admitted successfully',
            'data' => $student,
        ]);
    }

    // Update Student

    public function updateStudent(UpdateStudentRequest $request, Student $student): JsonResponse
    {
        $updated = $this->studentService->update(
            $request->user(),
            $student,
            $request->validated(),
            $request->file('photo')
        );

        if (! $updated) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $updated,
            'message' => 'Student updated successfully',
        ]);
    }

    // Delete Student

    public function destroyStudent(Request $request, Student $student): JsonResponse
    {
        $ok = $this->studentService->destroy($request->user(), $student);

        if (! $ok) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully',
        ]);
    }
}
