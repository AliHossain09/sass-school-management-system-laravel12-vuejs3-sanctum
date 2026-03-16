<?php

namespace App\Http\Requests\Student;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $student = $this->route('student');
        $userId = $student?->user_id;
        $classId = $this->input('class_id') ?? $student?->class_id;

        return [
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'religion' => 'nullable|string',
            'nationality' => 'nullable|string',
            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => 'nullable|string',
            'extra_curricular' => 'nullable|string',
            'description' => 'nullable|string',

            'father_name' => 'nullable|string',
            'father_phone' => 'nullable|string',
            'mother_name' => 'nullable|string',
            'mother_phone' => 'nullable|string',
            'local_guardian_name' => 'nullable|string',
            'local_guardian_phone' => 'nullable|string',
            'local_guardian_relationship' => 'nullable|string',

            'present_address' => 'nullable|string',
            'permanent_address' => 'nullable|string',

            'class_id' => [
                'nullable',
                Rule::exists('school_classes', 'id')->where('school_id', $this->user()?->school_id),
            ],
            'section_id' => [
                'nullable',
                Rule::exists('sections', 'id')
                    ->where('school_id', $this->user()?->school_id)
                    ->where('class_id', $classId),
            ],
            'academic_year' => 'nullable|string',
            'shift' => 'nullable|string',
            'id_card_number' => 'nullable|string',
            'roll_number' => 'nullable|string',
            'board_registration_number' => 'nullable|string',
            'elective_subject_id' => 'nullable|exists:subjects,id',

            'username' => [
                'nullable',
                'string',
                Rule::unique('students', 'username')->ignore($student?->id),
            ],
            'password' => 'nullable|string|min:6',

            'photo' => 'nullable|image|max:2048',
        ];
    }
}
