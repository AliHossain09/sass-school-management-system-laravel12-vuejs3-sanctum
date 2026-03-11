<?php

namespace App\Http\Requests\School;

use App\Http\Requests\ApiFormRequest;

class StoreSchoolRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'address' => 'required|string',
        ];
    }
}

