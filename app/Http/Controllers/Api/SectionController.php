<?php

namespace App\Http\Controllers\Api;

use App\Models\Section;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SectionController extends Controller
{
    // List Sections
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search  = $request->get('search');

        $query = Section::with('schoolClass')
            ->where('school_id', auth()->user()->school_id);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $sections = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $sections->items(),
            'meta' => [
                'current_page' => $sections->currentPage(),
                'from' => $sections->firstItem(),
                'to' => $sections->lastItem(),
                'per_page' => $sections->perPage(),
                'total' => $sections->total(),
                'last_page' => $sections->lastPage(),
            ],
        ]);
    }

    // Create Section
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'class_id' => 'required|exists:school_classes,id',
            'description' => 'nullable|string',
        ]);

        $section = Section::create([
            'name' => $request->name,
            'class_id' => $request->class_id,
            'description' => $request->description,
            'school_id' => auth()->user()->school_id,
        ]);

        return response()->json([
            'success' => true,
            'data' => $section,
            'message' => 'Section created successfully',
        ], 201);
    }

    // Update Section
    public function update(Request $request, Section $section)
    {
        // Security: Same school
        if ($section->school_id !== auth()->user()->school_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
            'class_id' => 'nullable|exists:school_classes,id',
            'description' => 'nullable|string',
        ]);

        $section->update(array_filter($request->only(['name','class_id','description'])));

        return response()->json([
            'success' => true,
            'data' => $section,
            'message' => 'Section updated successfully',
        ]);
    }

    // Delete Section
    public function destroy(Section $section)
    {
        if ($section->school_id !== auth()->user()->school_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $section->delete();

        return response()->json([
            'success' => true,
            'message' => 'Section deleted successfully',
        ]);
    }
}
