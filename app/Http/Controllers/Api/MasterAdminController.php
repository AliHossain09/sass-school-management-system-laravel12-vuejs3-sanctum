<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Http\Requests\School\StoreSchoolRequest;
use App\Http\Requests\School\UpdateSchoolRequest;
use App\Services\MasterAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterAdminController extends Controller
{
    public function __construct(protected MasterAdminService $masterAdminService)
    {
    }

    // List Schools
    public function schools(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10); // optional, default 10
        $schools = $this->masterAdminService->paginateSchools((int) $perPage);

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

    public function createSchool(StoreSchoolRequest $request): JsonResponse
    {
        $school = $this->masterAdminService->createSchool($request->validated());

        return response()->json([
            'success' => true,
            'data' => $school,
            'message' => 'School created successfully with Academic Year',
        ], 201);
    }

    // Update School
    public function updateSchool(UpdateSchoolRequest $request, School $school): JsonResponse
    {
        $school = $this->masterAdminService->updateSchool($school, $request->validated());

        return response()->json([
            'success' => true,
            'data' => $school,
            'message' => 'School updated successfully',
        ]);
    }

    // Delete School
    public function deleteSchool(School $school): JsonResponse
    {
        $this->masterAdminService->deleteSchool($school);

        return response()->json([
            'success' => true,
            'message' => 'School deleted successfully',
        ]);
    }
}
