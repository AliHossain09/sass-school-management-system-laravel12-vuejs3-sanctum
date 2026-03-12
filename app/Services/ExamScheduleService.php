<?php

namespace App\Services;

use App\Models\ExamSchedule;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ExamScheduleService
{
    public function paginate(User $actor, int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        return ExamSchedule::query()
            ->where('school_id', $actor->school_id)
            ->with([
                'schoolClass:id,name',
                'subject:id,name,class_id',
            ])
            ->when($search, function ($q) use ($search) {
                $s = trim($search);
                $q->where('room', 'like', "%{$s}%")
                    ->orWhereHas('schoolClass', fn ($qq) => $qq->where('name', 'like', "%{$s}%"))
                    ->orWhereHas('subject', fn ($qq) => $qq->where('name', 'like', "%{$s}%"));
            })
            ->orderByDesc('exam_date')
            ->orderBy('start_time')
            ->paginate($perPage);
    }

    public function store(User $actor, array $data): ExamSchedule
    {
        $this->assertSubjectBelongsToClass($actor, (int) $data['subject_id'], (int) $data['class_id']);

        return ExamSchedule::create([
            'school_id' => $actor->school_id,
            'class_id' => $data['class_id'],
            'subject_id' => $data['subject_id'],
            'exam_date' => $data['exam_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'duration_minutes' => $data['duration_minutes'],
            'room' => $data['room'] ?? null,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);
    }

    public function update(User $actor, ExamSchedule $schedule, array $data): ?ExamSchedule
    {
        if ($schedule->school_id !== $actor->school_id) {
            return null;
        }

        $this->assertSubjectBelongsToClass($actor, (int) $data['subject_id'], (int) $data['class_id']);

        $schedule->update([
            'class_id' => $data['class_id'],
            'subject_id' => $data['subject_id'],
            'exam_date' => $data['exam_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'duration_minutes' => $data['duration_minutes'],
            'room' => $data['room'] ?? null,
            'updated_by' => $actor->id,
        ]);

        return $schedule->fresh(['schoolClass:id,name', 'subject:id,name,class_id']);
    }

    public function destroy(User $actor, ExamSchedule $schedule): bool
    {
        if ($schedule->school_id !== $actor->school_id) {
            return false;
        }

        $schedule->delete();

        return true;
    }

    protected function assertSubjectBelongsToClass(User $actor, int $subjectId, int $classId): void
    {
        $ok = Subject::query()
            ->where('school_id', $actor->school_id)
            ->where('id', $subjectId)
            ->where('class_id', $classId)
            ->exists();

        if (! $ok) {
            abort(response()->json([
                'message' => 'Selected subject does not belong to the selected class.',
            ], 422));
        }
    }
}

