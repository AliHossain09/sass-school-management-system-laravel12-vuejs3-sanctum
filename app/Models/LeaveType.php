<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'allowed_days',
        'applicable_to',
        'applicable_gender',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'allowed_days' => 'integer',
        ];
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
