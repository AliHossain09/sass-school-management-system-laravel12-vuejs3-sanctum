<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Examination\StoreExaminationRequest;
use App\Http\Requests\Examination\UpdateExaminationRequest;
use App\Models\Examination;
use App\Services\ExaminationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExaminationController extends Controller
{
    public function __construct(protected ExaminationService $examinationService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);
        $search = $request->get('search');

        $exams = $this->examinationService->paginate($request->user(), $perPage, $search);

        return response()->json([
            'success' => true,
            'data' => $exams->items(),
            'meta' => [
                'current_page' => $exams->currentPage(),
                'from' => $exams->firstItem(),
                'to' => $exams->lastItem(),
                'per_page' => $exams->perPage(),
                'total' => $exams->total(),
                'last_page' => $exams->lastPage(),
            ],
        ]);
    }

    public function store(StoreExaminationRequest $request): JsonResponse
    {
        $exam = $this->examinationService->store($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $exam,
            'message' => 'Examination created successfully',
        ], 201);
    }

    public function update(UpdateExaminationRequest $request, int $id): JsonResponse
    {
        $exam = Examination::where('school_id', $request->user()->school_id)->findOrFail($id);

        $updated = $this->examinationService->update($request->user(), $exam, $request->validated());
        if (! $updated) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $updated,
            'message' => 'Examination updated successfully',
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $exam = Examination::where('school_id', $request->user()->school_id)->findOrFail($id);

        $ok = $this->examinationService->destroy($request->user(), $exam);
        if (! $ok) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Examination deleted successfully',
        ]);
    }
}

