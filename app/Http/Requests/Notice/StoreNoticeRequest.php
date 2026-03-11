<?php

namespace App\Http\Requests\Notice;

use App\Http\Requests\ApiFormRequest;

class StoreNoticeRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'type' => 'required|in:all,class',
            'publish_date' => 'required|date',
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'exists:school_classes,id',
            'section_ids' => 'nullable|array',
            'section_ids.*' => 'exists:sections,id',
            'description' => 'nullable|string',
        ];
    }
}

