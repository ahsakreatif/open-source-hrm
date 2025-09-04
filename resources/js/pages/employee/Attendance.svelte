<script lang="ts">
  import { onMount, onDestroy } from 'svelte';
  import { router } from '@inertiajs/svelte';
  import { Button } from '../../components/ui/button';
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
  import { Badge } from '../../components/ui/badge';
  import { Alert, AlertDescription } from '../../components/ui/alert';
  import { Breadcrumb, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator, Item, BreadcrumbLink } from '../../components/ui/breadcrumb';
  import { Link } from '@inertiajs/svelte';
  import EmployeeLayout from '../../layouts/employee/EmployeeLayout.svelte';
  import {
    Clock,
    MapPin,
    CheckCircle,
    XCircle,
    AlertTriangle,
    Navigation,
    Wifi,
    WifiOff
  } from 'lucide-svelte';

  export let user: any;

  // TypeScript interfaces
  interface OfficeLocation {
    lat: number;
    lng: number;
    name: string;
    address: string;
  }

  interface OfficeLocationInfo {
    name: string;
    address: string;
  }

  const breadcrumbItems = [
    { title: 'Attendance', href: undefined }
  ];

  let currentTime = '';
  let currentDate = '';
  let currentLocation: GeolocationPosition | null = null;
  let locationError = '';
  let isLocationLoading = true;
  let isSubmitting = false;
  let attendanceStatus = 'not_checked_in';
  let todayAttendance: any = null;
  let officeLocation: OfficeLocation = {
    lat: -6.389893,
    lng: 106.720359,
    name: 'South Jakarta Office',
    address: 'South Jakarta, Indonesia'
  };
  let maxRadius = 100; // 100 meters
  let officeLocationInfo: OfficeLocationInfo = {
    name: 'Default Office',
    address: 'Default Office Address'
  };
  let timeRestrictions = {
    checkInStart: '07:00',
    checkInEnd: '09:00',
    checkOutStart: '17:00',
    checkOutEnd: '19:00'
  };
  let canCheckIn = false;
  let canCheckOut = false;
  let timeWindows = {
    checkIn: { start: '07:00', end: '09:00', label: 'Check-in Window' },
    checkOut: { start: '17:00', end: '19:00', label: 'Check-out Window' }
  };

  let timeInterval: ReturnType<typeof setInterval>;
  let errorMessage = '';
  let successMessage = '';
  let isLoadingAttendance = true;
  let isLocationValidating = false;
  let locationValidationResult: any = null;

  onMount(() => {
    updateDateTime();
    timeInterval = setInterval(updateDateTime, 1000);
    getCurrentLocation();
    loadTodayAttendance();
  });

  onDestroy(() => {
    if (timeInterval) clearInterval(timeInterval);
  });

  function showMessage(message: string, type: 'success' | 'error') {
    if (type === 'success') {
      successMessage = message;
      setTimeout(() => successMessage = '', 5000);
    } else {
      errorMessage = message;
      setTimeout(() => errorMessage = '', 5000);
    }
  }

  function clearMessages() {
    errorMessage = '';
    successMessage = '';
  }

  // Helper function to safely get time window values
  function getTimeWindowValue(type: 'checkIn' | 'checkOut', field: 'start' | 'end'): string {
    if (timeWindows && timeWindows[type] && timeWindows[type][field]) {
      return timeWindows[type][field];
    }
    // Fallback values
    if (type === 'checkIn') {
      return field === 'start' ? '07:00' : '09:00';
    } else {
      return field === 'start' ? '17:00' : '19:00';
    }
  }

  function updateDateTime() {
    const now = new Date();
    currentTime = now.toLocaleTimeString('en-US', {
      hour12: false,
      hour: '2-digit',
      minute: '2-digit'
    });
    currentDate = now.toLocaleDateString('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  }

  function getCurrentLocation() {
    if (!navigator.geolocation) {
      locationError = 'Geolocation is not supported by this browser.';
      isLocationLoading = false;
      return;
    }

    navigator.geolocation.getCurrentPosition(
      (position) => {
        currentLocation = position;
        isLocationLoading = false;
        validateOfficeRadius();
      },
      (error) => {
        switch (error.code) {
          case error.PERMISSION_DENIED:
            locationError = 'Location access denied. Please enable location services.';
            break;
          case error.POSITION_UNAVAILABLE:
            locationError = 'Location information unavailable.';
            break;
          case error.TIMEOUT:
            locationError = 'Location request timed out.';
            break;
          default:
            locationError = 'An unknown error occurred while getting location.';
        }
        isLocationLoading = false;
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 60000
      }
    );
  }

  async function loadTodayAttendance() {
    isLoadingAttendance = true;
    try {
      const response = await fetch('/api/attendance/today', {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        credentials: 'same-origin'
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success) {
          todayAttendance = data.data.attendance;
          attendanceStatus = data.data.status;
          canCheckIn = data.data.can_check_in?.can || false;
          canCheckOut = data.data.can_check_out?.can || false;

          // Safely update time windows with fallback values
          if (data.data.time_windows) {
            timeWindows = {
              checkIn: {
                start: data.data.time_windows.check_in?.start || '07:00',
                end: data.data.time_windows.check_in?.end || '09:00',
                label: data.data.time_windows.check_in?.label || 'Check-in Window'
              },
              checkOut: {
                start: data.data.time_windows.check_out?.start || '17:00',
                end: data.data.time_windows.check_out?.end || '19:00',
                label: data.data.time_windows.check_out?.label || 'Check-out Window'
              }
            };

            // Also update timeRestrictions to keep them in sync
            timeRestrictions = {
              checkInStart: timeWindows.checkIn.start,
              checkInEnd: timeWindows.checkIn.end,
              checkOutStart: timeWindows.checkOut.start,
              checkOutEnd: timeWindows.checkOut.end
            };
          }
        }
      }
    } catch (error) {
      console.error('Failed to load attendance data:', error);
      showMessage('Failed to load attendance data. Please refresh the page.', 'error');

      // Ensure we have fallback values even when API fails
      timeWindows = {
        checkIn: { start: '07:00', end: '09:00', label: 'Check-in Window' },
        checkOut: { start: '17:00', end: '19:00', label: 'Check-out Window' }
      };

      // Also update timeRestrictions with fallback values
      timeRestrictions = {
        checkInStart: '07:00',
        checkInEnd: '09:00',
        checkOutStart: '17:00',
        checkOutEnd: '19:00'
      };
    } finally {
      isLoadingAttendance = false;
    }
  }

  async function validateOfficeRadius() {
    if (!currentLocation) return;

    isLocationValidating = true;
    try {
      const response = await fetch('/api/attendance/validate-location', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
          latitude: currentLocation.coords.latitude,
          longitude: currentLocation.coords.longitude
        })
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success) {
          // Store the validation result
          locationValidationResult = data.data;

          // Update office location from backend
          officeLocation = {
            lat: data.data.office_location.latitude,
            lng: data.data.office_location.longitude,
            name: data.data.office_location.name || 'Default Office',
            address: data.data.office_location.address || 'Default Office Address'
          };
          maxRadius = data.data.max_radius;
          officeLocationInfo = {
            name: data.data.office_location.name || 'Default Office',
            address: data.data.office_location.address || 'Default Office Address'
          };
        }
      }
    } catch (error) {
      console.error('Failed to validate location:', error);
      showMessage('Failed to validate location. Please try again.', 'error');
    } finally {
      isLocationValidating = false;
    }
  }

  function calculateDistance(lat1: number, lon1: number, lat2: number, lon2: number): number {
    const R = 6371e3; // Earth's radius in meters
    const φ1 = lat1 * Math.PI / 180;
    const φ2 = lat2 * Math.PI / 180;
    const Δφ = (lat2 - lat1) * Math.PI / 180;
    const Δλ = (lon2 - lon1) * Math.PI / 180;

    const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
              Math.cos(φ1) * Math.cos(φ2) *
              Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    return R * c;
  }

    function isWithinOfficeRadius(): boolean {
    if (!currentLocation) return false;

    // If we have backend validation result, use it
    if (locationValidationResult) {
      return locationValidationResult.is_within_radius;
    }

    // Fallback to frontend calculation if backend validation hasn't completed yet
    const distance = calculateDistance(
      currentLocation.coords.latitude,
      currentLocation.coords.longitude,
      officeLocation.lat,
      officeLocation.lng
    );

    return distance <= maxRadius;
  }

      function isWithinTimeWindow(action: 'checkIn' | 'checkOut'): boolean {
    const currentTimeStr = currentTime;

    // Convert time strings to minutes for proper numeric comparison
    function timeToMinutes(timeStr: string): number {
      const [hours, minutes] = timeStr.split(':').map(Number);
      return hours * 60 + minutes;
    }

    const currentMinutes = timeToMinutes(currentTimeStr);
    const checkInStartMinutes = timeToMinutes(timeRestrictions.checkInStart);
    const checkInEndMinutes = timeToMinutes(timeRestrictions.checkInEnd);
    const checkOutStartMinutes = timeToMinutes(timeRestrictions.checkOutStart);
    const checkOutEndMinutes = timeToMinutes(timeRestrictions.checkOutEnd);

    if (action === 'checkIn') {
      return currentMinutes >= checkInStartMinutes && currentMinutes <= checkInEndMinutes;
    } else {
      return currentMinutes >= checkOutStartMinutes && currentMinutes <= checkOutEndMinutes;
    }
  }

  function canPerformAction(action: 'checkIn' | 'checkOut'): { can: boolean; reason?: string } {
    if (!currentLocation) {
      return { can: false, reason: 'Location not available' };
    }

    if (!isWithinOfficeRadius()) {
      return { can: false, reason: 'You are outside the office radius (100m)' };
    }

    if (!isWithinTimeWindow(action)) {
      const timeWindow = action === 'checkIn'
        ? `${timeRestrictions.checkInStart} - ${timeRestrictions.checkInEnd}`
        : `${timeRestrictions.checkOutStart} - ${timeRestrictions.checkOutEnd}`;
      return { can: false, reason: `Outside ${action} time window (${timeWindow})` };
    }

    // Check backend validation
    if (action === 'checkIn' && !canCheckIn) {
      return { can: false, reason: 'Check-in not allowed at this time' };
    }

    if (action === 'checkOut' && !canCheckOut) {
      return { can: false, reason: 'Check-out not allowed at this time' };
    }

    return { can: true };
  }

  async function submitAttendance(action: 'checkIn' | 'checkOut') {
    const validation = canPerformAction(action);
    if (!validation.can) {
      showMessage(validation.reason || 'Cannot perform this action', 'error');
      return;
    }

    isSubmitting = true;
    clearMessages();

    try {
      const endpoint = action === 'checkIn' ? '/api/attendance/check-in' : '/api/attendance/check-out';

      const response = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
          latitude: currentLocation?.coords.latitude,
          longitude: currentLocation?.coords.longitude,
          accuracy: currentLocation?.coords.accuracy ? Math.round(currentLocation.coords.accuracy) : undefined
        })
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success) {
          // Update local state
          attendanceStatus = data.data.status;
          todayAttendance = data.data.attendance;

          // Show success message
          showMessage(`${action === 'checkIn' ? 'Check-in' : 'Check-out'} successful!`, 'success');

          // Reload attendance data
          await loadTodayAttendance();

          // Redirect to dashboard after a short delay
          setTimeout(() => {
            router.visit('/dashboard');
          }, 2000);
        } else {
          showMessage(data.message || 'Failed to submit attendance', 'error');
        }
      } else {
        const errorData = await response.json();
        showMessage(errorData.message || 'Failed to submit attendance', 'error');
      }

    } catch (error) {
      console.error('Attendance submission error:', error);
      showMessage('Failed to submit attendance. Please try again.', 'error');
    } finally {
      isSubmitting = false;
    }
  }

  function refreshLocation() {
    isLocationLoading = true;
    locationError = '';
    getCurrentLocation();
  }
