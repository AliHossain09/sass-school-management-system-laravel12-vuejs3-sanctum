<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;

class MasterAdminController extends Controller
{
    // List Schools
    public function schools()
    {
        $schools = School::with('headmaster')->get();

        return response()->json([
            'success' => true,
            'data' => $schools,
        ]);
    }

    public function createSchool(Request $request)
    {
        // dd('hit');
        $request->validate([
            'name' => 'required',
            'password' => 'required|min:6',
            'address' => 'required',
        ]);

        //  Create school FIRST (without headmaster)
        $school = School::create([
            'name' => $request->name,
            // 'password' => bcrypt($request->password),
            'address' => $request->address,
        ]);

        //  Create headmaster user
        $headmaster = User::create([
            'name' => $request->name.' Headmaster',
            'email' => strtolower(str_replace(' ', '', $request->name))
                        .rand(1000, 9999).'@school.com',
            'password' => bcrypt($request->password),
            'role' => 'headmaster',
            'school_id' => $school->id,
        ]);

        //  Update school with headmaster_id
        $school->update([
            'headmaster_id' => $headmaster->id,
        ]);

        return response()->json([
            'success' => true,
            'data' => $school,
            'message' => 'School created successfully',
        ], 201);
    }

    // Update School
    public function updateSchool(Request $request, School $school)
    {
        $request->validate([
            'name' => 'required',
            'address' => 'required',
        ]);

        $school->update([
            'name' => $request->name,
            'address' => $request->address,
        ]);

        return response()->json([
            'success' => true,
            'data' => $school,
            'message' => 'School updated successfully',
        ]);
    }
}
