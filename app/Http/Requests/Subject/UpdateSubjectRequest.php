<?php

namespace App\Http\Requests\Subject;

use App\Http\Requests\ApiFormRequest;

class UpdateSubjectRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_id' => 'nullable|exists:school_classes,id',
            'name' => 'nullable|string|max:255',
            'type' => 'nullable|in:core,elective,optional',
            'code' => 'nullable|string|max:50',
            'teacher_id' => 'nullable|exists:teachers,id',
        ];
    }
}

