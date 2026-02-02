<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'student_code',
        'user_id',
        'school_id',

        // Personal Info
        'first_name',
        'last_name',
        'dob',
        'gender',
        'religion',
        'nationality',
        'email',
        'phone',
        'photo',
        'extra_curricular',
        'description',

        // Guardian Info
        'father_name',
        'father_phone',
        'mother_name',
        'mother_phone',
        'local_guardian_name',
        'local_guardian_phone',
        'local_guardian_relationship',

        // Address
        'present_address',
        'permanent_address',

        // Academic Info
        'class_id',
        'section_id',
        'academic_year',
        'shift',
        'id_card_number',
        'roll_number',
        'board_registration_number',
        'elective_subject_id',

        // Access Info
        'username',
        'password',
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

    // Class
    public function schoolClass()
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
