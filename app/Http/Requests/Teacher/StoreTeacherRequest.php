<?php

namespace App\Http\Requests\Teacher;

use App\Http\Requests\ApiFormRequest;

class StoreTeacherRequest extends ApiFormRequest
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
        return [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'gender' => 'required|in:male,female,other',
            'dob' => 'nullable|date',

            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',

            'subjects' => 'nullable|array',
            'class_assigned' => 'nullable|string',
            'joining_date' => 'nullable|date',
            'grade' => 'nullable|string',
            'employment_type' => 'nullable|in:full-time,part-time',
            'department' => 'nullable|string',

            'nid' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
            'qualification' => 'nullable|string',
            'experience' => 'nullable|integer',
            'salary' => 'nullable|numeric',
        ];
    }
}

