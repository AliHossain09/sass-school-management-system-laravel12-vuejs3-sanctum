<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    // Subject belongs to a class
    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    // Subject assigned to one teacher
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    // Many students can take one elective subject
    public function electiveStudents()
    {
        return $this->hasMany(Student::class, 'elective_subject_id');
    }
}
