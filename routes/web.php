<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/api-key/generate', [DashboardController::class, 'generateApiKey'])->name('api-key.generate');
    Route::post('/api-key/regenerate', [DashboardController::class, 'regenerateApiKey'])->name('api-key.regenerate');
    Route::post('/api-key/disable', [DashboardController::class, 'disableApiKey'])->name('api-key.disable');
    Route::get('/table-fields', [DashboardController::class, 'getTableFields'])->name('table-fields');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
