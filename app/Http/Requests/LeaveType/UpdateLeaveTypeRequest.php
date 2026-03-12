<?php

namespace App\Http\Requests\LeaveType;

use App\Http\Requests\ApiFormRequest;

class UpdateLeaveTypeRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'allowed_days' => 'nullable|integer|min:1|max:366',
            'applicable_to' => 'nullable|in:all,teacher,student',
            'applicable_gender' => 'nullable|in:any,male,female,other',
            'is_active' => 'nullable|boolean',
        ];
    }
}
