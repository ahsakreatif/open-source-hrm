import { writable } from 'svelte/store';

const DEFAULT_MOBILE_BREAKPOINT = 768;

function createIsMobileStore(breakpoint: number = DEFAULT_MOBILE_BREAKPOINT) {
    const { subscribe, set } = writable(false);

    function updateIsMobile() {
        if (typeof window !== 'undefined') {
            set(window.innerWidth < breakpoint);
        }
    }

    // Initialize immediately if in browser
    if (typeof window !== 'undefined') {
        updateIsMobile();
        window.addEventListener('resize', updateIsMobile);
    }

    return { subscribe };
}

export const isMobile = createIsMobileStore();
