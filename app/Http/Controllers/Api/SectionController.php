<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Section\StoreSectionRequest;
use App\Http\Requests\Section\UpdateSectionRequest;
use App\Models\Section;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\SectionService;
use Illuminate\Http\JsonResponse;

class SectionController extends Controller
{
    public function __construct(protected SectionService $sectionService)
    {
    }

    // List Sections
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        $search  = $request->get('search');

        $sections = $this->sectionService->paginate($request->user(), (int) $perPage, $search);

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
    public function store(StoreSectionRequest $request): JsonResponse
    {
        $section = $this->sectionService->store($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $section,
            'message' => 'Section created successfully',
        ], 201);
    }

    // Update Section
    public function update(UpdateSectionRequest $request, Section $section): JsonResponse
    {
        $updated = $this->sectionService->update($request->user(), $section, $request->validated());

        if (! $updated) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $updated,
            'message' => 'Section updated successfully',
        ]);
    }

    // Delete Section
    public function destroy(Request $request, Section $section): JsonResponse
    {
        $ok = $this->sectionService->destroy($request->user(), $section);

        if (! $ok) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Section deleted successfully',
        ]);
    }
}
