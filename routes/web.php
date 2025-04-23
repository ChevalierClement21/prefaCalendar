<?php

use App\Http\Controllers\Admin\SectorController;
use App\Http\Controllers\Admin\StreetController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified', 'approved'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tours routes
    Route::controller(TourController::class)->group(function () {
        Route::get('/tours', 'index')->name('tours.index');
        Route::get('/tours/create', 'create')->name('tours.create');
        Route::post('/tours', 'store')->name('tours.store');
        Route::get('/tours/{tour}', 'show')->name('tours.show');
        Route::post('/tours/{tour}/house-numbers', 'addHouseNumber')->name('tours.house-numbers.add');
        Route::patch('/tours/{tour}/house-numbers/{houseNumber}/status', 'updateHouseNumberStatus')->name('tours.house-numbers.status');
        Route::get('/tours/{tour}/complete', 'showCompleteForm')->name('tours.complete-form');
        Route::post('/tours/{tour}/complete', 'submitCompletion')->name('tours.submit-completion');
        Route::patch('/tours/{tour}/complete', 'complete')->name('tours.complete');
        Route::post('/tours/{tour}/streets/{street}/mark-completed', 'markStreetCompleted')->name('tours.streets.mark-completed');
    });

    // Users routes supprimées
});

// Admin routes
Route::middleware(['auth', 'verified', 'approved', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Sectors
    Route::controller(SectorController::class)->group(function () {
        Route::get('/sectors', 'index')->name('sectors.index');
        Route::get('/sectors/create', 'create')->name('sectors.create');
        Route::post('/sectors', 'store')->name('sectors.store');
        Route::get('/sectors/{sector}', 'show')->name('sectors.show');
        Route::get('/sectors/{sector}/edit', 'edit')->name('sectors.edit');
        Route::put('/sectors/{sector}', 'update')->name('sectors.update');
        Route::delete('/sectors/{sector}', 'destroy')->name('sectors.destroy');
    });
    
    // Streets
    Route::controller(StreetController::class)->group(function () {
        Route::get('/streets', 'index')->name('streets.index');
        Route::get('/streets/create', 'create')->name('streets.create');
        Route::post('/streets', 'store')->name('streets.store');
        Route::get('/streets/{street}', 'show')->name('streets.show');
        Route::get('/streets/{street}/edit', 'edit')->name('streets.edit');
        Route::put('/streets/{street}', 'update')->name('streets.update');
        Route::delete('/streets/{street}', 'destroy')->name('streets.destroy');
    });
    
    // Route spéciale pour créer une rue liée à un secteur spécifique
    Route::get('/sectors/{sector}/streets/create', [StreetController::class, 'createForSector'])->name('sectors.streets.create');
});

require __DIR__.'/auth.php';