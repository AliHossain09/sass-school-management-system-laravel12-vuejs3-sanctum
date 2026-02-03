<?php

namespace App\Models;

use App\Models\School;
use App\Models\Section;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
     protected $fillable = [
        'school_id',
        'title',
        'type',
        'publish_date',
        'class_ids',
        'section_ids',
        'description',
    ];

    protected $casts = [
        'class_ids' => 'array',
        'section_ids' => 'array',
        'publish_date' => 'date',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'notice_section');
    }
}
