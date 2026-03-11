<?php

namespace App\Http\Controllers;

use App\Http\Requests\Notice\StoreNoticeRequest;
use App\Http\Requests\Notice\UpdateNoticeRequest;
use App\Models\Notice;
use App\Services\NoticeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function __construct(protected NoticeService $noticeService)
    {
    }

    // List / Pagination
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $notices = $this->noticeService->paginateForUser(
            $user,
            $request->input('search'),
            (int) $request->input('per_page', 10)
        );

        return response()->json($notices);
    }

    // Store Notice
    public function store(StoreNoticeRequest $request): JsonResponse
    {
        $notice = $this->noticeService->store($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $notice,
            'message' => 'Notice created successfully',
        ], 201);
    }

    // Update Notice
    public function update(UpdateNoticeRequest $request, Notice $notice): JsonResponse
    {
        $updated = $this->noticeService->update($request->user(), $notice, $request->validated());

        if (! $updated) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $updated,
            'message' => 'Notice updated successfully',
        ]);
    }

    // Delete Notice
    public function destroy(Request $request, Notice $notice): JsonResponse
    {
        $ok = $this->noticeService->destroy($request->user(), $notice);

        if (! $ok) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notice deleted successfully',
        ]);
    }
}
