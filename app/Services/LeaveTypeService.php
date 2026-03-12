<?php

namespace App\Services;

use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LeaveTypeService
{
    public function paginate(User $actor, int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        $search = trim((string) $search);

        return LeaveType::query()
            ->where('school_id', $actor->school_id)
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function availableFor(User $actor, int $perPage = 50, ?string $search = null): LengthAwarePaginator
    {
        $search = trim((string) $search);
        $role = (string) $actor->role;
        $gender = $this->resolveActorGender($actor);

        return LeaveType::query()
            ->where('school_id', $actor->school_id)
            ->where('is_active', true)
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when(in_array($role, ['teacher', 'student'], true), fn ($q) => $q->whereIn('applicable_to', ['all', $role]))
            ->when($gender, fn ($q) => $q->whereIn('applicable_gender', ['any', $gender]))
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function store(User $actor, array $data): LeaveType
    {
        return LeaveType::create([
            'school_id' => $actor->school_id,
            'name' => $data['name'],
            'allowed_days' => $data['allowed_days'] ?? null,
            'applicable_to' => $data['applicable_to'] ?? 'all',
            'applicable_gender' => $data['applicable_gender'] ?? 'any',
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
    }

    public function update(User $actor, LeaveType $leaveType, array $data): ?LeaveType
    {
        if ($leaveType->school_id !== $actor->school_id) {
            return null;
        }

        $leaveType->update([
            'name' => $data['name'],
            'allowed_days' => array_key_exists('allowed_days', $data) ? $data['allowed_days'] : $leaveType->allowed_days,
            'applicable_to' => array_key_exists('applicable_to', $data) ? $data['applicable_to'] : ($leaveType->applicable_to ?? 'all'),
            'applicable_gender' => array_key_exists('applicable_gender', $data) ? $data['applicable_gender'] : ($leaveType->applicable_gender ?? 'any'),
            'is_active' => (bool) ($data['is_active'] ?? $leaveType->is_active),
        ]);

        return $leaveType;
    }

    public function destroy(User $actor, LeaveType $leaveType): bool
    {
        if ($leaveType->school_id !== $actor->school_id) {
            return false;
        }

        $leaveType->delete();

        return true;
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
