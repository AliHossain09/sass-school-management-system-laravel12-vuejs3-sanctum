<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Teacher;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TeacherController extends Controller
{
 
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        // Create user
        $user = User::create([
            'name' => $request->first_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'teacher',
            'school_id' => auth()->user()->school_id,
        ]);

        // Create teacher profile
        Teacher::create([
            'user_id' => $user->id,
            'school_id' => $user->school_id,
            'teacher_code' => 'TCH-' . rand(1000,9999),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
        ]);

        return response()->json(['message' => 'Teacher created']);
    }


}
