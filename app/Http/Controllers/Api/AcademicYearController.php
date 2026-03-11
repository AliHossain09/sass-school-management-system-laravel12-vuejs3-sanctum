<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicYear\StoreAcademicYearRequest;
use App\Http\Requests\AcademicYear\UpdateAcademicYearRequest;
use App\Models\AcademicYear;
use App\Services\AcademicYearService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function __construct(protected AcademicYearService $academicYearService)
    {
    }

    // list
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->academicYearService->listForSchool($request->user()));
    }

    // create
    public function store(StoreAcademicYearRequest $request): JsonResponse
    {
        $academicYear = $this->academicYearService->store($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $academicYear,
            'message' => 'Academic Year created successfully',
        ]);
    }

    // update
    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear): JsonResponse
    {
        $updated = $this->academicYearService->update($request->user(), $academicYear, $request->validated());

        if (! $updated) {
            abort(403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Academic Year updated successfully',
        ]);
    }
    // delete
    public function destroy(Request $request, AcademicYear $academicYear): JsonResponse
    {
        $ok = $this->academicYearService->destroy($request->user(), $academicYear);

        if (! $ok) {
            abort(403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Academic Year deleted successfully',
        ]);
    }
}
