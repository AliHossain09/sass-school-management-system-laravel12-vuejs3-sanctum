<?php

namespace App\Http\Requests\AcademicYear;

use App\Http\Requests\ApiFormRequest;

class StoreAcademicYearRequest extends ApiFormRequest
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
        ];
    }
}

