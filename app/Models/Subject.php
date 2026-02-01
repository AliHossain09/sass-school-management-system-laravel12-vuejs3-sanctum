<?php

namespace App\Models;

use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
     protected $fillable = [
    'school_id',
    'class_id',
    'name',
    'code',
    'type',        
    'teacher_id',
];


    // School
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    // Subject belongs to a class
    public function schoolClass()
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
