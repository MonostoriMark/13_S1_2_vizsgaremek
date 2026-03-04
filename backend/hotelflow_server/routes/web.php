<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// API Documentation (Swagger UI)
Route::get('/api-docs', function () {
    return view('api-docs');
});
