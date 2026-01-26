<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
       protected $fillable = ['student_code', 'user_id', 'school_id', 'first_name', 'last_name', 'class', 'academic_year',];

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

    // Parents
    public function parents()
    {
        return $this->hasMany(ParentModel::class);
    }
}
