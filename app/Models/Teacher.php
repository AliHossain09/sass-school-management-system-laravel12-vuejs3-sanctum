<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = ['teacher_code', 'user_id', 'school_id', 'first_name', 'last_name',];

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
}
