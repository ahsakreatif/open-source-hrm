<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotEmployee
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('employee')->check()) {
            // If not authenticated as employee, redirect to employee login
            return redirect()->route('employee.login');
        }

        // Check if the authenticated employee is active
        $employee = Auth::guard('employee')->user();
        if (!$employee->is_active) {
            Auth::guard('employee')->logout();
            return redirect()->route('employee.login')->withErrors([
                'email' => 'Your account has been deactivated. Please contact HR.'
            ]);
        }

        return $next($request);
    }
}
