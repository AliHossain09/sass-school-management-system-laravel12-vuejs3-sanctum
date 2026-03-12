<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamSchedule\StoreExamScheduleRequest;
use App\Http\Requests\ExamSchedule\UpdateExamScheduleRequest;
use App\Models\ExamSchedule;
use App\Services\ExamScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamScheduleController extends Controller
{
    public function __construct(protected ExamScheduleService $examScheduleService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);
        $search = $request->get('search');

        $schedules = $this->examScheduleService->paginate($request->user(), $perPage, $search);

        return response()->json([
            'success' => true,
            'data' => $schedules->items(),
            'meta' => [
                'current_page' => $schedules->currentPage(),
                'from' => $schedules->firstItem(),
                'to' => $schedules->lastItem(),
                'per_page' => $schedules->perPage(),
                'total' => $schedules->total(),
                'last_page' => $schedules->lastPage(),
            ],
        ]);
    }

    public function store(StoreExamScheduleRequest $request): JsonResponse
    {
        $schedule = $this->examScheduleService->store($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $schedule->load(['schoolClass:id,name', 'subject:id,name,class_id']),
            'message' => 'Exam schedule created successfully',
        ], 201);
    }

    public function update(UpdateExamScheduleRequest $request, int $id): JsonResponse
    {
        $schedule = ExamSchedule::where('school_id', $request->user()->school_id)->findOrFail($id);

        $updated = $this->examScheduleService->update($request->user(), $schedule, $request->validated());
        if (! $updated) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $updated,
            'message' => 'Exam schedule updated successfully',
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $schedule = ExamSchedule::where('school_id', $request->user()->school_id)->findOrFail($id);

        $ok = $this->examScheduleService->destroy($request->user(), $schedule);
        if (! $ok) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Exam schedule deleted successfully',
        ]);
    }
}

