<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\AdminController;
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

Route::middleware('auth:sanctum')->get('/account', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth', 'is_admin'])->get('/admin', [AdminController::class, 'listUsers']);

Route::get('/get-cities/{country}', [CityController::class, 'getCitiesByCountry'])
    ->where('country', '[0-9]+')
    ->name('getCities');
