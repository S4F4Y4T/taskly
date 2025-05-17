<?php


use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\TaskController;
use App\Http\Controllers\v1\TaskStatusController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::put('tasks/{task}/status', TaskStatusController::class);
    Route::apiResource('tasks', TaskController::class);
});
