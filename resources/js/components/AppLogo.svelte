<script lang="ts">
    // import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import { Link } from '@inertiajs/svelte';

    let { ...attrs } = $props();
</script>

<div class="inline-flex items-center gap-3 h-full" {...attrs}>
    <div class="flex aspect-square items-center justify-center rounded-md p-1 h-full">
        <!-- <AppLogoIcon class="size-5 fill-current text-white! dark:text-black!" /> -->
        <img src="/images/gb-logo.png" alt="HRMS Logo" class="w-full h-full" />
    </div>
    <!-- <div class="flex items-center">
        <Link href={route('dashboard')}>
            <span class="truncate font-semibold">HRMS</span>
        </Link>
    </div> -->
</div>
