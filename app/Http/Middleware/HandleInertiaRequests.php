<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Helpers\TranslationHelper;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        // Get fresh user data (not cached)
        $user = $request->user();

        // Load roles if user exists
        if ($user) {
            $user->load('roles');
            $userData = $user->toFrontendArray();
        } else {
            $userData = null;
        }

        // Debug: Check the current locale
        \Illuminate\Support\Facades\Log::info('HandleInertiaRequests - Current Locale: ' . app()->getLocale());

        $shared = [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $userData,
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',

            // Add translations - these will be available on ALL routes
            'translations' => TranslationHelper::getTranslations(),
            'locale' => TranslationHelper::getLocale(),
            'availableLocales' => TranslationHelper::getAvailableLocales(),
            'flash' => [
                'message' => fn () => $request->session()->get('message')
            ],
        ];

        return $shared;
    }
}
