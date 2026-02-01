<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    protected $table = 'school_classes';

    protected $fillable = [
        'school_id',
        'name',
        'order',
        'group',
        'description'
    ];

    // One class has many sections
    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    // One class has many subjects
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    // One class has many students
    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
