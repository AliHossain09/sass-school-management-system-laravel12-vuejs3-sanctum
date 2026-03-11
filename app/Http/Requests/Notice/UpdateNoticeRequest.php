<?php

namespace App\Http\Requests\Notice;

use App\Http\Requests\ApiFormRequest;

class UpdateNoticeRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'type' => 'nullable|in:all,class',
            'publish_date' => 'nullable|date',
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'exists:school_classes,id',
            'section_ids' => 'nullable|array',
            'section_ids.*' => 'exists:sections,id',
            'description' => 'nullable|string',
        ];
    }
}

