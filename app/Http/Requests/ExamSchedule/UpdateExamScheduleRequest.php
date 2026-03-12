<?php

namespace App\Http\Requests\ExamSchedule;

use App\Http\Requests\ApiFormRequest;

class UpdateExamScheduleRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_id' => 'required|integer|exists:school_classes,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'exam_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'duration_minutes' => 'required|integer|min:1|max:1440',
            'room' => 'nullable|string|max:255',
        ];
    }
}

