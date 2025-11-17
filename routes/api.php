<?php

use Illuminate\Support\Facades\Route;

Route::get('/api', function () {
    return response()->json(['message' => 'Welcome to the API']);
});