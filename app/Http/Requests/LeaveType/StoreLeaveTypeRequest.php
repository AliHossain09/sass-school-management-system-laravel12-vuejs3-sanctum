<?php

namespace App\Http\Requests\LeaveType;

use App\Http\Requests\ApiFormRequest;

class StoreLeaveTypeRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }
}

