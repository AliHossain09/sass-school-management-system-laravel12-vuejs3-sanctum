<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamMark\ManageExamMarksRequest;
use App\Http\Requests\ExamMark\UpsertExamMarkRequest;
use App\Models\Examination;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Services\ExamMarkService;
use App\Services\GradeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ExamMarkController extends Controller
{
    public function __construct(
        protected ExamMarkService $examMarkService,
        protected GradeService $gradeService
    ) {
    }

    public function manage(ManageExamMarksRequest $request): JsonResponse
    {
        $actor = $request->user();
        $data = $request->validated();

        $exam = Examination::where('school_id', $actor->school_id)->findOrFail($data['examination_id']);
        $class = SchoolClass::where('school_id', $actor->school_id)->findOrFail($data['class_id']);
        $sectionId = $data['section_id'] ?? null;
        $section = $sectionId
            ? Section::where('school_id', $actor->school_id)->findOrFail($sectionId)
            : null;
        $subject = Subject::where('school_id', $actor->school_id)->findOrFail($data['subject_id']);

        $rows = $this->examMarkService
            ->rowsForManage($actor, $exam->id, $class->id, $sectionId, $subject->id)
            ->map(function (array $row) use ($actor) {
                $mark = $row['mark'];
                $grade = $mark !== null ? $this->gradeService->resolveForMark($actor, (int) $mark) : null;

                $row['grade'] = $grade ? [
                    'grade' => $grade->grade,
                    'grade_point' => (float) $grade->grade_point,
                ] : null;

                return $row;
            })
            ->values();

        return response()->json([
            'success' => true,
            'context' => [
                'examination' => ['id' => $exam->id, 'name' => $exam->name],
                'class' => ['id' => $class->id, 'name' => $class->name],
                'section' => $section ? ['id' => $section->id, 'name' => $section->name] : null,
                'subject' => ['id' => $subject->id, 'name' => $subject->name],
            ],
            'data' => $rows,
        ]);
    }

    public function upsert(UpsertExamMarkRequest $request): JsonResponse
    {
        $actor = $request->user();
        $data = $request->validated();

        Examination::where('school_id', $actor->school_id)->findOrFail($data['examination_id']);
        SchoolClass::where('school_id', $actor->school_id)->findOrFail($data['class_id']);
        if (! empty($data['section_id'])) {
            Section::where('school_id', $actor->school_id)->findOrFail($data['section_id']);
        }
        Subject::where('school_id', $actor->school_id)->findOrFail($data['subject_id']);

        $student = Student::query()
            ->where('school_id', $actor->school_id)
            ->findOrFail($data['student_id']);

        if ((int) $student->class_id !== (int) $data['class_id']) {
            throw ValidationException::withMessages([
                'student_id' => 'Student does not belong to the selected class.',
            ]);
        }

        if (! empty($data['section_id']) && (int) $student->section_id !== (int) $data['section_id']) {
            throw ValidationException::withMessages([
                'student_id' => 'Student does not belong to the selected section.',
            ]);
        }

        if (empty($data['section_id'])) {
            $data['section_id'] = $student->section_id;
        }

        $mark = $this->examMarkService->upsert($actor, $data);
        $grade = $this->gradeService->resolveForMark($actor, (int) $mark->mark);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $mark->id,
                'mark' => $mark->mark,
                'comment' => $mark->comment,
                'grade' => $grade ? [
                    'grade' => $grade->grade,
                    'grade_point' => (float) $grade->grade_point,
                ] : null,
            ],
            'message' => 'Mark saved',
        ]);
    }
}
