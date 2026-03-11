<?php

namespace App\Http\Requests\Subject;

use App\Http\Requests\ApiFormRequest;

class StoreSubjectRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_id' => 'required|exists:school_classes,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:core,elective,optional',
            'code' => 'nullable|string|max:50',
            'teacher_id' => 'nullable|exists:teachers,id',
        ];
    }
}

