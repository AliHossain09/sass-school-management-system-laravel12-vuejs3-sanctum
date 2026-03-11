<?php

namespace App\Http\Requests\ClassRoutine;

use App\Http\Requests\ApiFormRequest;

class UpdateClassRoutineRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $days = 'Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday,Friday';

        return [
            'class_id' => 'sometimes|exists:school_classes,id',
            'section_id' => 'sometimes|exists:sections,id',
            'subject_id' => 'sometimes|exists:subjects,id',
            'teacher_id' => 'sometimes|exists:teachers,id',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'is_break' => 'sometimes|boolean',
            'class_room' => 'nullable|string|max:100',

            'day' => 'sometimes|in:'.$days,
            'other_days' => 'sometimes|array|min:1',
            'other_days.*' => 'in:'.$days,
        ];
    }
}

