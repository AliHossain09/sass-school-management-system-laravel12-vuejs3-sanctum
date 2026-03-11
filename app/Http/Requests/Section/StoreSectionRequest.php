<?php

namespace App\Http\Requests\Section;

use App\Http\Requests\ApiFormRequest;

class StoreSectionRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'class_id' => 'required|exists:school_classes,id',
            'description' => 'nullable|string',
        ];
    }
}

