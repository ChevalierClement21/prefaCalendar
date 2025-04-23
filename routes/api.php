<?php

use App\Http\Controllers\Admin\SessionController;
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

// Routes API pour les sessions
Route::prefix('sessions')->group(function () {
    Route::get('/', [SessionController::class, 'index']);
    Route::get('/active', [SessionController::class, 'getActive']);
    Route::get('/{session}', [SessionController::class, 'show']);
    Route::post('/', [SessionController::class, 'store']);
    Route::put('/{session}', [SessionController::class, 'update']);
    Route::delete('/{session}', [SessionController::class, 'destroy']);
    Route::put('/{session}/activate', [SessionController::class, 'setActive']);
});
