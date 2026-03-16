<?php

namespace App\Http\Requests\ExamMark;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class ManageExamMarksRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'examination_id' => [
                'required',
                'integer',
                Rule::exists('examinations', 'id')->where('school_id', $this->user()?->school_id),
            ],
            'class_id' => [
                'required',
                'integer',
                Rule::exists('school_classes', 'id')->where('school_id', $this->user()?->school_id),
            ],
            'section_id' => [
                'nullable',
                'integer',
                Rule::exists('sections', 'id')
                    ->where('school_id', $this->user()?->school_id)
                    ->where('class_id', $this->input('class_id')),
            ],
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id')
                    ->where('school_id', $this->user()?->school_id)
                    ->where('class_id', $this->input('class_id')),
            ],
        ];
    }
}
