<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    // list
    public function index()
    {
        return AcademicYear::where('school_id', auth()->user()->school_id)
            ->orderByDesc('start_date')
            ->get();
    }

    // create
    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $schoolId = auth()->user()->school_id;

        //  before all academic year inactive
        AcademicYear::where('school_id', $schoolId)
            ->update(['is_active' => false]);

        //  new academic year create
        $academicYear = AcademicYear::create([
            'school_id' => $schoolId,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'data' => $academicYear,
            'message' => 'Academic Year created successfully',
        ]);
    }

    // update
    public function update(Request $request, AcademicYear $academicYear)
    {
        if ($academicYear->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'required|boolean',
        ]);

        // if this active set all others inactive
        if ($request->is_active) {
            AcademicYear::where('school_id', auth()->user()->school_id)
                ->update(['is_active' => false]);
        }

        $academicYear->update($request->only([
            'start_date',
            'end_date',
            'is_active',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Academic Year updated successfully',
        ]);
    }
    // delete
    public function destroy(AcademicYear $academicYear)
    {
        if ($academicYear->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        $academicYear->delete();

        return response()->json([
            'success' => true,
            'message' => 'Academic Year deleted successfully',
        ]);
    }
}