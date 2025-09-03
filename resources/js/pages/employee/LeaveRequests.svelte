<script lang="ts">
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
  import { Button } from '../../components/ui/button';
  import { Badge } from '../../components/ui/badge';
  import { Breadcrumb, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator, Item, BreadcrumbLink } from '../../components/ui/breadcrumb';
  import { Link } from '@inertiajs/svelte';
  import { Calendar, Plus, Clock, CheckCircle, XCircle, AlertCircle } from 'lucide-svelte';
  import EmployeeLayout from '../../layouts/employee/EmployeeLayout.svelte';

  export let user: any;

  const breadcrumbItems = [
    { title: 'Leave Requests', href: undefined }
  ];

  // Mock data - replace with actual data from backend
  const leaveRequests = [
    {
      id: 1,
      type: 'Annual Leave',
      start_date: '2024-01-15',
      end_date: '2024-01-17',
      days: 3,
      reason: 'Family vacation',
      status: 'approved',
      submitted_at: '2024-01-10'
    },
    {
      id: 2,
      type: 'Sick Leave',
      start_date: '2024-01-20',
      end_date: '2024-01-20',
      days: 1,
      reason: 'Not feeling well',
      status: 'pending',
      submitted_at: '2024-01-19'
    },
    {
      id: 3,
      type: 'Personal Leave',
      start_date: '2024-02-01',
      end_date: '2024-02-01',
      days: 1,
      reason: 'Personal appointment',
      status: 'rejected',
      submitted_at: '2024-01-25'
    }
  ];

  function getStatusBadge(status: string) {
    const statusConfig: Record<string, { variant: 'default' | 'secondary' | 'destructive'; icon: any; text: string }> = {
      approved: { variant: 'default', icon: CheckCircle, text: 'Approved' },
      pending: { variant: 'secondary', icon: Clock, text: 'Pending' },
      rejected: { variant: 'destructive', icon: XCircle, text: 'Rejected' }
    };

    const config = statusConfig[status] || statusConfig.pending;
    const Icon = config.icon;

    return {
      variant: config.variant,
      icon: Icon,
      text: config.text
    };
  }

  function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  }
</script>

<svelte:head>
  <title>Leave Requests - Employee Portal</title>
</svelte:head>

<EmployeeLayout {user} currentPage="/employee/leave-requests">
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

  <div class="container mx-auto px-4 py-6 max-w-6xl">
    <!-- Page Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 md:text-3xl">Leave Requests</h1>
      <p class="text-gray-600 mt-2">Manage your leave requests and view their status</p>
    </div>
    <Button class="w-full sm:w-auto">
      <Plus class="mr-2 h-4 w-4" />
      New Leave Request
    </Button>
  </div>

  <!-- Leave Balance Summary -->
  <div class="grid gap-4 mb-6 md:grid-cols-4">
    <Card>
      <CardContent class="p-4">
        <div class="flex items-center space-x-2">
          <Calendar class="h-5 w-5 text-blue-600" />
          <div>
            <p class="text-sm font-medium text-gray-600">Annual Leave</p>
            <p class="text-2xl font-bold text-gray-900">15</p>
            <p class="text-xs text-gray-500">days remaining</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardContent class="p-4">
        <div class="flex items-center space-x-2">
          <AlertCircle class="h-5 w-5 text-orange-600" />
          <div>
            <p class="text-sm font-medium text-gray-600">Sick Leave</p>
            <p class="text-2xl font-bold text-gray-900">10</p>
            <p class="text-xs text-gray-500">days remaining</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardContent class="p-4">
        <div class="flex items-center space-x-2">
          <Clock class="h-5 w-5 text-green-600" />
          <div>
            <p class="text-sm font-medium text-gray-600">Personal Leave</p>
            <p class="text-2xl font-bold text-gray-900">5</p>
            <p class="text-xs text-gray-500">days remaining</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardContent class="p-4">
        <div class="flex items-center space-x-2">
          <CheckCircle class="h-5 w-5 text-purple-600" />
          <div>
            <p class="text-sm font-medium text-gray-600">Total Used</p>
            <p class="text-2xl font-bold text-gray-900">8</p>
            <p class="text-xs text-gray-500">days this year</p>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>

  <!-- Leave Requests List -->
  <Card>
    <CardHeader>
      <CardTitle>Recent Leave Requests</CardTitle>
      <CardDescription>View the status of your submitted leave requests</CardDescription>
    </CardHeader>
    <CardContent>
      <div class="space-y-4">
        {#each leaveRequests as request}
          {@const statusBadge = getStatusBadge(request.status)}
          {@const Icon = statusBadge.icon}

          <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border rounded-lg hover:bg-gray-50 transition-colors">
            <div class="flex-1 space-y-2">
              <div class="flex items-center gap-2">
                <h3 class="font-semibold text-gray-900">{request.type}</h3>
                <Badge variant={statusBadge.variant} class="text-xs">
                  <Icon class="mr-1 h-3 w-3" />
                  {statusBadge.text}
                </Badge>
              </div>

              <div class="flex flex-col sm:flex-row sm:items-center gap-4 text-sm text-gray-600">
                <div class="flex items-center gap-1">
                  <Calendar class="h-4 w-4" />
                  <span>{formatDate(request.start_date)} - {formatDate(request.end_date)}</span>
                </div>
                <span class="text-gray-400 hidden sm:inline">•</span>
                <span>{request.days} day{request.days !== 1 ? 's' : ''}</span>
                <span class="text-gray-400 hidden sm:inline">•</span>
                <span class="truncate max-w-xs">{request.reason}</span>
              </div>

              <p class="text-xs text-gray-500">
                Submitted on {formatDate(request.submitted_at)}
              </p>
            </div>

            <div class="flex gap-2 mt-4 sm:mt-0">
              {#if request.status === 'pending'}
                <Button variant="outline" size="sm">Edit</Button>
                <Button variant="outline" size="sm" class="text-red-600 hover:text-red-700">Cancel</Button>
              {:else}
                <Button variant="outline" size="sm">View Details</Button>
              {/if}
            </div>
          </div>
        {/each}

        {#if leaveRequests.length === 0}
          <div class="text-center py-8">
            <Calendar class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No leave requests</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by creating a new leave request.</p>
            <div class="mt-6">
              <Button>
                <Plus class="mr-2 h-4 w-4" />
                New Leave Request
              </Button>
            </div>
          </div>
        {/if}
      </div>
    </CardContent>
  </Card>
</div>
</EmployeeLayout>
