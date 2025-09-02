<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\EmployeeLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeAuthenticatedSessionController extends Controller
{
    /**
     * Show the employee login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/EmployeeLogin', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming employee authentication request.
     */
    public function store(EmployeeLoginRequest $request): RedirectResponse
    {

        try {
            // Test authentication manually first
            $credentials = $request->only('email', 'password');
            Log::info('Attempting authentication with credentials', [
                'email' => $credentials['email'],
                'password_length' => strlen($credentials['password'])
            ]);

            $request->authenticate();
            $request->session()->regenerate();

            $employee = Auth::guard('employee')->user();

            // Redirect to our custom employee dashboard
            return redirect()->intended(route('dashboard', absolute: false));
        } catch (\Exception $e) {

            throw $e;
        }
    }

    /**
     * Destroy an authenticated employee session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('employee')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
