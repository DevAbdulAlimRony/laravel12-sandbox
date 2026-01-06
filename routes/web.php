<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// To chek if xdebug is installed and enabled. If we see a dashboard of xdebug, then it is enabled.
Route::get('/xdebug-check', function () {
    return xdebug_info();
});
