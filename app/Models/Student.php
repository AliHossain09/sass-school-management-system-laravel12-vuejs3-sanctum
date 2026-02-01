<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'student_code',
        'user_id',
        'school_id',
        'first_name',
        'last_name',
        'class_id',       // FK to school_classes
        'section_id',     // FK to sections
        'academic_year',
        'father_name',
        'father_phone',
        'mother_name',
        'mother_phone',
        'local_guardian_name',
        'local_guardian_phone',
        'local_guardian_relationship',
        'dob',
        'gender',
        'religion',
        'nationality',
        'email',
        'phone',
        'extra_curricular',
        'description',
        'shift',
        'id_card_number',
        'roll_number',
        'board_registration_number',
        'elective_subject_id',
        'username',
        'password'
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

    // Parents
    // public function parents()
    // {
    //     return $this->hasMany(ParentModel::class);
    // }
    
  // Class 
    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    // Section 
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    // Elective subject 
    public function electiveSubject()
    {
        return $this->belongsTo(Subject::class, 'elective_subject_id');
    }


}
