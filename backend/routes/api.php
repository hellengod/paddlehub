<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HydrographyController;
use App\Http\Controllers\LocationSearchController;
use App\Http\Controllers\RiverController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/user', [UserController::class, 'show'])
    ->middleware('auth:sanctum');
Route::get('/rivers', [RiverController::class, 'index'])
    ->middleware('auth:sanctum');
Route::post('/rivers', [RiverController::class, 'store'])
    ->middleware('auth:sanctum');
Route::put('/rivers/{river}', [RiverController::class, 'update'])
    ->middleware('auth:sanctum');
Route::delete('/rivers/{river}', [RiverController::class, 'destroy'])
    ->middleware('auth:sanctum');
Route::get('/hydrography/ana', [HydrographyController::class, 'ana'])
    ->middleware('auth:sanctum');
Route::get('/locations/search', LocationSearchController::class)
    ->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
