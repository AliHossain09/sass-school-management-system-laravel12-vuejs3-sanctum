<?php

namespace App\Models;

use App\Models\School;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
      protected $fillable = [
        'school_id',
        'title',
        'start_date',
        'end_date',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
