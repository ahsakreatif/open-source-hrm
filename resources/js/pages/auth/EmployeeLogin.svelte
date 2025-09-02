<script lang="ts">
  import { createEventDispatcher } from 'svelte';
  import { Button } from '../../components/ui/button';
  import { Input } from '../../components/ui/input';
  import { Label } from '../../components/ui/label';
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
  import { Alert, AlertDescription } from '../../components/ui/alert';
  import { Eye, EyeOff, Building2 } from 'lucide-svelte';

  const dispatch = createEventDispatcher();

  let email = '';
  let password = '';
  let rememberMe = false;
  let showPassword = false;
  let isLoading = false;
  let errors: Record<string, string> = {};

  function handleSubmit() {
    if (!email || !password) {
      errors = {
        email: !email ? 'Email is required' : '',
        password: !password ? 'Password is required' : ''
      };
      return;
    }

    isLoading = true;
    errors = {};

    // Submit form data
    const formData = new FormData();
    formData.append('email', email);
    formData.append('password', password);
    formData.append('remember', rememberMe ? '1' : '0');

    dispatch('submit', { formData });
  }

  function togglePasswordVisibility() {
    showPassword = !showPassword;
  }
</script>

<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
  <div class="w-full max-w-md">
    <!-- Logo and Title -->
    <div class="text-center mb-8">
      <div class="flex justify-center mb-4">
        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-primary shadow-lg">
          <Building2 class="h-8 w-8 text-primary-foreground" />
        </div>
      </div>
      <h1 class="text-2xl font-bold text-gray-900 mb-2">Employee Portal</h1>
      <p class="text-gray-600">Sign in to access your HR dashboard</p>
    </div>

    <!-- Login Form -->
    <Card class="shadow-xl border-0">
      <CardHeader class="text-center pb-4">
        <CardTitle class="text-xl">Welcome Back</CardTitle>
        <CardDescription>Enter your credentials to continue</CardDescription>
      </CardHeader>

      <CardContent class="space-y-4">
        <form on:submit|preventDefault={handleSubmit} class="space-y-4">
          <!-- Email Field -->
          <div class="space-y-2">
            <Label for="email">Email Address</Label>
            <Input
              id="email"
              type="email"
              bind:value={email}
              placeholder="Enter your email"
              class="h-12 {errors.email ? 'border-red-500' : ''}"
            />
            {#if errors.email}
              <p class="text-sm text-red-500">{errors.email}</p>
            {/if}
          </div>

          <!-- Password Field -->
          <div class="space-y-2">
            <Label for="password">Password</Label>
            <div class="relative">
              <Input
                id="password"
                type={showPassword ? 'text' : 'password'}
                bind:value={password}
                placeholder="Enter your password"
                class="h-12 pr-12 {errors.password ? 'border-red-500' : ''}"
              />
              <button
                type="button"
                on:click={togglePasswordVisibility}
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
              >
                {#if showPassword}
                  <EyeOff class="h-5 w-5" />
                {:else}
                  <Eye class="h-5 w-5" />
                {/if}
              </button>
            </div>
            {#if errors.password}
              <p class="text-sm text-red-500">{errors.password}</p>
            {/if}
          </div>

          <!-- Remember Me -->
          <div class="flex items-center justify-between">
            <label class="flex items-center space-x-2">
              <input
                type="checkbox"
                bind:checked={rememberMe}
                class="rounded border-gray-300 text-primary focus:ring-primary"
              />
              <span class="text-sm text-gray-600">Remember me</span>
            </label>
          </div>

          <!-- Submit Button -->
          <Button
            type="submit"
            class="w-full h-12 text-base font-medium"
            disabled={isLoading}
          >
            {#if isLoading}
              <div class="flex items-center space-x-2">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                <span>Signing in...</span>
              </div>
            {:else}
              Sign In
            {/if}
          </Button>
        </form>

        <!-- Additional Links -->
        <div class="text-center pt-4">
          <a href="/login" class="text-sm text-primary hover:underline">
            Admin Login
          </a>
        </div>
      </CardContent>
    </Card>

    <!-- Footer -->
    <div class="text-center mt-6">
      <p class="text-sm text-gray-500">
        © 2024 HR Management System. All rights reserved.
      </p>
    </div>
  </div>
</div>
