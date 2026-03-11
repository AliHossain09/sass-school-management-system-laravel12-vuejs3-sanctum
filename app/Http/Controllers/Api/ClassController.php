<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\SchoolClass\StoreSchoolClassRequest;
use App\Http\Requests\SchoolClass\UpdateSchoolClassRequest;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\SchoolClassService;
use Illuminate\Http\JsonResponse;

class ClassController extends Controller
{
    public function __construct(protected SchoolClassService $schoolClassService)
    {
    }

     // List Classes (with pagination & search)
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        $search  = $request->get('search');

        $classes = $this->schoolClassService->paginate($request->user(), (int) $perPage, $search);

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
    public function store(StoreSchoolClassRequest $request): JsonResponse
    {
        $class = $this->schoolClassService->store($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $class,
            'message' => 'Class created successfully',
        ], 201);
    }

    // Update Class
    public function update(UpdateSchoolClassRequest $request, SchoolClass $schoolClass): JsonResponse
    {
        $updated = $this->schoolClassService->update($request->user(), $schoolClass, $request->validated());

        if (! $updated) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $updated,
            'message' => 'Class updated successfully',
        ]);
    }

    // Delete Class
    public function destroy(Request $request, SchoolClass $schoolClass): JsonResponse
    {
        $ok = $this->schoolClassService->destroy($request->user(), $schoolClass);

        if (! $ok) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Class deleted successfully',
        ]);
    }
}
