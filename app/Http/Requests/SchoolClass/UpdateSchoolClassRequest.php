<?php

namespace App\Http\Requests\SchoolClass;

use App\Http\Requests\ApiFormRequest;

class UpdateSchoolClassRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
            'group' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}

