<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\HeadmasterController;
use App\Http\Controllers\Api\MasterAdminController;

// Public
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn (Request $request) => $request->user());

    // Master Admin
    Route::middleware('role:master_admin')->group(function () {
        Route::get('/schools', [MasterAdminController::class, 'schools']);
        Route::post('/schools', [MasterAdminController::class, 'createSchool']);
        Route::put('/schools/{school}', [MasterAdminController::class, 'updateSchool']);
        Route::delete('/schools/{school}', [MasterAdminController::class, 'deleteSchool']);
    });

    // Headmaster for Teachers
    Route::middleware('role:headmaster')->group(function () {
        Route::post('/teachers', [TeacherController::class, 'storeTeacher']);
        Route::get('/teachers', [TeacherController::class, 'indexTeachers']);
        Route::put('/teachers/{teacher}', [TeacherController::class, 'updateTeacher']);
        Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroyTeacher']);

    });
});

