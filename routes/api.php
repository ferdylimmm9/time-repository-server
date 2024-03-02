<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FakultasController;
use App\Http\Controllers\Admin\JurusanController;
use App\Http\Controllers\Admin\TesisController;
use App\Http\Controllers\User\TesisController as UserTesisController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\User\UserController as UserUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EnumController;
use App\Http\Controllers\GlobalController;
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

Route::get('health', [GlobalController::class, 'health']);

Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/refresh', [AuthController::class, 'refresh']);

Route::middleware('auth:api')->group(function () {
    Route::get('enums/{enum}', EnumController::class);
    Route::post('auth/logout', [AuthController::class, 'revoke']);

    Route::middleware('admin_auth')->prefix('admin')->group(function () {
        Route::get('dashboard', DashboardController::class);

        Route::apiResource('fakultas', FakultasController::class);
        Route::apiResource('jurusan', JurusanController::class);
        Route::get('user/{user}/tesis', [UserController::class, 'tesis']);
        Route::apiResource('user', UserController::class);
        Route::get('tugas-akhir/badges', [TesisController::class, 'badges']);
        Route::apiResource('tugas-akhir', TesisController::class);
    });

    Route::middleware('user_auth')->prefix('user')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::post('tugas-akhir/{tesis}/update-file', [UserTesisController::class, 'updateFile']);
        Route::apiResource('tugas-akhir', UserTesisController::class)->only('index', 'show', 'store');
        Route::apiResource('user', UserUserController::class)->only('index', 'show');
    });
}) ;
