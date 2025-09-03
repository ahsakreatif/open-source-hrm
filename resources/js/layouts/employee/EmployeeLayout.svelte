<script lang="ts">
  import { onMount } from 'svelte';
  import { isMobile } from '../../hooks/is-mobile.svelte';
  import EmployeeTopNavbar from './EmployeeTopNavbar.svelte';
  import EmployeeBottomNav from './EmployeeBottomNav.svelte';

  export let user: any;
  export let currentPage: string = '';
</script>

<div class="min-h-screen bg-gray-50">
  <!-- Top Navigation Bar (Always visible) -->
  <EmployeeTopNavbar {user} {currentPage} />

  <!-- Main Content Area -->
  <main class="pb-20 md:pb-0">
    <!-- Breadcrumb Navigation (Desktop only) -->
    <div class="hidden md:block border-b bg-white">
      <div class="container mx-auto px-4 py-3">
        <slot name="breadcrumb" />
      </div>
    </div>

    <slot />
  </main>

  <!-- Bottom Navigation (Mobile only) -->
  {#if $isMobile}
    <EmployeeBottomNav {currentPage} />
  {/if}
</div>

<style>
  /* Custom scrollbar for web */
  @media (min-width: 768px) {
    ::-webkit-scrollbar {
      width: 6px;
    }

    ::-webkit-scrollbar-track {
      background: #f1f1f1;
    }

    ::-webkit-scrollbar-thumb {
      background: #c1c1c1;
      border-radius: 3px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: #a8a8a8;
    }
  }
</style>
