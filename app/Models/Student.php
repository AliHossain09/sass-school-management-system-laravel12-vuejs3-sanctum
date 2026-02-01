<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
       protected $fillable = ['student_code', 'user_id', 'school_id', 'first_name', 'last_name', 'class_id', 'section_id', 'academic_year',];



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
    // public function parents()
    // {
    //     return $this->hasMany(ParentModel::class);
    // }
    // Section
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }
    // Class
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }
    // Subjects
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'student_subject', 'student_id', 'subject_id');
    }


}
