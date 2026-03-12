<?php

namespace App\Services;

use App\Models\Examination;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ExaminationService
{
    public function paginate(User $actor, int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        $search = trim((string) $search);

        return Examination::query()
            ->where('school_id', $actor->school_id)
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function store(User $actor, array $data): Examination
    {
        return Examination::create([
            'school_id' => $actor->school_id,
            'name' => $data['name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
    }

    public function update(User $actor, Examination $examination, array $data): ?Examination
    {
        if ($examination->school_id !== $actor->school_id) {
            return null;
        }

        $examination->update([
            'name' => $data['name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'is_active' => (bool) ($data['is_active'] ?? $examination->is_active),
        ]);

        return $examination;
    }

    public function destroy(User $actor, Examination $examination): bool
    {
        if ($examination->school_id !== $actor->school_id) {
            return false;
        }

        $examination->delete();

        return true;
    }
}

