<script lang="ts">
    import { SidebarProvider } from '@/components/ui/sidebar';
    import { Toaster } from '@/components/ui/sonner';
    import { page } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';

    interface Props {
        variant?: 'header' | 'sidebar';
        class?: string;
        children?: Snippet;
    }

    let { variant = 'sidebar', class: className, children }: Props = $props();

    const isOpen = $derived($page.props.sidebarOpen as boolean);
</script>

{#if variant === 'header'}
    <div class="flex min-h-screen w-full flex-col {className}">
        {@render children?.()}
        <Toaster />
    </div>
{:else}
    <SidebarProvider open={isOpen}>
        {@render children?.()}
        <Toaster />
    </SidebarProvider>
{/if}
