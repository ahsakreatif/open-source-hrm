<script lang="ts">
  import { onMount } from 'svelte';
  import { router } from '@inertiajs/svelte';
  import { Button } from '../../components/ui/button';
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
  import { Badge } from '../../components/ui/badge';
  import { Avatar, AvatarFallback, AvatarImage } from '../../components/ui/avatar';
  import type { Employee } from '@/types/models';
  import {
    Clock,
    MapPin,
    Calendar,
    FileText,
    Bell,
    CreditCard,
    User,
    CheckCircle,
    XCircle,
    AlertCircle
  } from 'lucide-svelte';
  import EmployeeLayout from '../../layouts/employee/EmployeeLayout.svelte';

  import { page } from '@inertiajs/svelte';

  let currentTime = $state('');
  let currentDate = $state('');
  let attendanceStatus = $state('not_checked_in'); // 'not_checked_in', 'checked_in', 'checked_out'
  let lastAttendance = $state(null);
  let isLoading = $state(true);

  let user = $derived($page.props.auth.user);

  onMount(() => {
    updateDateTime();
    setInterval(updateDateTime, 1000);
    loadAttendanceData();
  });

  function updateDateTime() {
    const now = new Date();
    currentTime = now.toLocaleTimeString('en-US', {
      hour12: true,
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    });
    currentDate = now.toLocaleDateString('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  }

  function loadAttendanceData() {
    // TODO: Load actual attendance data from API
    // For now, simulate loading
    setTimeout(() => {
      isLoading = false;
      // Simulate attendance status
      attendanceStatus = 'not_checked_in';
    }, 1000);
  }

  function getAttendanceStatusInfo() {
    switch (attendanceStatus) {
      case 'checked_in':
        return {
          status: 'Checked In',
          color: 'bg-green-100 text-green-800',
          icon: CheckCircle,
          action: 'Check Out',
          actionColor: 'bg-red-500 hover:bg-red-600'
        };
      case 'checked_out':
        return {
          status: 'Checked Out',
          color: 'bg-gray-100 text-gray-800',
          icon: XCircle,
          action: 'Check In',
          actionColor: 'bg-green-500 hover:bg-green-600'
        };
      default:
        return {
          status: 'Not Checked In',
          color: 'bg-yellow-100 text-yellow-800',
          icon: AlertCircle,
          action: 'Check In',
          actionColor: 'bg-green-500 hover:bg-green-600'
        };
    }
  }

  function handleAttendanceAction() {
    router.visit('/employee/attendance');
  }

  function navigateTo(path: string) {
    router.visit(path);
  }

  // Use $derived instead of $: for Svelte 5
  const attendanceInfo = $derived(getAttendanceStatusInfo());
</script>

<svelte:head>
  <title>Employee Dashboard</title>
</svelte:head>

<EmployeeLayout {user}>
    <div class="container mx-auto px-4 py-6 max-w-4xl">
      <!-- Header Section -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Welcome back, {user?.first_name}!</h1>
        <p class="text-gray-600">Here's what's happening today</p>
      </div>

      <!-- Time and Date Card -->
      <Card class="mb-6 bg-gradient-to-r from-blue-500 to-indigo-600 text-white">
        <CardContent class="p-6">
          <div class="text-center">
            <div class="text-4xl font-bold mb-2">{currentTime}</div>
            <div class="text-lg opacity-90">{currentDate}</div>
          </div>
        </CardContent>
      </Card>

      <!-- Profile Summary Card -->
      <Card class="mb-6">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <User class="h-5 w-5" />
            Profile Summary
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="flex items-center gap-4">
            <Avatar class="h-16 w-16">
              <AvatarImage src={user?.avatar} alt={user?.full_name || user?.name} />
              <AvatarFallback class="text-lg">
                {user?.first_name?.[0]}{user?.last_name?.[0] || 'E'}
              </AvatarFallback>
            </Avatar>
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-gray-900">{user?.full_name || user?.name}</h3>
              <p class="text-gray-600">{user?.position?.title || 'Position'}</p>
              <p class="text-gray-600">{user?.department?.name || 'Department'}</p>
              <p class="text-sm text-gray-500">Employee ID: {user?.employee_number}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Attendance Status Card -->
      <Card class="mb-6">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Clock class="h-5 w-5" />
            Today's Attendance
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="text-center mb-4">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full {attendanceInfo.color}">
              <svelte:component this={attendanceInfo.icon} class="h-4 w-4" />
              <span class="text-sm font-medium">{attendanceInfo.status}</span>
            </div>
          </div>

          {#if isLoading}
            <div class="text-center py-4">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
            </div>
          {:else}
            <Button
              onclick={handleAttendanceAction}
              class="w-full h-12 {attendanceInfo.actionColor} text-white"
            >
              {attendanceInfo.action}
            </Button>
          {/if}
        </CardContent>
      </Card>

      <!-- Quick Access Grid -->
      <div class="grid grid-cols-2 gap-4 mb-6">
        <button class="w-full" on:click={() => navigateTo('/employee/profile')}>
          <Card class="cursor-pointer hover:shadow-lg transition-shadow">
            <CardContent class="p-4 text-center">
              <User class="h-8 w-8 mx-auto mb-2 text-blue-500" />
              <h3 class="font-medium text-gray-900">Profile</h3>
              <p class="text-sm text-gray-600">Manage your information</p>
            </CardContent>
          </Card>
        </button>

        <button class="w-full" on:click={() => navigateTo('/employee/leave-requests')}>
          <Card class="cursor-pointer hover:shadow-lg transition-shadow">
            <CardContent class="p-4 text-center">
              <Calendar class="h-8 w-8 mx-auto mb-2 text-green-500" />
              <h3 class="font-medium text-gray-900">Leave Requests</h3>
              <p class="text-sm text-gray-600">Submit & track leaves</p>
            </CardContent>
          </Card>
        </button>

        <button class="w-full" on:click={() => navigateTo('/employee/announcements')}>
          <Card class="cursor-pointer hover:shadow-lg transition-shadow">
            <CardContent class="p-4 text-center">
            <Bell class="h-8 w-8 mx-auto mb-2 text-yellow-500" />
            <h3 class="font-medium text-gray-900">Announcements</h3>
            <p class="text-sm text-gray-600">Company updates</p>
            </CardContent>
          </Card>
        </button>

        <button class="w-full" on:click={() => navigateTo('/employee/payslip')}>
          <Card class="cursor-pointer hover:shadow-lg transition-shadow">
            <CardContent class="p-4 text-center">
              <CreditCard class="h-8 w-8 mx-auto mb-2 text-purple-500" />
              <h3 class="font-medium text-gray-900">Payslip</h3>
              <p class="text-sm text-gray-600">View salary details</p>
            </CardContent>
          </Card>
        </button>
      </div>

      <!-- Recent Activity Card -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <FileText class="h-5 w-5" />
            Recent Activity
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-3">
            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
              <div class="h-2 w-2 bg-green-500 rounded-full"></div>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">Attendance submitted</p>
                <p class="text-xs text-gray-500">Today at 8:30 AM</p>
              </div>
            </div>

            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
              <div class="h-2 w-2 bg-blue-500 rounded-full"></div>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">Leave request approved</p>
                <p class="text-xs text-gray-500">Yesterday at 2:15 PM</p>
              </div>
            </div>

            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
              <div class="h-2 w-2 bg-yellow-500 rounded-full"></div>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">New announcement</p>
                <p class="text-xs text-gray-500">2 days ago</p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
</EmployeeLayout>


