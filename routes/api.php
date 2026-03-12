<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\EventNotificationController;
use App\Http\Controllers\Api\MasterAdminController;
use App\Http\Controllers\Api\AcademicYearController;
use App\Http\Controllers\Api\ClassRoutineController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\LeaveTypeController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ExaminationController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\ExamMarkController;

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
    Route::get('/event-notifications', [EventNotificationController::class, 'index']);
    Route::post('/event-notifications/{eventNotification}/read', [EventNotificationController::class, 'markAsRead']);
    // Everyone logged in can VIEW notices index route with optional filters
    Route::get('/notices', [NoticeController::class, 'index']);
    //ClassRoutineController index route with optional filters
    Route::get('/class-routines', [ClassRoutineController::class, 'index']);
    
    // Notifications (database)
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);

    // Teacher/Student self-service Leaves
    Route::middleware('role:teacher,student')->group(function () {
        Route::get('/leave-types/available', [LeaveTypeController::class, 'available']);
        Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
        Route::get('/my/leave-requests', [LeaveRequestController::class, 'myIndex']);
        Route::get('/my/leave-balance', [LeaveRequestController::class, 'myBalance']);
    });

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
        Route::get('/sections/by-class/{classId}', [SectionController::class, 'byClass']);
        Route::post('/sections', [SectionController::class, 'store']);
        Route::put('/sections/{section}', [SectionController::class, 'update']);
        Route::delete('/sections/{section}', [SectionController::class, 'destroy']);

        // Subject Management
        Route::get('/subjects', [SubjectController::class, 'index']);
        Route::get('/subjects/by-class/{classId}', [SubjectController::class, 'byClass']);
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

        // ClassRoutineController Management
        Route::post('/class-routines', [ClassRoutineController::class, 'store']);
        Route::put('/class-routines/{classRoutine}', [ClassRoutineController::class, 'update']);
        Route::delete('/class-routines/{classRoutine}', [ClassRoutineController::class, 'destroy']);
        
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

        // Leaves
        Route::get('/leave-types', [LeaveTypeController::class, 'index']);
        Route::post('/leave-types', [LeaveTypeController::class, 'store']);
        Route::put('/leave-types/{leaveType}', [LeaveTypeController::class, 'update']);
        Route::delete('/leave-types/{leaveType}', [LeaveTypeController::class, 'destroy']);

        Route::get('/leave-requests', [LeaveRequestController::class, 'index']);
        Route::get('/leave-requests/{leaveRequest}', [LeaveRequestController::class, 'show']);
        Route::patch('/leave-requests/{leaveRequest}/status', [LeaveRequestController::class, 'updateStatus']);
        Route::delete('/leave-requests/{leaveRequest}', [LeaveRequestController::class, 'destroy']);

        // Examinations
        Route::get('/examinations', [ExaminationController::class, 'index']);
        Route::post('/examinations', [ExaminationController::class, 'store']);
        Route::put('/examinations/{examination}', [ExaminationController::class, 'update']);
        Route::delete('/examinations/{examination}', [ExaminationController::class, 'destroy']);

        // Grades
        Route::get('/grades', [GradeController::class, 'index']);
        Route::post('/grades', [GradeController::class, 'store']);
        Route::put('/grades/{grade}', [GradeController::class, 'update']);
        Route::delete('/grades/{grade}', [GradeController::class, 'destroy']);

        // Exam Marks
        Route::get('/exam-marks/manage', [ExamMarkController::class, 'manage']);
        Route::post('/exam-marks', [ExamMarkController::class, 'upsert']);

    });
});
