<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassRoutine\StoreClassRoutineRequest;
use App\Http\Requests\ClassRoutine\UpdateClassRoutineRequest;
use App\Models\ClassRoutine;
use App\Services\ClassRoutineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassRoutineController extends Controller
{
    public function __construct(protected ClassRoutineService $classRoutineService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $result = $this->classRoutineService->buildIndexQuery($user, $request->all());

        if (isset($result['error'])) {
            return response()->json(
                ['message' => $result['error']['message']],
                $result['error']['code']
            );
        }

        $query = $result['query'];

        $query->with(['schoolClass', 'section', 'subject', 'teacher'])
            ->orderBy('start_time');

        if ($request->boolean('all')) {
            return response()->json($query->get());
        }

        $perPage = $request->get('per_page', 10);

        return response()->json($query->paginate($perPage));
    }

    public function store(StoreClassRoutineRequest $request): JsonResponse
    {
        $created = $this->classRoutineService->store($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $created['data'],
            'message' => 'Class routine created successfully',
        ], 201);
    }

    public function update(UpdateClassRoutineRequest $request, ClassRoutine $classRoutine): JsonResponse
    {
        $updated = $this->classRoutineService->update($request->user(), $classRoutine, $request->validated());

        if (! $updated) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $updated,
            'message' => 'Class routine updated successfully',
        ]);
    }

    public function destroy(Request $request, ClassRoutine $classRoutine): JsonResponse
    {
        $ok = $this->classRoutineService->destroy($request->user(), $classRoutine);

        if (! $ok) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Class routine deleted successfully',
        ]);
    }
}