</script>

<svelte:head>
  <title>Attendance - Employee Portal</title>
</svelte:head>

<EmployeeLayout {user} currentPage="/employee/attendance">
  <svelte:fragment slot="breadcrumb">
    <Breadcrumb>
      <BreadcrumbList>
        {#each breadcrumbItems as item, index (index)}
          <Item>
            {#if index === breadcrumbItems.length - 1}
              <BreadcrumbPage>{item.title}</BreadcrumbPage>
            {:else}
              <BreadcrumbLink>
                <Link href={item.href ?? '#'}>{item.title}</Link>
              </BreadcrumbLink>
            {/if}
          </Item>
          {#if index !== breadcrumbItems.length - 1}
            <BreadcrumbSeparator />
          {/if}
        {/each}
      </BreadcrumbList>
    </Breadcrumb>
  </svelte:fragment>

  <div class="container mx-auto px-4 py-6 max-w-2xl">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 mb-2">Attendance</h1>
      <p class="text-gray-600">Submit your daily check-in and check-out</p>
    </div>

    <!-- Success Message -->
    {#if successMessage}
      <Alert class="mb-6">
        <CheckCircle class="h-4 w-4" />
        <AlertDescription>{successMessage}</AlertDescription>
      </Alert>
    {/if}

    <!-- Error Message -->
    {#if errorMessage}
      <Alert variant="destructive" class="mb-6">
        <AlertTriangle class="h-4 w-4" />
        <AlertDescription>{errorMessage}</AlertDescription>
      </Alert>
    {/if}

  <!-- Current Time and Date -->
  <Card class="mb-6 bg-gradient-to-r from-blue-500 to-indigo-600 text-white">
    <CardContent class="p-6">
      <div class="text-center">
        <div class="text-4xl font-bold mb-2">{currentTime}</div>
        <div class="text-lg opacity-90">{currentDate}</div>
      </div>
    </CardContent>
  </Card>

  <!-- Location Status -->
  {#if currentLocation}
    <div class="space-y-3">
      <h3 class="text-lg font-semibold text-gray-900">Location Status</h3>

      <!-- GPS Coordinates -->
      <div class="grid grid-cols-3 gap-4 p-3 bg-gray-50 rounded-lg">
        <div>
          <div class="text-xs text-gray-500">Latitude</div>
          <div class="text-sm font-medium">{currentLocation.coords.latitude.toFixed(6)}</div>
        </div>
        <div>
          <div class="text-xs text-gray-500">Longitude</div>
          <div class="text-sm font-medium">{currentLocation.coords.longitude.toFixed(6)}</div>
        </div>
        <div>
          <div class="text-xs text-gray-500">Accuracy</div>
          <div class="text-sm font-medium">±{currentLocation.coords.accuracy}m</div>
        </div>
      </div>

      <!-- Office Radius Check -->
      {#if isLocationValidating}
        <div class="flex items-center justify-center p-3 bg-blue-50 rounded-lg">
          <div class="text-sm text-blue-700">Validating location...</div>
        </div>
      {:else if locationValidationResult}
        <div class="space-y-3">
          <!-- Distance Info -->
          <div class="p-3 bg-blue-50 rounded-lg">
            <div class="text-sm font-medium text-blue-700 mb-1">Distance to Office</div>
            <div class="text-xs text-blue-600">
              <div><strong>{locationValidationResult.distance}m</strong> from office</div>
              <div>Max allowed: {locationValidationResult.max_radius}m</div>
            </div>
          </div>

          <!-- Office Radius Check -->
          <div class="flex items-center justify-between p-3 rounded-lg {locationValidationResult.is_within_radius ? 'bg-green-50' : 'bg-red-50'}">
            <span class="text-sm font-medium {locationValidationResult.is_within_radius ? 'text-green-700' : 'text-red-700'}">
              Office Radius Check
            </span>
            <Badge variant={locationValidationResult.is_within_radius ? 'default' : 'destructive'}>
              {locationValidationResult.is_within_radius ? 'Within Range' : 'Outside Range'}
            </Badge>
          </div>

          <!-- Office Location Info -->
          <div class="p-3 bg-blue-50 rounded-lg">
            <div class="text-sm font-medium text-blue-700 mb-1">Office Location</div>
            <div class="text-xs text-blue-600">
              <div><strong>{officeLocationInfo.name}</strong></div>
              <div>{officeLocationInfo.address}</div>
            </div>
          </div>
        </div>
      {:else if !locationValidationResult}
        <div class="p-3 bg-yellow-50 rounded-lg">
          <div class="text-sm text-yellow-700">Location not yet validated</div>
        </div>
      {:else if !locationValidationResult?.is_within_radius}
        <div class="text-center py-4">
          <MapPin class="h-12 w-12 text-red-400 mx-auto mb-3" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">Outside Office Range</h3>
          <p class="text-sm text-gray-600 mb-4">
            You are currently {locationValidationResult?.distance}m from the office.
            Please move within {locationValidationResult?.max_radius}m to submit attendance.
          </p>
          <Button onclick={refreshLocation} variant="outline">
            <Navigation class="h-4 w-4 mr-2" />
            Refresh Location
          </Button>
        </div>
      {/if}
    </div>
  {/if}

  <!-- Time Restrictions -->
  <Card class="mb-6">
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <Clock class="h-5 w-4" />
        Time Restrictions
      </CardTitle>
    </CardHeader>
    <CardContent>
      {#if isLoadingAttendance}
        <div class="text-center py-4">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
          <p class="text-sm text-gray-600 mt-2">Loading time windows...</p>
        </div>
      {:else}
        <div class="grid grid-cols-2 gap-4">
          <div class="text-center p-3 bg-blue-50 rounded-lg">
            <h4 class="font-medium text-blue-900">Check-in Window</h4>
            <p class="text-sm text-blue-700">{getTimeWindowValue('checkIn', 'start')} - {getTimeWindowValue('checkIn', 'end')}</p>
          </div>
          <div class="text-center p-3 bg-green-50 rounded-lg">
            <h4 class="font-medium text-green-900">Check-out Window</h4>
            <p class="text-sm text-green-700">{getTimeWindowValue('checkOut', 'start')} - {getTimeWindowValue('checkOut', 'end')}</p>
          </div>
        </div>
      {/if}
    </CardContent>
  </Card>

  <!-- Attendance Actions -->
  <Card class="mb-6">
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <CheckCircle class="h-5 w-5" />
        Submit Attendance
      </CardTitle>
    </CardHeader>
    <CardContent class="space-y-4">
      <!-- Check-in Button -->
      <Button
        onclick={() => submitAttendance('checkIn')}
        disabled={isSubmitting || !currentLocation || !isWithinOfficeRadius() || !isWithinTimeWindow('checkIn') || !canCheckIn}
        class="w-full"
      >
        <Clock class="h-4 w-4 mr-2" />
        Check In
      </Button>

      <!-- Check-out Button -->
      <Button
        onclick={() => submitAttendance('checkOut')}
        disabled={isSubmitting || !currentLocation || !isWithinOfficeRadius() || !isWithinTimeWindow('checkOut') || !canCheckOut}
        variant="outline"
        class="w-full"
      >
        <Clock class="h-4 w-4 mr-2" />
        Check Out
      </Button>

      <!-- Status Messages -->
      {#if !currentLocation}
        <Alert>
          <AlertTriangle class="h-4 w-4" />
          <AlertDescription>Location access required to submit attendance.</AlertDescription>
        </Alert>
      {:else if !isWithinOfficeRadius()}
        <Alert variant="destructive">
          <AlertTriangle class="h-4 w-4" />
          <AlertDescription>You must be within {maxRadius}m of the office to submit attendance.</AlertDescription>
        </Alert>
      {/if}


    </CardContent>
  </Card>

  <!-- Today's Attendance Summary -->
  <Card>
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <Clock class="h-5 w-5" />
        Today's Summary
      </CardTitle>
    </CardHeader>
    <CardContent>
      {#if todayAttendance}
        <div class="space-y-3">
          <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <span class="text-sm font-medium text-gray-700">Status:</span>
            <Badge variant={attendanceStatus === 'checked_in' ? 'default' : attendanceStatus === 'checked_out' ? 'secondary' : 'outline'}>
              {attendanceStatus === 'checked_in' ? 'Checked In' : attendanceStatus === 'checked_out' ? 'Checked Out' : 'Not Checked In'}
            </Badge>
          </div>
          <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <span class="text-sm font-medium text-gray-700">Check-in:</span>
            <span class="text-sm text-gray-900">{todayAttendance.clock_in || 'Not checked in'}</span>
          </div>
          <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <span class="text-sm font-medium text-gray-700">Check-out:</span>
            <span class="text-sm text-gray-900">{todayAttendance.clock_out || 'Not checked out'}</span>
          </div>
          {#if todayAttendance.hours}
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <span class="text-sm font-medium text-gray-700">Hours Worked:</span>
              <span class="text-sm text-gray-900">{todayAttendance.hours} hours</span>
            </div>
          {/if}
        </div>
      {:else}
        <p class="text-center text-gray-500 py-4">Loading attendance data...</p>
      {/if}
    </CardContent>
  </Card>
</div>
</EmployeeLayout>
