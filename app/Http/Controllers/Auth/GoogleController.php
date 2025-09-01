<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        try {
            Log::info('Google OAuth redirect initiated');
            return Socialite::driver('google')->redirect();
        } catch (\Exception $e) {
            Log::error('Google OAuth redirect failed: ' . $e->getMessage());
            return redirect()->route('login')
                ->withErrors(['email' => 'Google OAuth is currently unavailable. Please try again later.']);
        }
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            Log::info('Google OAuth callback initiated');
            $googleUser = Socialite::driver('google')->user();
            Log::info('Google user retrieved: ' . $googleUser->getEmail());

            // Check if user already exists
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                Log::info('Existing user found: ' . $user->email);

                // User exists, check if they have Google ID
                if (!$user->google_id) {
                    // Update user with Google ID
                    $user->update([
                        'google_id' => $googleUser->getId(),
                    ]);
                    Log::info('Updated user with Google ID');
                }

                // Check if user is a student (only students can use Google SSO)
                if (!$user->hasRole('student')) {
                    Log::warning('Non-student user attempted Google SSO: ' . $user->email);
                    return redirect()->route('login')
                        ->withErrors(['email' => 'Google SSO is only available for students. Please use email/password login.']);
                }

                Auth::login($user);
                Log::info('User logged in successfully: ' . $user->email);
                return redirect()->intended(route('dashboard', absolute: false));
            }

            // User doesn't exist, create new student account
            Log::info('Creating new student account for: ' . $googleUser->getEmail());
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'email_verified_at' => now(), // Google emails are verified
            ]);

            // Assign student role
            $user->assignRole('employee');

            Auth::login($user);
            Log::info('New student account created and logged in: ' . $user->email);

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            Log::error('Google OAuth callback failed: ' . $e->getMessage());
            return redirect()->route('login')
                ->withErrors(['email' => 'Google authentication failed. Please try again.']);
        }
    }
}
