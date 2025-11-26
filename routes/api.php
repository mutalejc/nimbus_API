<?php

use App\Http\Controllers\Api\ClientController;
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

// API v1 routes with API key authentication
Route::prefix('v1')->middleware('api.key')->group(function () {
    Route::get('/clients', [ClientController::class, 'index']);
    Route::get('/clients/{clientNum}', [ClientController::class, 'show']);
    
});
