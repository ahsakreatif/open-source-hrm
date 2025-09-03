<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;

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

    // Attendance API routes
    Route::prefix('api/attendance')->group(function () {
        Route::get('/today', [AttendanceController::class, 'getTodayAttendance'])->name('api.attendance.today');
        Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('api.attendance.check-in');
        Route::post('/check-out', [AttendanceController::class, 'checkOut'])->name('api.attendance.check-out');
        Route::get('/history', [AttendanceController::class, 'getAttendanceHistory'])->name('api.attendance.history');
        Route::post('/validate-location', [AttendanceController::class, 'validateLocation'])->name('api.attendance.validate-location');
    });
});

require __DIR__.'/auth.php';
