<?php

namespace App\Models;

use App\Models\School;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
     protected $fillable = [
        'school_id',
        'start_date',
        'end_date',
        'is_active',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
