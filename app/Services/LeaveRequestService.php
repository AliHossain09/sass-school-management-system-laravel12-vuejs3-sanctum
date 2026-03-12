<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Notifications\LeaveRequestCreatedNotification;
use App\Notifications\LeaveRequestStatusUpdatedNotification;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

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

        if ($status === self::STATUS_APPROVED && $leaveRequest->status !== self::STATUS_APPROVED) {
            $this->assertLeaveTypeAllowedForUser($leaveRequest->user, $leaveRequest->leaveType);
            $this->assertHasRemainingDays($leaveRequest->user, $leaveRequest->leaveType, $leaveRequest->start_date, (int) $leaveRequest->duration, $leaveRequest->id);
        }

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

        $this->assertLeaveTypeAllowedForUser($actor, $leaveType);

        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = isset($data['end_date']) && $data['end_date']
            ? Carbon::parse($data['end_date'])->startOfDay()
            : null;

        $duration = $endDate ? $startDate->diffInDays($endDate) + 1 : 1;
        $this->assertHasRemainingDays($actor, $leaveType, $startDate, $duration);

        $leaveRequest = LeaveRequest::create([
            'school_id' => $actor->school_id,
            'leave_type_id' => $leaveType->id,
            'user_id' => $actor->id,
            'applied_at' => now(),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate?->toDateString(),
            'duration' => $duration,
            'status' => self::STATUS_PENDING,
        ]);

        $headmaster = $actor->school?->headmaster;
        if ($headmaster) {
            Notification::send($headmaster, new LeaveRequestCreatedNotification($leaveRequest));
        }

        return $leaveRequest;
    }

    public function balanceForUser(User $actor, ?Carbon $onDate = null): array
    {
        $onDate = $onDate ?: now();
        $year = (int) $onDate->year;

        $types = LeaveType::query()
            ->where('school_id', $actor->school_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return $types->map(function (LeaveType $type) use ($actor, $onDate, $year) {
            $allowed = $type->allowed_days;
            $used = $this->usedDays($actor, $type, $year);
            $remaining = $allowed === null ? null : max(0, (int) $allowed - (int) $used);

            return [
                'leave_type' => [
                    'id' => $type->id,
                    'name' => $type->name,
                    'allowed_days' => $type->allowed_days,
                    'applicable_to' => $type->applicable_to,
                    'applicable_gender' => $type->applicable_gender,
                ],
                'year' => $year,
                'used_days' => (int) $used,
                'remaining_days' => $remaining,
            ];
        })->values()->all();
    }

    private function assertHasRemainingDays(
        ?User $user,
        ?LeaveType $leaveType,
        $startDate,
        int $requestedDays,
        ?int $excludeRequestId = null
    ): void {
        if (! $user || ! $leaveType) {
            throw ValidationException::withMessages([
                'leave_type_id' => 'Invalid leave type.',
            ]);
        }

        if ($leaveType->allowed_days === null) {
            return;
        }

        $date = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $year = (int) $date->year;
        $used = $this->usedDays($user, $leaveType, $year, $excludeRequestId);
        $remaining = (int) $leaveType->allowed_days - (int) $used;

        if ($requestedDays > $remaining) {
            throw ValidationException::withMessages([
                'leave_type_id' => "Not enough leave balance. Remaining {$remaining} day(s) for {$leaveType->name} in {$year}.",
            ]);
        }
    }

    private function usedDays(User $user, LeaveType $leaveType, int $year, ?int $excludeRequestId = null): int
    {
        return (int) LeaveRequest::query()
            ->where('school_id', $user->school_id)
            ->where('user_id', $user->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('status', self::STATUS_APPROVED)
            ->whereYear('start_date', $year)
            ->when($excludeRequestId, fn ($q) => $q->where('id', '!=', $excludeRequestId))
            ->sum('duration');
    }

    private function assertLeaveTypeAllowedForUser(?User $user, ?LeaveType $leaveType): void
    {
        if (! $user || ! $leaveType) {
            throw ValidationException::withMessages([
                'leave_type_id' => 'Invalid leave type.',
            ]);
        }

        $role = (string) $user->role;
        if (in_array($role, ['teacher', 'student'], true) && $leaveType->applicable_to && $leaveType->applicable_to !== 'all' && $leaveType->applicable_to !== $role) {
            throw ValidationException::withMessages([
                'leave_type_id' => "This leave type is not available for {$role}.",
            ]);
        }

        $gender = $this->resolveActorGender($user);
        if ($leaveType->applicable_gender && $leaveType->applicable_gender !== 'any' && $gender && $leaveType->applicable_gender !== $gender) {
            throw ValidationException::withMessages([
                'leave_type_id' => "This leave type is only available for {$leaveType->applicable_gender} users.",
            ]);
        }
    }

    private function resolveActorGender(User $actor): ?string
    {
        if ($actor->role === 'teacher') {
            $actor->loadMissing(['teacher:id,user_id,gender']);

            return $actor->teacher?->gender;
        }

        if ($actor->role === 'student') {
            $actor->loadMissing(['student:id,user_id,gender']);

            return $actor->student?->gender;
        }

        return null;
    }
}
