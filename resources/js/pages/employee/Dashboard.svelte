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
  import { Breadcrumb, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator, Item, BreadcrumbLink } from '../../components/ui/breadcrumb';
  import { Link } from '@inertiajs/svelte';

  import { page } from '@inertiajs/svelte';

  let currentTime = $state('');
  let currentDate = $state('');
  let attendanceStatus = $state('not_checked_in'); // 'not_checked_in', 'checked_in', 'checked_out'
  let lastAttendance = $state(null);
  let isLoading = $state(true);

  let user = $derived($page.props.auth.user);

  // Dummy data for demonstration
  const dummyEmployeeData = {
    first_name: 'John',
    last_name: 'Doe',
    full_name: 'John Doe',
    employee_number: 'EMP001',
    position: { title: 'Software Developer' },
    department: { name: 'Engineering' },
    avatar: null
  };

  // Enhanced dummy data for recent activities
  const dummyRecentActivities = [
    {
      id: 1,
      type: 'attendance',
      title: 'Attendance submitted',
      description: 'Checked in at 8:30 AM',
      time: 'Today at 8:30 AM',
      status: 'success',
      color: 'bg-green-500'
    },
    {
      id: 2,
      type: 'leave',
      title: 'Leave request approved',
      description: 'Annual leave for Dec 25-27',
      time: 'Yesterday at 2:15 PM',
      status: 'approved',
      color: 'bg-blue-500'
    },
    {
      id: 3,
      type: 'announcement',
      title: 'New announcement',
      description: 'Company holiday schedule updated',
      time: '2 days ago',
      status: 'info',
      color: 'bg-yellow-500'
    },
    {
      id: 4,
      type: 'payslip',
      title: 'Payslip generated',
      description: 'November 2024 salary details',
      time: '3 days ago',
      status: 'success',
      color: 'bg-purple-500'
    }
  ];

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
    // Load actual attendance data from API
    fetch('/api/attendance/today', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        attendanceStatus = data.data.status;
        lastAttendance = data.data.attendance;
      }
      isLoading = false;
    })
    .catch(error => {
      console.error('Failed to load attendance data:', error);
      // Fallback to dummy data if API fails
      attendanceStatus = 'not_checked_in';
      isLoading = false;
    });
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

  // Get user data with fallback to dummy data
  const displayUser = $derived(user || dummyEmployeeData);
</script>

<svelte:head>
  <title>Employee Dashboard</title>
</svelte:head>

<EmployeeLayout {user} currentPage="/dashboard">
  <svelte:fragment slot="breadcrumb">
    <Breadcrumb>
      <BreadcrumbList>
        <Item>
          <BreadcrumbPage>Dashboard</BreadcrumbPage>
        </Item>
      </BreadcrumbList>
    </Breadcrumb>
  </svelte:fragment>

  <div class="container mx-auto px-4 py-6 max-w-4xl">
      <!-- Header Section -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Welcome back, {displayUser.first_name}!</h1>
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
              <AvatarImage src={displayUser.avatar} alt={displayUser.full_name} />
              <AvatarFallback class="text-lg">
                {displayUser.first_name?.[0]}{displayUser.last_name?.[0] || 'E'}
              </AvatarFallback>
            </Avatar>
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-gray-900">{displayUser.full_name}</h3>
              <p class="text-gray-600">{displayUser.position?.title || 'Position'}</p>
              <p class="text-gray-600">{displayUser.department?.name || 'Department'}</p>
              <p class="text-sm text-gray-500">Employee ID: {displayUser.employee_number}</p>
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
              <div class="h-4 w-4">
                {#if attendanceInfo.icon === CheckCircle}
                  <CheckCircle class="h-4 w-4" />
                {:else if attendanceInfo.icon === XCircle}
                  <XCircle class="h-4 w-4" />
                {:else}
                  <AlertCircle class="h-4 w-4" />
                {/if}
              </div>
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
        <button class="w-full" onclick={() => navigateTo('/employee/profile')}>
          <Card class="cursor-pointer hover:shadow-lg transition-shadow">
            <CardContent class="p-4 text-center">
              <User class="h-8 w-8 mx-auto mb-2 text-blue-500" />
              <h3 class="font-medium text-gray-900">Profile</h3>
              <p class="text-sm text-gray-600">Manage your information</p>
            </CardContent>
          </Card>
        </button>

        <button class="w-full" onclick={() => navigateTo('/employee/leave-requests')}>
          <Card class="cursor-pointer hover:shadow-lg transition-shadow">
            <CardContent class="p-4 text-center">
              <Calendar class="h-8 w-8 mx-auto mb-2 text-green-500" />
              <h3 class="font-medium text-gray-900">Leave Requests</h3>
              <p class="text-sm text-gray-600">Submit & track leaves</p>
            </CardContent>
          </Card>
        </button>

        <button class="w-full" onclick={() => navigateTo('/employee/announcements')}>
          <Card class="cursor-pointer hover:shadow-lg transition-shadow">
            <CardContent class="p-4 text-center">
            <Bell class="h-8 w-8 mx-auto mb-2 text-yellow-500" />
            <h3 class="font-medium text-gray-900">Announcements</h3>
            <p class="text-sm text-gray-600">Company updates</p>
            </CardContent>
          </Card>
        </button>

        <button class="w-full" onclick={() => navigateTo('/employee/payslip')}>
          <Card class="cursor-pointer hover:shadow-lg transition-shadow">
            <CardContent class="p-4 text-center">
              <CreditCard class="h-8 w-8 mx-auto mb-2 text-purple-500" />
              <p class="font-medium text-gray-900">Payslip</p>
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
            {#each dummyRecentActivities as activity}
              <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <div class="h-2 w-2 {activity.color} rounded-full"></div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900">{activity.title}</p>
                  <p class="text-xs text-gray-500">{activity.description}</p>
                  <p class="text-xs text-gray-400">{activity.time}</p>
                </div>
              </div>
            {/each}
          </div>
        </CardContent>
      </Card>
    </div>
</EmployeeLayout>


