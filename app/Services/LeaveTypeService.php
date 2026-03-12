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

    public function store(User $actor, array $data): LeaveType
    {
        return LeaveType::create([
            'school_id' => $actor->school_id,
            'name' => $data['name'],
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
}

