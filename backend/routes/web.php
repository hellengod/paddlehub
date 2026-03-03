<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', function () {
    return response()->json(
        [
            "success" => true,
            "message" => "API running",
            "time" => now()
        ]
    );
});