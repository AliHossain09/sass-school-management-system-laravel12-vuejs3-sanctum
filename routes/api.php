<?php

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Api\AuthController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Route::post('/login', [AuthController::class, 'login']);
// Route::post('/register', [AuthController::class, 'register']);




// Protected routes
// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('/logout', [AuthController::class, 'logout']);


//     // User profile route
//     Route::get('/user', function (Request $request) {
//         return $request->user();
//     });


// });

// ......................

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Api\AuthController;
// use App\Http\Controllers\Api\MasterAdminController;
// use App\Http\Controllers\Api\admasterController;


// // Public Routes

// Route::post('/login', [AuthController::class, 'login']);
// Route::post('/register', [AuthController::class, 'register']);


// // Protected Routes

//  Route::middleware(['auth:sanctum'])->group(function () {

//     Route::middleware('role:master_admin')->group(function () {
//         Route::post('/schools', [MasterAdminController::class, 'createSchool']);
//     });

//     Route::middleware('role:headmaster')->group(function () {
//         Route::post('/users', [HeadmasterController::class, 'createUser']);
//     });



// Route::middleware('auth:sanctum')->group(function () {

//    // admaster Routes
    
//     Route::middleware('role:headmaster')->group(function () {
//         Route::get('/users', [HeadmasterController::class, 'users']);
//         Route::post('/users', [HeadmasterController::class, 'createUser']);
//         Route::put('/users/{user}', [HeadmasterController::class, 'updateUser']);
//         Route::delete('/users/{user}', [HeadmasterController::class, 'deleteUser']);
//     });
// });





use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MasterAdminController;
use App\Http\Controllers\Api\HeadmasterController;

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

    // Headmaster
    Route::middleware('role:headmaster')->group(function () {
        Route::post('/teachers', [HeadmasterController::class, 'storeTeacher']);
        Route::get('/teachers', [HeadmasterController::class, 'indexTeachers']);
        Route::post('/teachers/{teacher}', [HeadmasterController::class, 'updateTeacher']);
        Route::delete('/teachers/{teacher}', [HeadmasterController::class, 'destroyTeacher']);

    });
});

