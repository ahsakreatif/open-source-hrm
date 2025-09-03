<script lang="ts">
  import { router } from '@inertiajs/svelte';
  import { Home, User, Clock, FileText, Bell, CreditCard } from 'lucide-svelte';

  export let currentPage: string;

  const navigationItems = [
    { name: 'Dashboard', href: '/dashboard', icon: Home },
    { name: 'Profile', href: '#', icon: User },
    { name: 'Attendance', href: '#', icon: Clock },
    { name: 'Leave', href: '#', icon: FileText },
    { name: 'News', href: '#', icon: Bell },
    { name: 'Payslip', href: '#', icon: CreditCard },
  ];

  function handleNavigation(href: string) {
    router.visit(href);
  }
</script>

<nav class="fixed bottom-0 left-0 right-0 z-50 border-t bg-white md:hidden">
  <div class="flex h-16 items-center justify-around">
    {#each navigationItems as item}
      {@const Icon = item.icon}
      {@const isActive = currentPage === item.href}
      <button
        on:click={() => handleNavigation(item.href)}
        class="flex flex-col items-center justify-center gap-1 px-2 py-1 transition-colors hover:text-primary {isActive ? 'text-primary' : 'text-gray-500'}"
      >
        <Icon class="h-5 w-5" />
        <span class="text-xs font-medium">{item.name}</span>
      </button>
    {/each}
  </div>
</nav>
