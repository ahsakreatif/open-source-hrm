<script lang="ts">
  import { onMount, onDestroy } from 'svelte';
  import { router } from '@inertiajs/svelte';
  import { Button } from '../../components/ui/button';
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
  import { Badge } from '../../components/ui/badge';
  import { Alert, AlertDescription } from '../../components/ui/alert';
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

  let currentTime = '';
  let currentDate = '';
  let currentLocation: GeolocationPosition | null = null;
  let locationError = '';
  let isLocationLoading = true;
  let isSubmitting = false;
  let attendanceStatus = 'not_checked_in';
  let todayAttendance: { check_in_time?: string; check_out_time?: string } | null = null;
  let officeLocation = { lat: -1.2921, lng: 36.8219 }; // Default: Nairobi coordinates
  let maxRadius = 100; // 100 meters
  let timeRestrictions = {
    checkInStart: '07:00',
    checkInEnd: '09:00',
    checkOutStart: '17:00',
    checkOutEnd: '19:00'
  };

  let timeInterval: ReturnType<typeof setInterval>;

  onMount(() => {
    updateDateTime();
    timeInterval = setInterval(updateDateTime, 1000);
    getCurrentLocation();
    loadTodayAttendance();
  });

  onDestroy(() => {
    if (timeInterval) clearInterval(timeInterval);
  });

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

  function loadTodayAttendance() {
    // TODO: Load actual attendance data from API
    // For now, simulate loading
    setTimeout(() => {
      // Simulate attendance status
      attendanceStatus = 'not_checked_in';
    }, 1000);
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

    if (action === 'checkIn') {
      return currentTimeStr >= timeRestrictions.checkInStart &&
             currentTimeStr <= timeRestrictions.checkInEnd;
    } else {
      return currentTimeStr >= timeRestrictions.checkOutStart &&
             currentTimeStr <= timeRestrictions.checkOutEnd;
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

    return { can: true };
  }

  async function submitAttendance(action: 'checkIn' | 'checkOut') {
    const validation = canPerformAction(action);
    if (!validation.can) {
      alert(validation.reason);
      return;
    }

    isSubmitting = true;

    try {
      // TODO: Submit to actual API
      const attendanceData = {
        employee_id: user.id,
        action: action,
        timestamp: new Date().toISOString(),
        latitude: currentLocation?.coords.latitude,
        longitude: currentLocation?.coords.longitude,
        accuracy: currentLocation?.coords.accuracy
      };

      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 2000));

      // Update local state
      attendanceStatus = action === 'checkIn' ? 'checked_in' : 'checked_out';

      // Show success message
      alert(`${action === 'checkIn' ? 'Check-in' : 'Check-out'} successful!`);

      // Redirect to dashboard
      router.visit('/employee/dashboard');

    } catch (error) {
      alert('Failed to submit attendance. Please try again.');
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

<div class="container mx-auto px-4 py-6 max-w-2xl">
  <!-- Header -->
  <div class="mb-6">
    <Button variant="ghost" on:click={() => router.visit('/employee/dashboard')} class="mb-4">
      ← Back to Dashboard
    </Button>
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Attendance</h1>
    <p class="text-gray-600">Submit your daily check-in and check-out</p>
  </div>

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
  <Card class="mb-6">
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <MapPin class="h-5 w-5" />
        Location Status
      </CardTitle>
    </CardHeader>
    <CardContent class="space-y-4">
      {#if isLocationLoading}
        <div class="text-center py-4">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
          <p class="text-sm text-gray-600 mt-2">Getting your location...</p>
        </div>
      {:else if locationError}
        <Alert variant="destructive">
          <AlertTriangle class="h-4 w-4" />
          <AlertDescription>{locationError}</AlertDescription>
        </Alert>
        <Button on:click={refreshLocation} variant="outline" class="w-full">
          <Navigation class="h-4 w-4 mr-2" />
          Try Again
        </Button>
      {:else if currentLocation}
        <div class="space-y-3">
          <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <span class="text-sm font-medium text-gray-700">Latitude:</span>
            <span class="text-sm text-gray-900">{currentLocation.coords.latitude.toFixed(6)}</span>
          </div>
          <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <span class="text-sm font-medium text-gray-700">Longitude:</span>
            <span class="text-sm text-gray-900">{currentLocation.coords.longitude.toFixed(6)}</span>
          </div>
          <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <span class="text-sm font-medium text-gray-700">Accuracy:</span>
            <span class="text-sm text-gray-900">±{Math.round(currentLocation.coords.accuracy)}m</span>
          </div>

          <!-- Office Radius Check -->
          <div class="flex items-center justify-between p-3 rounded-lg {isWithinOfficeRadius() ? 'bg-green-50' : 'bg-red-50'}">
            <span class="text-sm font-medium {isWithinOfficeRadius() ? 'text-green-700' : 'text-red-700'}">
              Office Radius Check
            </span>
            <Badge variant={isWithinOfficeRadius() ? 'default' : 'destructive'}>
              {isWithinOfficeRadius() ? 'Within Range' : 'Outside Range'}
            </Badge>
          </div>
        </div>
      {/if}
    </CardContent>
  </Card>

  <!-- Time Restrictions -->
  <Card class="mb-6">
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <Clock class="h-5 w-4" />
        Time Restrictions
      </CardTitle>
    </CardHeader>
    <CardContent>
      <div class="grid grid-cols-2 gap-4">
        <div class="text-center p-3 bg-blue-50 rounded-lg">
          <h4 class="font-medium text-blue-900">Check-in Window</h4>
          <p class="text-sm text-blue-700">{timeRestrictions.checkInStart} - {timeRestrictions.checkInEnd}</p>
        </div>
        <div class="text-center p-3 bg-green-50 rounded-lg">
          <h4 class="font-medium text-green-900">Check-out Window</h4>
          <p class="text-sm text-green-700">{timeRestrictions.checkOutStart} - {timeRestrictions.checkOutEnd}</p>
        </div>
      </div>
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
      <button
        on:click={() => submitAttendance('checkIn')}
        disabled={isSubmitting || !currentLocation || !isWithinOfficeRadius() || !isWithinTimeWindow('checkIn')}
        class="w-full h-12 bg-green-500 hover:bg-green-600 text-white rounded-md font-medium disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {#if isSubmitting}
          <div class="flex items-center space-x-2">
            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
            <span>Submitting...</span>
          </div>
        {:else}
          <CheckCircle class="h-4 w-4 mr-2" />
          Check In
        {/if}
              </button>

      <!-- Check-out Button -->
      <button
        on:click={() => submitAttendance('checkOut')}
        disabled={isSubmitting || !currentLocation || !isWithinOfficeRadius() || !isWithinTimeWindow('checkOut')}
        class="w-full h-12 bg-red-500 hover:bg-red-600 text-white rounded-md font-medium disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {#if isSubmitting}
          <div class="flex items-center space-x-2">
            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
            <span>Submitting...</span>
          </div>
        {:else}
          <XCircle class="h-4 w-4 mr-2" />
          Check Out
        {/if}
      </button>

      <!-- Status Messages -->
      {#if !currentLocation}
        <Alert>
          <AlertTriangle class="h-4 w-4" />
          <AlertDescription>Location access required to submit attendance.</AlertDescription>
        </Alert>
      {:else if !isWithinOfficeRadius()}
        <Alert variant="destructive">
          <AlertTriangle class="h-4 w-4" />
          <AlertDescription>You must be within 100m of the office to submit attendance.</AlertDescription>
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
            <span class="text-sm font-medium text-gray-700">Check-in:</span>
            <span class="text-sm text-gray-900">{todayAttendance.check_in_time || 'Not checked in'}</span>
          </div>
          <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <span class="text-sm font-medium text-gray-700">Check-out:</span>
            <span class="text-sm text-gray-900">{todayAttendance.check_out_time || 'Not checked out'}</span>
          </div>
        </div>
      {:else}
        <p class="text-center text-gray-500 py-4">No attendance records for today</p>
      {/if}
    </CardContent>
  </Card>
</div>
