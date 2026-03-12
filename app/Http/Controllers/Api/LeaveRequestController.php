<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveRequest\UpdateLeaveRequestStatusRequest;
use App\Models\LeaveRequest;
use App\Services\LeaveRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function __construct(protected LeaveRequestService $leaveRequestService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);
        $search = $request->get('search');
        $status = $request->get('status');

        $leaveRequests = $this->leaveRequestService->paginateForHeadmaster(
            $request->user(),
            $perPage,
            $search,
            $status
        );

        $data = collect($leaveRequests->items())->map(function (LeaveRequest $leaveRequest) {
            $user = $leaveRequest->user;

            return [
                'id' => $leaveRequest->id,
                'applied_at' => $leaveRequest->applied_at,
                'start_date' => $leaveRequest->start_date,
                'end_date' => $leaveRequest->end_date,
                'duration' => $leaveRequest->duration,
                'status' => $leaveRequest->status,
                'rejection_note' => $leaveRequest->rejection_note,
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                ] : null,
                'user_type' => $user?->role,
                'leave_type' => $leaveRequest->leaveType ? [
                    'id' => $leaveRequest->leaveType->id,
                    'name' => $leaveRequest->leaveType->name,
                ] : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $leaveRequests->currentPage(),
                'from' => $leaveRequests->firstItem(),
                'to' => $leaveRequests->lastItem(),
                'per_page' => $leaveRequests->perPage(),
                'total' => $leaveRequests->total(),
                'last_page' => $leaveRequests->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $leaveRequest = $this->leaveRequestService->findForHeadmasterOrFail($request->user(), $id);
        $user = $leaveRequest->user;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $leaveRequest->id,
                'applied_at' => $leaveRequest->applied_at,
                'start_date' => $leaveRequest->start_date,
                'end_date' => $leaveRequest->end_date,
                'duration' => $leaveRequest->duration,
                'status' => $leaveRequest->status,
                'rejection_note' => $leaveRequest->rejection_note,
                'reviewed_by' => $leaveRequest->reviewed_by,
                'reviewed_at' => $leaveRequest->reviewed_at,
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                ] : null,
                'leave_type' => $leaveRequest->leaveType ? [
                    'id' => $leaveRequest->leaveType->id,
                    'name' => $leaveRequest->leaveType->name,
                ] : null,
            ],
        ]);
    }

    public function updateStatus(UpdateLeaveRequestStatusRequest $request, int $id): JsonResponse
    {
        $leaveRequest = LeaveRequest::where('school_id', $request->user()->school_id)->findOrFail($id);

        $updated = $this->leaveRequestService->updateStatus($request->user(), $leaveRequest, $request->validated());

        if (! $updated) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $updated,
            'message' => 'Leave request status updated',
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $leaveRequest = LeaveRequest::where('school_id', $request->user()->school_id)->findOrFail($id);

        $ok = $this->leaveRequestService->destroy($request->user(), $leaveRequest);

        if (! $ok) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Leave request deleted successfully',
        ]);
    }
}

