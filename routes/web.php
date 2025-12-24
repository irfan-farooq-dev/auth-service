<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    // Artisan::call('view:clear');

    return "Cleared!";
});
