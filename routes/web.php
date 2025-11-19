<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/documentation', function () {
    return view('swagger');
});

Route::get('/swagger', function () {
    return view('swagger');
});


