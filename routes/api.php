<?php

use App\Http\Controllers\Api\AcademicYearController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\MasterAdminController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\NoticeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Route::get('/user', fn (Request $request) => $request->user());

    Route::get('/user', function (Request $request) {
        $user = $request->user()->load(['teacher', 'student']);

        $avatar = null;

        if ($user->role === 'teacher' && $user->teacher?->photo) {
            $avatar = asset('storage/'.$user->teacher->photo);
        }

        if ($user->role === 'student' && $user->student?->photo) {
            $avatar = asset('storage/'.$user->student->photo);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'avatar' => $avatar, //  real image here
        ]);
    });

    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/calendar', [EventController::class, 'calendarEvents']);
    // Everyone logged in can VIEW notices
    Route::get('/notices', [NoticeController::class, 'index']);

    // Master Admin
    Route::middleware('role:master_admin')->group(function () {
        Route::get('/schools', [MasterAdminController::class, 'schools']);
        Route::post('/schools', [MasterAdminController::class, 'createSchool']);
        Route::put('/schools/{school}', [MasterAdminController::class, 'updateSchool']);
        Route::delete('/schools/{school}', [MasterAdminController::class, 'deleteSchool']);
    });

    // Headmaster
    Route::middleware('role:headmaster')->group(function () {
        // Class Management
        Route::get('/classes', [ClassController::class, 'index']);
        Route::post('/classes', [ClassController::class, 'store']);
        Route::put('/classes/{schoolClass}', [ClassController::class, 'update']);
        Route::delete('/classes/{schoolClass}', [ClassController::class, 'destroy']);

        // Section Management
        Route::get('/sections', [SectionController::class, 'index']);
        Route::post('/sections', [SectionController::class, 'store']);
        Route::put('/sections/{section}', [SectionController::class, 'update']);
        Route::delete('/sections/{section}', [SectionController::class, 'destroy']);

        // Subject Management
        Route::get('/subjects', [SubjectController::class, 'index']);
        Route::post('/subjects', [SubjectController::class, 'store']);
        Route::put('/subjects/{subject}', [SubjectController::class, 'update']);
        Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy']);

        // Route::get('/subjects/teachers', [SubjectController::class, 'teachers']);

        // Teacher Management
        Route::post('/teachers', [TeacherController::class, 'storeTeacher']);
        Route::get('/teachers', [TeacherController::class, 'indexTeachers']);
        Route::put('/teachers/{teacher}', [TeacherController::class, 'updateTeacher']);
        Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroyTeacher']);

        // Student Management
        Route::post('/students', [StudentController::class, 'storeStudent']);
        Route::get('/students', [StudentController::class, 'indexStudents']);
        Route::put('/students/{student}', [StudentController::class, 'updateStudent']);
        Route::delete('/students/{student}', [StudentController::class, 'destroyStudent']);

        // Notices Management
        Route::post('/notices', [NoticeController::class, 'store']);
        Route::put('/notices/{notice}', [NoticeController::class, 'update']);
        Route::delete('/notices/{notice}', [NoticeController::class, 'destroy']);

        // Events Management
        Route::post('/events', [EventController::class, 'store']);
        Route::put('/events/{event}', [EventController::class, 'update']);
        Route::delete('/events/{event}', [EventController::class, 'destroy']);

        // AcademicYear
        Route::get('/academic-years', [AcademicYearController::class, 'index']);
        Route::post('/academic-years', [AcademicYearController::class, 'store']);
        Route::put('/academic-years/{academicYear}', [AcademicYearController::class, 'update']);
        Route::delete('/academic-years/{academicYear}', [AcademicYearController::class, 'destroy']);

    });
});
