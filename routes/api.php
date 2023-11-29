<?php

use App\Http\Controllers\AllocationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GanttController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'auth:sanctum'], function() {
    // Dashboard APIs
    Route::get('pending_tasks', [DashboardController::class, 'mostPendingTasks']);

    // Project APIs
    Route::get('projects', [ProjectController::class, 'index']);
    Route::post('projects', [ProjectController::class, 'store']);
    Route::put('projects/{id}', [ProjectController::class, 'update']);
    Route::delete('projects/{id}', [ProjectController::class, 'delete']);

    // Resource APIs
    Route::get('resources', [ResourceController::class, 'index']);
    Route::post('resources', [ResourceController::class, 'store']);
    Route::put('resources/{id}', [ResourceController::class, 'update']);
    Route::delete('resources/{id}', [ResourceController::class, 'delete']);

    // Gantt API
    Route::get('gantt/{project}', [GanttController::class, 'execute']);

    // Allocation API
    Route::post('allocations', [AllocationController::class, 'store']);

    // Task APIs
    Route::post('tasks', [TaskController::class, 'store']);
    Route::delete('tasks/{id}', [TaskController::class, 'delete']);
    Route::put('tasks/{id}', [TaskController::class, 'update']);
    Route::put('tasks/sync/{project_id}', [TaskController::class, 'syncFromJira']);

    // Holiday APIs
    Route::get('holidays', [HolidayController::class, 'index']);
    Route::post('holidays', [HolidayController::class, 'store']);
});
