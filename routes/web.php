<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\SectorController;
use App\Http\Controllers\Admin\StreetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Routes pour l'administration des secteurs et des rues
    Route::prefix('admin')->name('admin.')->group(function () {
        // Routes pour les secteurs
        Route::resource('sectors', SectorController::class);
        
        // Routes pour les rues
        Route::resource('streets', StreetController::class);
        
        // Route spéciale pour créer une rue dans un secteur spécifique
        Route::get('sectors/{sector}/streets/create', [StreetController::class, 'createForSector'])
            ->name('sectors.streets.create');
    });
});

require __DIR__.'/auth.php';
