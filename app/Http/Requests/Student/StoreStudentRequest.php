<?php

namespace App\Http\Requests\Student;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_code' => 'required|string|unique:students,student_code',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'dob' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'religion' => 'required|string',
            'nationality' => 'nullable|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',

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
                'required',
                Rule::exists('school_classes', 'id')->where('school_id', $this->user()?->school_id),
            ],
            'section_id' => [
                'nullable',
                Rule::exists('sections', 'id')
                    ->where('school_id', $this->user()?->school_id)
                    ->where('class_id', $this->input('class_id')),
            ],
            'academic_year' => 'required|string',
            'shift' => 'nullable|string',
            'id_card_number' => 'nullable|string',
            'roll_number' => 'nullable|string',
            'board_registration_number' => 'nullable|string',
            'elective_subject_id' => 'nullable|exists:subjects,id',
            'extra_curricular' => 'nullable|string',
            'description' => 'nullable|string',

            'username' => 'required|string|unique:students,username',
            'password' => 'required|string|min:6',

            'photo' => 'required|image|max:2048',
        ];
    }
}
