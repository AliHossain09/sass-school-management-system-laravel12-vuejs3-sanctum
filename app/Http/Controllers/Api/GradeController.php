<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Grade\StoreGradeRequest;
use App\Http\Requests\Grade\UpdateGradeRequest;
use App\Models\Grade;
use App\Services\GradeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function __construct(protected GradeService $gradeService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);
        $search = $request->get('search');

        $grades = $this->gradeService->paginate($request->user(), $perPage, $search);

        return response()->json([
            'success' => true,
            'data' => $grades->items(),
            'meta' => [
                'current_page' => $grades->currentPage(),
                'from' => $grades->firstItem(),
                'to' => $grades->lastItem(),
                'per_page' => $grades->perPage(),
                'total' => $grades->total(),
                'last_page' => $grades->lastPage(),
            ],
        ]);
    }

    public function store(StoreGradeRequest $request): JsonResponse
    {
        $grade = $this->gradeService->store($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $grade,
            'message' => 'Grade created successfully',
        ], 201);
    }

    public function update(UpdateGradeRequest $request, int $id): JsonResponse
    {
        $grade = Grade::where('school_id', $request->user()->school_id)->findOrFail($id);

        $updated = $this->gradeService->update($request->user(), $grade, $request->validated());
        if (! $updated) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $updated,
            'message' => 'Grade updated successfully',
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $grade = Grade::where('school_id', $request->user()->school_id)->findOrFail($id);

        $ok = $this->gradeService->destroy($request->user(), $grade);
        if (! $ok) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Grade deleted successfully',
        ]);
    }
}

