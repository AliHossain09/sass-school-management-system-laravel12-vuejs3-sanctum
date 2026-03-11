<?php

namespace App\Http\Controllers\Api;

use App\Models\Teacher;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreTeacherRequest;
use App\Http\Requests\Teacher\UpdateTeacherRequest;
use App\Services\TeacherService;
use Illuminate\Http\JsonResponse;

class TeacherController extends Controller
{
    public function __construct(protected TeacherService $teacherService)
    {
    }

     public function indexTeachers(Request $request): JsonResponse
{
    $perPage = $request->get('per_page', 10);
    $search  = $request->get('search');

    $teachers = $this->teacherService->paginate($request->user(), (int) $perPage, $search);

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

    public function storeTeacher(StoreTeacherRequest $request): JsonResponse
    {
        $teacher = $this->teacherService->store(
            $request->user(),
            $request->validated(),
            $request->file('photo')
        );

        return response()->json([
            'success' => true,
            'data' => $teacher,
            'message' => 'Teacher created successfully',
        ], 201);
    }

    public function updateTeacher(UpdateTeacherRequest $request, Teacher $teacher): JsonResponse
    {
        $updated = $this->teacherService->update(
            $request->user(),
            $teacher,
            $request->validated(),
            $request->file('photo')
        );

        if (! $updated) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $updated,
            'message' => 'Teacher updated successfully',
        ]);
    }

    public function destroyTeacher(Request $request, Teacher $teacher): JsonResponse
    {
        $ok = $this->teacherService->destroy($request->user(), $teacher);

        if (! $ok) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Teacher deleted successfully',
        ]);
    }

}
