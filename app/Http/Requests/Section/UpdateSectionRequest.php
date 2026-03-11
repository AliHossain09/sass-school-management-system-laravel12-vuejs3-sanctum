<?php

namespace App\Http\Requests\Section;

use App\Http\Requests\ApiFormRequest;

class UpdateSectionRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'class_id' => 'nullable|exists:school_classes,id',
            'description' => 'nullable|string',
        ];
    }
}

