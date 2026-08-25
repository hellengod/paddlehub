<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RiverController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/user', [UserController::class, 'show'])
    ->middleware('auth:sanctum');
Route::get('/rivers', [RiverController::class, 'index'])
    ->middleware('auth:sanctum');
Route::post('/rivers', [RiverController::class, 'store'])
    ->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
