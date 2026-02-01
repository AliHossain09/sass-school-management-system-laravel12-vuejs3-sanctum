<?php

namespace App\Models;

use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'school_id',
        'class_id',
        'name',
        'capacity',
        'student_in_time',
        'student_out_time',
        'teacher_id',
    ];
       // Section belongs to a class
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    // Section has many students
    public function students()
    {
        return $this->hasMany(Student::class, 'section_id');
    }

    // Section has one class teacher
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    // Optional but useful
    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
