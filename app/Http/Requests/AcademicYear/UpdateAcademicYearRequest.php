<?php

namespace App\Http\Requests\AcademicYear;

use App\Http\Requests\ApiFormRequest;

class UpdateAcademicYearRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'required|boolean',
        ];
    }
}

