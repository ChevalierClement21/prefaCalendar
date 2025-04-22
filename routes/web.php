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
    return view('welcome');
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
    });

    // Users routes
    Route::controller(UserController::class)->group(function () {
        Route::get('/users', 'index')->name('users.index');
        Route::put('/users/{user}/approve', 'approve')->name('users.approve');
        Route::delete('/users/{user}/reject', 'reject')->name('users.reject');
        Route::put('/users/{user}/assign-admin', 'assignAdmin')->name('users.assign-admin');
        Route::put('/users/{user}/remove-admin', 'removeAdmin')->name('users.remove-admin');
    });
});

// Admin routes
Route::middleware(['auth', 'verified', 'approved', 'can:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Sectors
    Route::controller(SectorController::class)->group(function () {
        Route::get('/sectors', 'index')->name('sectors.index')->middleware('can:manageSectors');
        Route::get('/sectors/create', 'create')->name('sectors.create')->middleware('can:manageSectors');
        Route::post('/sectors', 'store')->name('sectors.store')->middleware('can:manageSectors');
        Route::get('/sectors/{sector}', 'show')->name('sectors.show')->middleware('can:manageSectors');
        Route::get('/sectors/{sector}/edit', 'edit')->name('sectors.edit')->middleware('can:manageSectors');
        Route::put('/sectors/{sector}', 'update')->name('sectors.update')->middleware('can:manageSectors');
        Route::delete('/sectors/{sector}', 'destroy')->name('sectors.destroy')->middleware('can:manageSectors');
    });
    
    // Streets
    Route::controller(StreetController::class)->group(function () {
        Route::get('/streets', 'index')->name('streets.index')->middleware('can:manageStreets');
        Route::get('/streets/create', 'create')->name('streets.create')->middleware('can:manageStreets');
        Route::post('/streets', 'store')->name('streets.store')->middleware('can:manageStreets');
        Route::get('/streets/{street}', 'show')->name('streets.show')->middleware('can:manageStreets');
        Route::get('/streets/{street}/edit', 'edit')->name('streets.edit')->middleware('can:manageStreets');
        Route::put('/streets/{street}', 'update')->name('streets.update')->middleware('can:manageStreets');
        Route::delete('/streets/{street}', 'destroy')->name('streets.destroy')->middleware('can:manageStreets');
    });
    
    // Route spéciale pour créer une rue liée à un secteur spécifique
    Route::get('/sectors/{sector}/streets/create', [StreetController::class, 'createForSector'])->name('sectors.streets.create')->middleware('can:manageStreets');
});

require __DIR__.'/auth.php';