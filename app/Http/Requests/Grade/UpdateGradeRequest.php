<?php

namespace App\Http\Requests\Grade;

use App\Http\Requests\ApiFormRequest;

class UpdateGradeRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade' => 'required|string|max:20',
            'grade_point' => 'required|numeric|min:0|max:10',
            'mark_from' => 'required|integer|min:0|max:100',
            'mark_upto' => 'required|integer|min:0|max:100|gte:mark_from',
        ];
    }
}

