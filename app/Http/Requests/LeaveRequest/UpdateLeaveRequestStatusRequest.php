<?php

namespace App\Http\Requests\LeaveRequest;

use App\Http\Requests\ApiFormRequest;

class UpdateLeaveRequestStatusRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:approved,pending,rejected',
            'rejection_note' => 'nullable|string|max:2000',
        ];
    }
}

