<?php

namespace App\Services;

use App\Models\ExamMark;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;

class ExamMarkService
{
    public function rowsForManage(
        User $actor,
        int $examinationId,
        int $classId,
        ?int $sectionId,
        int $subjectId
    ): Collection {
        $students = Student::query()
            ->where('school_id', $actor->school_id)
            ->where('class_id', $classId)
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);

        $marks = ExamMark::query()
            ->where('school_id', $actor->school_id)
            ->where('examination_id', $examinationId)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId), fn ($q) => $q->whereNull('section_id'))
            ->get()
            ->keyBy('student_id');

        return $students->map(function (Student $student) use ($marks) {
            $m = $marks->get($student->id);

            return [
                'student' => [
                    'id' => $student->id,
                    'name' => trim($student->first_name.' '.$student->last_name),
                ],
                'mark' => $m?->mark,
                'comment' => $m?->comment,
            ];
        });
    }

    public function upsert(User $actor, array $data): ExamMark
    {
        $unique = [
            'school_id' => $actor->school_id,
            'examination_id' => $data['examination_id'],
            'class_id' => $data['class_id'],
            'section_id' => $data['section_id'] ?? null,
            'subject_id' => $data['subject_id'],
            'student_id' => $data['student_id'],
        ];

        $values = [
            'mark' => $data['mark'],
            'comment' => $data['comment'] ?? null,
            'updated_by' => $actor->id,
        ];

        $mark = ExamMark::query()->where($unique)->first();
        if (! $mark) {
            $values['created_by'] = $actor->id;
            $mark = ExamMark::create(array_merge($unique, $values));

            return $mark;
        }

        $mark->update($values);

        return $mark;
    }
}

