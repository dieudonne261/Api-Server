<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ContactController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/contacts', [ContactController::class, 'getAll']);
    Route::post('/contacts', [ContactController::class, 'createContact']);
    Route::get('/contacts/{id}', [ContactController::class, 'getContactById']);
    Route::put('/contacts/{id}', [ContactController::class, 'updateContact']);
    Route::delete('/contacts/{id}', [ContactController::class, 'deleteContact']);
});

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');