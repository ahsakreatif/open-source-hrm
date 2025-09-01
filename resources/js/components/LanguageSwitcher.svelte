<script lang="ts">
    import { locale, availableLocales } from '@/stores/translation.js';
    import { Globe } from 'lucide-svelte';
    import { router } from '@inertiajs/svelte';

    console.log("availableLocales:", $availableLocales);

    function switchLanguage(langCode: string) {
        // Set a cookie and reload the page
        console.log('Switching language to:', langCode);
        
        // Set cookie with more explicit options
        document.cookie = `locale=${langCode}; path=/; max-age=31536000; SameSite=Lax`;
        
        // Log the cookie to verify it was set
        console.log('Current cookies:', document.cookie);
        
        // Force a hard reload to ensure the new locale is picked up
        window.location.reload();
    }
</script>

<div class="flex items-center gap-1">
    <!-- <Globe class="h-4 w-4 text-gray-600" /> -->
    {#each Object.entries($availableLocales) as [code, name]}
        <button
            class="flex items-center gap-1 px-2 py-1 h-8 text-sm rounded-md transition-colors {$locale === code ? 'bg-primary text-white' : 'bg-transparent text-gray-600 hover:text-gray-900 hover:bg-gray-100'}"
            on:click={() => switchLanguage(code)}
        >
            <span class="text-sm">{code === 'en' ? '🇺🇸' : '🇮🇩'}</span>
            <!-- <span class="hidden sm:inline text-xs">{name}</span> -->
        </button>
    {/each}
</div>
