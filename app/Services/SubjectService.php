<?php

namespace App\Services;

use App\Models\Subject;
use App\Models\User;

class SubjectService
{
    public function paginate(User $actor, int $perPage = 10, ?string $search = null)
    {
        $query = Subject::with('schoolClass', 'teacher')
            ->where('school_id', $actor->school_id);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->paginate($perPage);
    }

    public function store(User $actor, array $data): Subject
    {
        return Subject::create([
            'name' => $data['name'],
            'class_id' => $data['class_id'],
            'type' => $data['type'],
            'code' => $data['code'] ?? null,
            'teacher_id' => $data['teacher_id'] ?? null,
            'school_id' => $actor->school_id,
        ]);
    }

    public function update(User $actor, Subject $subject, array $data): ?Subject
    {
        if ($subject->school_id !== $actor->school_id) {
            return null;
        }

        $subject->fill(array_filter([
            'name' => $data['name'] ?? null,
            'class_id' => $data['class_id'] ?? null,
            'type' => $data['type'] ?? null,
            'code' => $data['code'] ?? null,
            'teacher_id' => $data['teacher_id'] ?? null,
        ], fn ($v) => ! is_null($v)));
        $subject->save();

        return $subject;
    }

    public function destroy(User $actor, Subject $subject): bool
    {
        if ($subject->school_id !== $actor->school_id) {
            return false;
        }

        $subject->delete();

        return true;
    }
}

