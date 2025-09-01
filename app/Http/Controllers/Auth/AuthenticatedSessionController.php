<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use App\Enums\Role;
use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        // Get role from POST data (selected_role) or query params, default to student
        $role = $request->input('role') ?? 'student';
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
            'role' => $role,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        $role = $user->roles->first();

        // Debug logging
        Log::info('User login attempt', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $role ? $role->name : 'no role',
            'requested_role' => $request->input('role'),
        ]);

        // Redirect based on role
        return match ($role ? $role->name : '') {
            Role::STUDENT->value => redirect()->intended(route('student.dashboard', absolute: false)),
            Role::SCHOOL->value => redirect()->intended(route('school.dashboard', absolute: false)),
            Role::EMPLOYER->value => redirect()->away(route('filament.portal.pages.dashboard', absolute: false)),
            default => redirect()->intended(route('dashboard', absolute: false)),
        };
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
