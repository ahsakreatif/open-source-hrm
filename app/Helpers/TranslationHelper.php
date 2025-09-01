<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class TranslationHelper
{
    public static function getTranslations(?string $locale = null): array
    {
        $locale = $locale ?? App::getLocale();
        $cacheKey = "translations.{$locale}";
        
        return Cache::remember($cacheKey, 3600, function () use ($locale) {
            return [
                'frontend' => trans('frontend', [], $locale),
                'validation' => trans('validation', [], $locale),
                'auth' => trans('auth', [], $locale),
            ];
        });
    }

    public static function getLocale(): string
    {
        return App::getLocale();
    }

    public static function getAvailableLocales(): array
    {
        return [
            'en' => 'English',
            'id' => 'Bahasa Indonesia',
        ];
    }
    
    /**
     * Clear translation cache for a specific locale or all locales
     */
    public static function clearCache(?string $locale = null): void
    {
        if ($locale) {
            Cache::forget("translations.{$locale}");
        } else {
            // Clear cache for all supported locales
            foreach (array_keys(self::getAvailableLocales()) as $locale) {
                Cache::forget("translations.{$locale}");
            }
        }
    }
}
