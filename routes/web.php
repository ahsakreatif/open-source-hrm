<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

// Public routes (no authentication required)
Route::get('/', function () {
    if (Auth::guard('employee')->check()) {
        return redirect()->route('dashboard');
    }
    // Redirect to employee login for the main app
    return redirect()->route('login');
})->name('home');

// Employee authentication routes (handled in auth.php)
// These are already defined in routes/auth.php

// Protected routes - require authentication
Route::middleware(['auth:employee', 'employee.auth'])->group(function () {

    // Main employee dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('employee/Dashboard');
    })->name('dashboard');

    // Employee-specific routes
    Route::prefix('employee')->group(function () {
        Route::get('/profile', function () {
            return Inertia::render('employee/Profile');
        })->name('employee.profile');

        Route::get('/attendance', function () {
            return Inertia::render('employee/Attendance');
        })->name('employee.attendance');

        Route::get('/leave-requests', function () {
            return Inertia::render('employee/LeaveRequests');
        })->name('employee.leave-requests');

        Route::get('/announcements', function () {
            return Inertia::render('employee/Announcements');
        })->name('employee.announcements');

        Route::get('/payslip', function () {
            return Inertia::render('employee/Payslip');
        })->name('employee.payslip');
    });
});

require __DIR__.'/auth.php';
