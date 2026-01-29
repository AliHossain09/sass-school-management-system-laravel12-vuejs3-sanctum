<?php

namespace App\Http\Controllers\Api;

use App\Models\Teacher;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class HeadmasterController extends Controller
{
   public function storeTeacher(Request $request)
{
    $request->validate([
        'first_name' => 'required',
        'last_name' => 'required',
        'gender' => 'required',
        'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    $photoPath = null;

    if ($request->hasFile('photo')) {
        $photoPath = $request->file('photo')
            ->store('teachers', 'public');
    }

    $teacher = Teacher::create([
        'teacher_code' => 'T-' . strtoupper(Str::random(6)),
        'user_id' => auth()->id(),
        'school_id' => auth()->user()->school_id,

        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'gender' => $request->gender,
        'dob' => $request->dob,

        'designation' => $request->designation,
        'department' => $request->department,

        'phone' => $request->phone,
        'email' => $request->email,
        'address' => $request->address,

        'guardian_name' => $request->guardian_name,
        'guardian_phone' => $request->guardian_phone,

        'photo' => $photoPath,
    ]);

    return response()->json([
        'success' => true,
        'data' => $teacher,
        'message' => 'Teacher created successfully'
    ], 201);
}
}
