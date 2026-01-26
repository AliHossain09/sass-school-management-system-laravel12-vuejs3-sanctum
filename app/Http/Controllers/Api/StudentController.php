<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StudentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'email' => 'required|email|unique:users',
            'class' => 'required',
        ]);

        $user = User::create([
            'name' => $request->first_name,
            'email' => $request->email,
            'password' => bcrypt('123456'),
            'role' => 'student',
            'school_id' => auth()->user()->school_id,
        ]);

        Student::create([
            'user_id' => $user->id,
            'school_id' => $user->school_id,
            'student_code' => 'STD-' . rand(10000,99999),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'class' => $request->class,
            'academic_year' => date('Y'),
        ]);

        return response()->json(['message' => 'Student created']);
    }
}
