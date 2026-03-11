<?php

namespace App\Http\Requests\School;

use App\Http\Requests\ApiFormRequest;

class UpdateSchoolRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
        ];
    }
}

