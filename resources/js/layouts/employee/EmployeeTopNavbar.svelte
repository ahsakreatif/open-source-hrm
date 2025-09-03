<script lang="ts">
  import { router } from '@inertiajs/svelte';
  import { createEventDispatcher } from 'svelte';
  import { Button } from '../../components/ui/button';
  import { Link } from '@inertiajs/svelte';
  import { Avatar, AvatarFallback, AvatarImage } from '../../components/ui/avatar';
  import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '../../components/ui/dropdown-menu';
  import { LogOut, User, Settings, Bell, Home, Clock, FileText, Megaphone, Receipt } from 'lucide-svelte';
  import AppLogo from '../../components/AppLogo.svelte';

  export let user: any;
  export let currentPage: string = '';

  const dispatch = createEventDispatcher();

  function handleLogout() {
    router.post(route('employee.logout'));
  }

  const navigationItems = [
    { href: '/employee/dashboard', label: 'Dashboard', icon: Home, title: 'Dashboard' },
    { href: '/employee/attendance', label: 'Attendance', icon: Clock, title: 'Attendance' },
    { href: '/employee/leave-requests', label: 'Leave', icon: FileText, title: 'Leave Requests' },
    { href: '/employee/announcements', label: 'Announcements', icon: Megaphone, title: 'Announcements' },
    { href: '/employee/payslip', label: 'Payslip', icon: Receipt, title: 'Payslip' }
  ];

  function getPageTitle(pathname: string): string {
    const item = navigationItems.find(item => item.href === pathname);
    return item?.title || 'Employee Portal';
  }

  $: pageTitle = getPageTitle(currentPage || '/dashboard');
</script>

<header class="sticky top-0 z-50 w-full border-b bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/60">
  <div class="container flex h-16 items-center justify-between px-4 md:px-6">
    <!-- Logo and Title -->
    <div class="flex items-center gap-3">
      <!-- <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary">
        <span class="text-sm font-bold text-primary-foreground">HR</span>
      </div> -->
      <Link href={route('dashboard')}>
        <AppLogo class="size-15 fill-current text-white" />
        <div class="hidden md:block">
            <h1 class="text-lg font-semibold text-gray-900">{pageTitle}</h1>
        </div>
      </Link>
    </div>

    <!-- Navigation Menu (Desktop only) -->
    <nav class="hidden md:flex items-center gap-1">
      {#each navigationItems as item}
        {@const Icon = item.icon}
        <Button
          variant={currentPage === item.href ? 'default' : 'ghost'}
          size="sm"
          onclick={() => router.visit(item.href)}
          class="h-9 px-3"
        >
          <Icon class="h-4 w-4 mr-2" />
          {item.label}
        </Button>
      {/each}
    </nav>

    <!-- Right side - User menu and notifications -->
    <div class="flex items-center gap-3">
      <!-- Notifications (Desktop only) -->
      <div class="hidden md:block">
        <Button variant="ghost" size="icon" class="relative">
          <Bell class="h-5 w-5" />
          <span class="absolute -top-1 -right-1 h-3 w-3 rounded-full bg-red-500"></span>
        </Button>
      </div>

      <!-- User Menu -->
      <DropdownMenu>
        <DropdownMenuTrigger>
          <Button variant="ghost" class="relative h-8 w-8 rounded-full">
            <Avatar class="h-8 w-8">
              <AvatarImage src={user?.profile_photo_url} alt={user?.name} />
              <AvatarFallback>
                {user?.first_name?.[0]}{user?.last_name?.[0] || 'E'}
              </AvatarFallback>
            </Avatar>
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent class="w-56" align="end">
          <div class="flex items-center justify-start gap-2 p-2">
            <div class="flex flex-col space-y-1 leading-none">
              <p class="font-medium text-sm">{user?.name}</p>
              <p class="w-[200px] truncate text-xs text-muted-foreground">
                {user?.email}
              </p>
            </div>
          </div>
          <DropdownMenuSeparator />
          <DropdownMenuItem onclick={() => router.visit('/employee/profile')}>
            <User class="mr-2 h-4 w-4" />
            <span>Profile</span>
          </DropdownMenuItem>
          <DropdownMenuItem onclick={() => router.visit('/employee/settings')}>
            <Settings class="mr-2 h-4 w-4" />
            <span>Settings</span>
          </DropdownMenuItem>
          <DropdownMenuSeparator />
          <DropdownMenuItem onSelect={handleLogout}>
            <LogOut class="mr-2 h-4 w-4" />
            <span>Log out</span>
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  </div>
</header>
