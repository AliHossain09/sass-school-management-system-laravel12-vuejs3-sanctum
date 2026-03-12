<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GradeService
{
    public function paginate(User $actor, int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        $search = trim((string) $search);

        return Grade::query()
            ->where('school_id', $actor->school_id)
            ->when($search, fn ($q) => $q->where('grade', 'like', "%{$search}%"))
            ->orderByDesc('mark_from')
            ->paginate($perPage);
    }

    public function store(User $actor, array $data): Grade
    {
        return Grade::create([
            'school_id' => $actor->school_id,
            'grade' => $data['grade'],
            'grade_point' => $data['grade_point'],
            'mark_from' => $data['mark_from'],
            'mark_upto' => $data['mark_upto'],
        ]);
    }

    public function update(User $actor, Grade $grade, array $data): ?Grade
    {
        if ($grade->school_id !== $actor->school_id) {
            return null;
        }

        $grade->update([
            'grade' => $data['grade'],
            'grade_point' => $data['grade_point'],
            'mark_from' => $data['mark_from'],
            'mark_upto' => $data['mark_upto'],
        ]);

        return $grade;
    }

    public function destroy(User $actor, Grade $grade): bool
    {
        if ($grade->school_id !== $actor->school_id) {
            return false;
        }

        $grade->delete();

        return true;
    }

    public function resolveForMark(User $actor, int $mark): ?Grade
    {
        return Grade::query()
            ->where('school_id', $actor->school_id)
            ->where('mark_from', '<=', $mark)
            ->where('mark_upto', '>=', $mark)
            ->orderByDesc('mark_from')
            ->first();
    }
}

