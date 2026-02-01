<?php

namespace App\Http\Controllers\Api;

use App\Models\SchoolClass;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ClassController extends Controller
{
     // List Classes (with pagination & search)
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search  = $request->get('search');

        $query = SchoolClass::where('school_id', auth()->user()->school_id);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $classes = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $classes->items(),
            'meta' => [
                'current_page' => $classes->currentPage(),
                'from' => $classes->firstItem(),
                'to' => $classes->lastItem(),
                'per_page' => $classes->perPage(),
                'total' => $classes->total(),
                'last_page' => $classes->lastPage(),
            ],
        ]);
    }

    // Create Class
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'order' => 'nullable|integer',
            'group' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $class = SchoolClass::create([
            'school_id' => auth()->user()->school_id,
            'name' => $request->name,
            'order' => $request->order,
            'group' => $request->group,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'data' => $class,
            'message' => 'Class created successfully',
        ], 201);
    }

    // Update Class
    public function update(Request $request, SchoolClass $schoolClass)
    {
        if ($schoolClass->school_id !== auth()->user()->school_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $schoolClass->update($request->only(['name', 'order', 'group', 'description']));

        return response()->json([
            'success' => true,
            'data' => $schoolClass,
            'message' => 'Class updated successfully',
        ]);
    }

    // Delete Class
    public function destroy(SchoolClass $schoolClass)
    {
        if ($schoolClass->school_id !== auth()->user()->school_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $schoolClass->delete();

        return response()->json([
            'success' => true,
            'message' => 'Class deleted successfully',
        ]);
    }
}
