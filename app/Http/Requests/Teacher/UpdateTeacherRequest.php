<?php

namespace App\Http\Requests\Teacher;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $subjects = $this->input('subjects');

        if (is_string($subjects)) {
            $decoded = json_decode($subjects, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge(['subjects' => $decoded]);
            }
        }
    }

    public function rules(): array
    {
        $teacher = $this->route('teacher');
        $userId = $teacher?->user_id;

        return [
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'gender' => 'nullable|in:male,female,other',
            'dob' => 'nullable|date',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'nid' => 'nullable|string',

            'subjects' => 'nullable|array',
            'class_assigned' => 'nullable|string',
            'joining_date' => 'nullable|date',
            'grade' => 'nullable|string',
            'employment_type' => 'nullable|in:full-time,part-time',
            'department' => 'nullable|string',

            'phone' => 'nullable|string',
            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'address' => 'nullable|string',

            'emergency_contact' => 'nullable|string',
            'qualification' => 'nullable|string',
            'experience' => 'nullable|integer',
            'salary' => 'nullable|numeric',
        ];
    }
}

