<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\UserController;
use \App\Http\Controllers\CarController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('user')->group(function () {
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/save', [UserController::class, 'save'])->middleware('auth:api');
    Route::post('/search', [UserController::class, 'search'])->middleware('auth:api','admin');
});

Route::middleware(['auth:api'])->group(function () {
    Route::prefix('car')->group(function () {
        Route::post('/search', [CarController::class, 'search']);
        Route::post('/save', [CarController::class, 'save'])->middleware('admin');
        Route::post('/delete', [CarController::class, 'delete'])->middleware('admin');

        Route::post('/assign', [CarController::class, 'assign']);
    });
});



