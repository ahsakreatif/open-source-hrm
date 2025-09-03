<script lang="ts">
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
  import { Button } from '../../components/ui/button';
  import { Input } from '../../components/ui/input';
  import { Label } from '../../components/ui/label';
  import { Avatar, AvatarFallback, AvatarImage } from '../../components/ui/avatar';
  import { Breadcrumb, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator, Item, BreadcrumbLink } from '../../components/ui/breadcrumb';
  import { Link } from '@inertiajs/svelte';
  import { User, Mail, Phone, MapPin, Building, Calendar, Edit } from 'lucide-svelte';
  import EmployeeLayout from '../../layouts/employee/EmployeeLayout.svelte';

  export let user: any;

  $: fullName = `${user?.first_name || ''} ${user?.last_name || ''}`.trim();
  $: initials = `${user?.first_name?.[0] || ''}${user?.last_name?.[0] || ''}`.toUpperCase();

  const breadcrumbItems = [
    { title: 'Profile', href: undefined }
  ];
</script>

<svelte:head>
  <title>Profile - Employee Portal</title>
</svelte:head>

<EmployeeLayout {user} currentPage="/employee/profile">
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

  <div class="container mx-auto px-4 py-6 max-w-4xl">
  <!-- Page Header -->
  <div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 md:text-3xl">Profile</h1>
    <p class="text-gray-600 mt-2">Manage your personal information and account settings</p>
  </div>

  <div class="grid gap-6 md:grid-cols-3">
    <!-- Profile Card -->
    <div class="md:col-span-1">
      <Card>
        <CardHeader class="text-center">
          <div class="flex justify-center mb-4">
            <Avatar class="h-24 w-24">
              <AvatarImage src={user?.profile_photo_url} alt={fullName} />
              <AvatarFallback class="text-2xl font-semibold">{initials}</AvatarFallback>
            </Avatar>
          </div>
          <CardTitle class="text-xl">{fullName}</CardTitle>
          <CardDescription>{user?.email}</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <Button class="w-full" variant="outline">
            <Edit class="mr-2 h-4 w-4" />
            Change Photo
          </Button>
        </CardContent>
      </Card>
    </div>

    <!-- Profile Information -->
    <div class="md:col-span-2">
      <Card>
        <CardHeader>
          <CardTitle>Personal Information</CardTitle>
          <CardDescription>Update your personal details and contact information</CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
          <!-- Basic Information -->
          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <Label for="first_name">First Name</Label>
              <Input id="first_name" value={user?.first_name || ''} disabled />
            </div>
            <div class="space-y-2">
              <Label for="last_name">Last Name</Label>
              <Input id="last_name" value={user?.last_name || ''} disabled />
            </div>
          </div>

          <div class="space-y-2">
            <Label for="email">Email Address</Label>
            <Input id="email" type="email" value={user?.email || ''} disabled />
          </div>

          <div class="space-y-2">
            <Label for="phone">Phone Number</Label>
            <Input id="phone" value={user?.phone || 'Not provided'} disabled />
          </div>

          <div class="space-y-2">
            <Label for="address">Address</Label>
            <Input id="address" value={user?.address || 'Not provided'} disabled />
          </div>

          <!-- Work Information -->
          <div class="pt-4 border-t">
            <h3 class="text-lg font-semibold mb-4">Work Information</h3>
            <div class="grid gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="employee_id">Employee ID</Label>
                <Input id="employee_id" value={user?.employee_id || 'N/A'} disabled />
              </div>
              <div class="space-y-2">
                <Label for="department">Department</Label>
                <Input id="department" value={user?.department?.name || 'N/A'} disabled />
              </div>
              <div class="space-y-2">
                <Label for="position">Position</Label>
                <Input id="position" value={user?.position?.name || 'N/A'} disabled />
              </div>
              <div class="space-y-2">
                <Label for="hire_date">Hire Date</Label>
                <Input id="hire_date" value={user?.hire_date || 'N/A'} disabled />
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="pt-4 border-t">
            <Button class="w-full md:w-auto">
              <Edit class="mr-2 h-4 w-4" />
              Edit Profile
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</EmployeeLayout>
