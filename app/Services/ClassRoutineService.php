<?php

namespace App\Services;

use App\Models\ClassRoutine;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;

class ClassRoutineService
{
    public function buildIndexQuery(User $user, array $filters): array
    {
        $query = ClassRoutine::query()->where('school_id', $user->school_id);

        if ($user->role === 'student') {
            $student = Student::query()->where('user_id', $user->id)->first();

            if (! $student) {
                return ['error' => ['message' => 'Student record not found', 'code' => 404]];
            }

            $query->where('class_id', $student->class_id)
                ->where('section_id', $student->section_id);
        } elseif ($user->role === 'teacher') {
            $teacher = Teacher::query()->where('user_id', $user->id)->first();

            if (! $teacher) {
                return ['error' => ['message' => 'Teacher record not found', 'code' => 404]];
            }

            $query->where('teacher_id', $teacher->id);

            if (! empty($filters['class_id'])) {
                $query->where('class_id', $filters['class_id']);
            }

            if (! empty($filters['subject_id'])) {
                $query->where('subject_id', $filters['subject_id']);
            }
        } elseif ($user->role === 'headmaster') {
            foreach (['class_id', 'section_id', 'teacher_id', 'subject_id'] as $field) {
                if (! empty($filters[$field])) {
                    $query->where($field, $filters[$field]);
                }
            }
        }

        return ['query' => $query];
    }

    public function store(User $user, array $data): array
    {
        $days = [];

        if (! empty($data['other_days']) && is_array($data['other_days'])) {
            $days = array_values($data['other_days']);
        } elseif (! empty($data['day'])) {
            $days = [$data['day']];
        }

        unset($data['other_days']);

        $created = collect();

        foreach ($days as $day) {
            $created->push(ClassRoutine::create([
                ...$data,
                'school_id' => $user->school_id,
                'day' => $day,
            ]));
        }

        return [
            'data' => $created->count() === 1 ? $created->first() : $created,
            'count' => $created->count(),
        ];
    }

    public function update(User $user, ClassRoutine $classRoutine, array $data): ?ClassRoutine
    {
        if ($classRoutine->school_id !== $user->school_id) {
            return null;
        }

        if (! empty($data['other_days']) && is_array($data['other_days'])) {
            $data['day'] = $data['other_days'][0] ?? null;
        }
        unset($data['other_days']);

        $classRoutine->fill(array_filter(
            $data,
            fn ($value) => ! is_null($value)
        ));
        $classRoutine->save();

        return $classRoutine;
    }

    public function destroy(User $user, ClassRoutine $classRoutine): bool
    {
        if ($classRoutine->school_id !== $user->school_id) {
            return false;
        }

        $classRoutine->delete();

        return true;
    }
}

