<?php

namespace App\Http\Controllers;

use App\Models\ExamResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExamResultController extends Controller
{
    /**
     * Get a list of recorded exam results.
     */
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;

        $results = ExamResult::with(['student', 'schoolClass', 'section', 'examination'])
            ->where('school_id', $schoolId)
            ->latest()
            ->get();

        // Map the results to match the frontend expected structure
        $mappedResults = $results->map(function ($result) {
            return [
                'id' => $result->id,
                'student' => $result->student,
                'roll_number' => $result->student->roll_number ?? 'N/A', // fallback
                'class_name' => $result->schoolClass->name . ($result->section ? ' (' . $result->section->name . ')' : ''),
                'exam_name' => $result->examination->name ?? 'N/A',
                'grand_total' => $result->grand_total,
                'percent' => $result->percent,
                'grade' => $result->grade,
                'result' => $result->result,
                'published_at' => $result->published_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $mappedResults
        ]);
    }

    /**
     * Store new exam results for a class.
     */
    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'academic_year' => 'required|string',
            'exam_name_id' => 'required|exists:examinations,id',
            'schedule_time' => 'nullable|date',
            'results' => 'required|array',
            'results.*.id' => 'required|exists:students,id',
            'results.*.grand_total' => 'nullable|numeric',
            'results.*.percent' => 'nullable|numeric',
            'results.*.grade' => 'nullable|string',
            'results.*.result' => 'nullable|string',
        ]);

        $schoolId = $request->user()->school_id;
        $userId = $request->user()->id;

        try {
            DB::beginTransaction();

            foreach ($request->results as $stuResult) {
                // If marks are not provided, skip saving for this student
                if (!isset($stuResult['grand_total']) || $stuResult['grand_total'] === '') {
                    continue;
                }

                ExamResult::updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'examination_id' => $request->exam_name_id,
                        'class_id' => $request->class_id,
                        'student_id' => $stuResult['id'],
                        'academic_year' => $request->academic_year,
                    ],
                    [
                        'section_id' => $request->section_id ?? null,
                        'grand_total' => $stuResult['grand_total'],
                        'percent' => $stuResult['percent'],
                        'grade' => $stuResult['grade'],
                        'result' => $stuResult['result'],
                        'published_at' => $request->schedule_time,
                        'created_by' => $userId,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Exam results saved successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving exam results: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save exam results.'
            ], 500);
        }
    }
}
