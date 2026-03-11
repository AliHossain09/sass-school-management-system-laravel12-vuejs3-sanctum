<?php

namespace App\Http\Requests\ClassRoutine;

use App\Http\Requests\ApiFormRequest;

class StoreClassRoutineRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $days = 'Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday,Friday';

        return [
            'class_id' => 'required|exists:school_classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_break' => 'boolean',
            'class_room' => 'nullable|string|max:100',

            'day' => 'required_without:other_days|in:'.$days,
            'other_days' => 'required_without:day|array|min:1',
            'other_days.*' => 'in:'.$days,
        ];
    }
}

