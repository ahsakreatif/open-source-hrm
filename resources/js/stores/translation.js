import { derived } from 'svelte/store';
import { page } from '@inertiajs/svelte';

// Create stores that derive from Inertia page props
export const translations = derived(page, ($page) => {
    return $page.props.translations || {};
});

export const locale = derived(page, ($page) => {
    return $page.props.locale || 'en';
});

export const availableLocales = derived(page, ($page) => {
    return $page.props.availableLocales || {};
});

// Helper function to get nested translation values
export function t(key, params = null, fallback = '') {
    let trans;
    translations.subscribe(t => trans = t)();

    const keys = key.split('.');
    let value = trans;

    for (const k of keys) {
        if (value && typeof value === 'object' && k in value) {
            value = value[k];
        } else {
            return fallback || key;
        }
    }

    let result = value || fallback || key;

    // Handle interpolation if params are provided
    if (params && typeof params === 'object') {
        Object.keys(params).forEach(param => {
            result = result.replace(new RegExp(`{${param}}`, 'g'), params[param]);
        });
    }

    return result;
}

// Direct access functions for components that need immediate access
export function getAvailableLocales() {
    let currentPage;
    page.subscribe(p => currentPage = p)();
    return currentPage?.props?.availableLocales || {};
}

export function getCurrentLocale() {
    let currentPage;
    page.subscribe(p => currentPage = p)();
    return currentPage?.props?.locale || 'en';
}

export function getTranslations() {
    let currentPage;
    page.subscribe(p => currentPage = p)();
    return currentPage?.props?.translations || {};
}

