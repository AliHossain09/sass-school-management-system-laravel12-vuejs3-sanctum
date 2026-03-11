<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Subject\StoreSubjectRequest;
use App\Http\Requests\Subject\UpdateSubjectRequest;
use App\Models\Subject;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\SubjectService;
use Illuminate\Http\JsonResponse;

class SubjectController extends Controller
{
    public function __construct(protected SubjectService $subjectService)
    {
    }

    // List Subjects
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        $search  = $request->get('search');

        $subjects = $this->subjectService->paginate($request->user(), (int) $perPage, $search);

        return response()->json([
            'success' => true,
            'data' => $subjects->items(),
            'meta' => [
                'current_page' => $subjects->currentPage(),
                'from' => $subjects->firstItem(),
                'to' => $subjects->lastItem(),
                'per_page' => $subjects->perPage(),
                'total' => $subjects->total(),
                'last_page' => $subjects->lastPage(),
            ],
        ]);
    }

    // Create Subject
    public function store(StoreSubjectRequest $request): JsonResponse
    {
        $subject = $this->subjectService->store($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $subject,
            'message' => 'Subject created successfully',
        ], 201);
    }

    // Update Subject
    public function update(UpdateSubjectRequest $request, $id): JsonResponse
    {
        $subject = Subject::where('school_id', $request->user()->school_id)->findOrFail($id);

        $updated = $this->subjectService->update($request->user(), $subject, $request->validated());

        if (! $updated) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $updated,
            'message' => 'Subject updated successfully',
        ]);
    }

    // Delete Subject
    public function destroy(Request $request, $id): JsonResponse
    {
        $subject = Subject::where('school_id', $request->user()->school_id)->findOrFail($id);

        $ok = $this->subjectService->destroy($request->user(), $subject);

        if (! $ok) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Subject deleted successfully',
        ]);
    }
}
