<?php

namespace App\Http\Requests\ExamMark;

use App\Http\Requests\ApiFormRequest;

class UpsertExamMarkRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'examination_id' => 'required|integer|exists:examinations,id',
            'class_id' => 'required|integer|exists:school_classes,id',
            'section_id' => 'nullable|integer|exists:sections,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'student_id' => 'required|integer|exists:students,id',
            'mark' => 'required|integer|min:0|max:100',
            'comment' => 'nullable|string|max:1000',
        ];
    }
}

