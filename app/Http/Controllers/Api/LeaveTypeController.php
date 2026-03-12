<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveType\StoreLeaveTypeRequest;
use App\Http\Requests\LeaveType\UpdateLeaveTypeRequest;
use App\Models\LeaveType;
use App\Services\LeaveTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function __construct(protected LeaveTypeService $leaveTypeService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);
        $search = $request->get('search');

        $leaveTypes = $this->leaveTypeService->paginate($request->user(), $perPage, $search);

        return response()->json([
            'success' => true,
            'data' => $leaveTypes->items(),
            'meta' => [
                'current_page' => $leaveTypes->currentPage(),
                'from' => $leaveTypes->firstItem(),
                'to' => $leaveTypes->lastItem(),
                'per_page' => $leaveTypes->perPage(),
                'total' => $leaveTypes->total(),
                'last_page' => $leaveTypes->lastPage(),
            ],
        ]);
    }

    public function available(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 50);
        $search = $request->get('search');

        $leaveTypes = $this->leaveTypeService->availableFor($request->user(), $perPage, $search);

        return response()->json([
            'success' => true,
            'data' => $leaveTypes->items(),
            'meta' => [
                'current_page' => $leaveTypes->currentPage(),
                'from' => $leaveTypes->firstItem(),
                'to' => $leaveTypes->lastItem(),
                'per_page' => $leaveTypes->perPage(),
                'total' => $leaveTypes->total(),
                'last_page' => $leaveTypes->lastPage(),
            ],
        ]);
    }

    public function store(StoreLeaveTypeRequest $request): JsonResponse
    {
        $leaveType = $this->leaveTypeService->store($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $leaveType,
            'message' => 'Leave type created successfully',
        ], 201);
    }

    public function update(UpdateLeaveTypeRequest $request, int $id): JsonResponse
    {
        $leaveType = LeaveType::where('school_id', $request->user()->school_id)->findOrFail($id);

        $updated = $this->leaveTypeService->update($request->user(), $leaveType, $request->validated());

        if (! $updated) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $updated,
            'message' => 'Leave type updated successfully',
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $leaveType = LeaveType::where('school_id', $request->user()->school_id)->findOrFail($id);

        $ok = $this->leaveTypeService->destroy($request->user(), $leaveType);

        if (! $ok) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Leave type deleted successfully',
        ]);
    }
}
