<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
   protected $fillable = [
        'teacher_code',
        'user_id',
        'school_id',

        // Basic
        'first_name',
        'last_name',
        'gender',
        'dob',
        'photo',
        'nid',

        // Professional
        'subjects',
        'class_assigned',
        'joining_date',
        'grade',
        'employment_type',
        'department',

        // Contact
        'phone',
        'email',
        'address',

        // Other
        'emergency_contact',
        'qualification',
        'experience',
        'salary',
    ];

    protected $casts = [
        'subjects' => 'array',
        'dob' => 'date',
        'joining_date' => 'date',
        'salary' => 'decimal:2',
    ];

    // Auth user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // School
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // Teacher teaches many subjects
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    // Teacher is class teacher of many sections
    public function sections()
    {
        return $this->hasMany(Section::class);
    }
}
