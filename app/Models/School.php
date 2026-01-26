<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = ['name','address','headmaster_id'];

    // School headmaster
    public function headmaster()
    {
        return $this->belongsTo(User::class, 'headmaster_id');
    }

    // All users of this school
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Students
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    // Teachers
    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }
}
