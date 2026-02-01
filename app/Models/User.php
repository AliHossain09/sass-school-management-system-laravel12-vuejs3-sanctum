<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];
    protected $fillable = [
    'name',
    'email',
    'password',
    'role',
    'school_id',
    'address',
];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    // Auth user belongs to a school
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // Headmaster of a school
    public function headedSchool()
    {
        return $this->hasOne(School::class, 'headmaster_id');
    }

    // If user is a student
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    // If user is a teacher
    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    // If user is a parent
    public function parentProfile()
    {
        return $this->hasOne(ParentModel::class);
    }
    
        public function subjects()
    {
        return $this->hasMany(Subject::class, 'class_id');
    }

}
