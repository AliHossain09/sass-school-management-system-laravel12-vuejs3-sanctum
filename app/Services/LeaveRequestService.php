<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Notifications\LeaveRequestCreatedNotification;
use App\Notifications\LeaveRequestStatusUpdatedNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Notification;

class LeaveRequestService
{
    private const STATUS_PENDING = 'pending';
    private const STATUS_APPROVED = 'approved';
    private const STATUS_REJECTED = 'rejected';

    public function paginateForHeadmaster(
        User $actor,
        int $perPage = 10,
        ?string $search = null,
        ?string $status = null
    ): LengthAwarePaginator {
        $search = trim((string) $search);
        $status = $status ? trim((string) $status) : null;

        return LeaveRequest::query()
            ->with(['user:id,name,role', 'leaveType:id,name'])
            ->where('school_id', $actor->school_id)
            ->when(in_array($status, [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED], true), fn ($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->whereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('leaveType', fn ($lq) => $lq->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('applied_at')
            ->paginate($perPage);
    }

    public function findForHeadmasterOrFail(User $actor, int $id): LeaveRequest
    {
        return LeaveRequest::query()
            ->with(['user:id,name,role', 'leaveType:id,name'])
            ->where('school_id', $actor->school_id)
            ->findOrFail($id);
    }

    public function updateStatus(User $actor, LeaveRequest $leaveRequest, array $data): ?LeaveRequest
    {
        if ($leaveRequest->school_id !== $actor->school_id) {
            return null;
        }

        $status = $data['status'];
        $rejectionNote = $data['rejection_note'] ?? null;

        $leaveRequest->update([
            'status' => $status,
            'rejection_note' => $status === self::STATUS_REJECTED ? $rejectionNote : null,
            'reviewed_by' => $actor->id,
            'reviewed_at' => now(),
        ]);

        $leaveRequest->user?->notify(new LeaveRequestStatusUpdatedNotification($leaveRequest));

        return $leaveRequest;
    }

    public function destroy(User $actor, LeaveRequest $leaveRequest): bool
    {
        if ($leaveRequest->school_id !== $actor->school_id) {
            return false;
        }

        $leaveRequest->delete();

        return true;
    }

    /**
     * Future use (teacher/student apply): stores a request and notifies headmaster.
     */
    public function createForUser(User $actor, array $data): LeaveRequest
    {
        /** @var LeaveType $leaveType */
        $leaveType = LeaveType::query()
            ->where('school_id', $actor->school_id)
            ->where('is_active', true)
            ->findOrFail($data['leave_type_id']);

        $leaveRequest = LeaveRequest::create([
            'school_id' => $actor->school_id,
            'leave_type_id' => $leaveType->id,
            'user_id' => $actor->id,
            'applied_at' => now(),
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'duration' => (int) ($data['duration'] ?? 1),
            'status' => self::STATUS_PENDING,
        ]);

        $headmaster = $actor->school?->headmaster;
        if ($headmaster) {
            Notification::send($headmaster, new LeaveRequestCreatedNotification($leaveRequest));
        }

        return $leaveRequest;
    }
}

