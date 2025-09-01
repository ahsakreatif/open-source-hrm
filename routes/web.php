<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

// Keep your existing Filament admin routes
Route::prefix('admin')->group(function () {
    // Your existing admin routes
});


require __DIR__.'/auth.php';
