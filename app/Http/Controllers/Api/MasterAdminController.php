<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\School;

class MasterAdminController extends Controller
{
     public function createSchool(Request $request)
    {
     $request->validate([
        'name' => 'required',
        'password' => 'required|min:6',
        'address' => 'required',
    ]);


    //  Create school FIRST (without headmaster)
    $school = School::create([
        'name' => $request->name,
        'password' => bcrypt($request->password),
        'address' => $request->address,
    ]);


    //  Create headmaster user
    $headmaster = User::create([
        'name' => $request->name . ' Headmaster',
        'email' => strtolower(str_replace(' ', '', $request->name))
                    . rand(1000,9999) . '@school.com',
        'password' => bcrypt($request->password),
        'role' => 'headmaster',
        'school_id' => $school->id,
    ]);


    //  Update school with headmaster_id
    $school->update([
        'headmaster_id' => $headmaster->id
    ]);


    return response()->json([
        'success' => true,
        'data' => $school,
        'message' => 'School created successfully'
    ], 201);
}

}
