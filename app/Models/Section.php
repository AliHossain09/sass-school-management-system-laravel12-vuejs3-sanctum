<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
     // Section belongs to a class
    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    // Section has many students
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    // Section has one class teacher
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
