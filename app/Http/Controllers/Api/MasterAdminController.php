<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MasterAdminController extends Controller
{
    // List Schools
    public function schools(Request $request)
    {
        $perPage = $request->get('per_page', 10); // optional, default 10
        $schools = School::with('headmaster')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $schools->items(),
            'meta' => [
                'current_page' => $schools->currentPage(),
                'from' => $schools->firstItem(),
                'to' => $schools->lastItem(),
                'per_page' => $schools->perPage(),
                'total' => $schools->total(),
                'last_page' => $schools->lastPage(),
            ],
        ]);
    }

    public function createSchool(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required|min:6',
            'address' => 'required',
        ]);

        //  Create school FIRST
        $school = School::create([
            'name' => $request->name,
            'address' => $request->address,
        ]);

        // Create headmaster user
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

        //  AUTO CREATE ACADEMIC YEAR
        AcademicYear::create([
            'school_id' => $school->id,
            'start_date' => Carbon::now()->startOfYear(),
            'end_date' => Carbon::now()->endOfYear(),
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'data' => $school,
            'message' => 'School created successfully with Academic Year',
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

    // Delete School
    public function deleteSchool(School $school)
    {
        // delete headmaster + all users of this school
        User::where('school_id', $school->id)->delete();

        $school->delete();

        return response()->json([
            'success' => true,
            'message' => 'School deleted successfully',
        ]);
    }
}
