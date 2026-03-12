<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = [
        'school_id',
        'grade',
        'grade_point',
        'mark_from',
        'mark_upto',
    ];

    protected function casts(): array
    {
        return [
            'grade_point' => 'decimal:2',
            'mark_from' => 'integer',
            'mark_upto' => 'integer',
        ];
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}

