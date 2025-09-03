<script lang="ts">
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
  import { Badge } from '../../components/ui/badge';
  import { Button } from '../../components/ui/button';
  import { Breadcrumb, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator, Item, BreadcrumbLink } from '../../components/ui/breadcrumb';
  import { Link } from '@inertiajs/svelte';
  import { Bell, Calendar, User, AlertTriangle, Info, CheckCircle } from 'lucide-svelte';
  import EmployeeLayout from '../../layouts/employee/EmployeeLayout.svelte';

  export let user: any;

  const breadcrumbItems = [
    { title: 'Announcements', href: undefined }
  ];

  // Mock data - replace with actual data from backend
  const announcements = [
    {
      id: 1,
      title: 'Company Holiday Schedule 2024',
      content: 'Please note that the office will be closed on the following dates: January 1st (New Year), December 25th (Christmas), and other national holidays. Plan your work accordingly.',
      type: 'info',
      priority: 'high',
      author: 'HR Department',
      published_at: '2024-01-05T10:00:00Z',
      is_read: false
    },
    {
      id: 2,
      title: 'New Employee Benefits Package',
      content: 'We are excited to announce an enhanced benefits package including improved health insurance, additional vacation days, and flexible work arrangements. Details will be shared in the upcoming town hall meeting.',
      type: 'info',
      priority: 'medium',
      author: 'Management Team',
      published_at: '2024-01-03T14:30:00Z',
      is_read: true
    },
    {
      id: 3,
      title: 'Office Renovation Notice',
      content: 'Starting next week, the 3rd floor will undergo renovation. Please expect some noise and temporary relocation of some departments. We apologize for any inconvenience.',
      type: 'warning',
      priority: 'medium',
      author: 'Facilities Team',
      published_at: '2024-01-02T09:15:00Z',
      is_read: true
    },
    {
      id: 4,
      title: 'Monthly Team Meeting',
      content: 'Our monthly team meeting will be held this Friday at 2 PM in the conference room. All employees are required to attend. Please prepare your updates and questions.',
      type: 'info',
      priority: 'low',
      author: 'Team Lead',
      published_at: '2024-01-01T16:45:00Z',
      is_read: false
    }
  ];

  function getTypeConfig(type: string) {
    const configs: Record<string, { icon: any; color: string; bgColor: string }> = {
      info: { icon: Info, color: 'text-blue-600', bgColor: 'bg-blue-50' },
      warning: { icon: AlertTriangle, color: 'text-orange-600', bgColor: 'bg-orange-50' },
      success: { icon: CheckCircle, color: 'text-green-600', bgColor: 'bg-green-50' }
    };
    return configs[type] || configs.info;
  }

  function getPriorityConfig(priority: string) {
    const configs: Record<string, { variant: 'destructive' | 'secondary' | 'outline'; text: string }> = {
      high: { variant: 'destructive', text: 'High' },
      medium: { variant: 'secondary', text: 'Medium' },
      low: { variant: 'outline', text: 'Low' }
    };
    return configs[priority] || configs.medium;
  }

  function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  function getTimeAgo(dateString: string) {
    const now = new Date();
    const date = new Date(dateString);
    const diffInHours = Math.floor((now.getTime() - date.getTime()) / (1000 * 60 * 60));

    if (diffInHours < 1) return 'Just now';
    if (diffInHours < 24) return `${diffInHours} hour${diffInHours !== 1 ? 's' : ''} ago`;

    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 7) return `${diffInDays} day${diffInDays !== 1 ? 's' : ''} ago`;

    return formatDate(dateString);
  }
</script>

<svelte:head>
  <title>Announcements - Employee Portal</title>
</svelte:head>

<EmployeeLayout {user} currentPage="/employee/announcements">
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
      <h1 class="text-2xl font-bold text-gray-900 md:text-3xl">Announcements</h1>
      <p class="text-gray-600 mt-2">Stay updated with company news and important information</p>
    </div>

  <!-- Unread Count -->
  {#if announcements.filter(a => !a.is_read).length > 0}
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
      <div class="flex items-center gap-2">
        <Bell class="h-5 w-5 text-blue-600" />
        <span class="text-sm font-medium text-blue-900">
          You have {announcements.filter(a => !a.is_read).length} unread announcement{announcements.filter(a => !a.is_read).length !== 1 ? 's' : ''}
        </span>
      </div>
    </div>
  {/if}

  <!-- Announcements List -->
  <div class="space-y-4">
    {#each announcements as announcement}
      {@const typeConfig = getTypeConfig(announcement.type)}
      {@const priorityConfig = getPriorityConfig(announcement.priority)}
      {@const TypeIcon = typeConfig.icon}

      <Card class={`transition-all hover:shadow-md ${!announcement.is_read ? 'border-l-4 border-l-blue-500 bg-blue-50/50' : ''}`}>
        <CardHeader class="pb-3">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <div class={`p-2 rounded-full ${typeConfig.bgColor}`}>
                  <TypeIcon class={`h-4 w-4 ${typeConfig.color}`} />
                </div>
                <h3 class="font-semibold text-gray-900 text-lg">{announcement.title}</h3>
                {#if !announcement.is_read}
                  <Badge variant="default" class="text-xs">New</Badge>
                {/if}
              </div>

              <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                <div class="flex items-center gap-1">
                  <User class="h-4 w-4" />
                  <span>{announcement.author}</span>
                </div>
                <div class="flex items-center gap-1">
                  <Calendar class="h-4 w-4" />
                  <span>{getTimeAgo(announcement.published_at)}</span>
                </div>
                <Badge variant={priorityConfig.variant} class="text-xs">
                  {priorityConfig.text} Priority
                </Badge>
              </div>
            </div>
          </div>
        </CardHeader>

        <CardContent class="pt-0">
          <p class="text-gray-700 leading-relaxed mb-4">{announcement.content}</p>

          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-sm text-gray-500">
              <span>Published: {formatDate(announcement.published_at)}</span>
            </div>

            <div class="flex gap-2">
              {#if !announcement.is_read}
                <Button variant="outline" size="sm">Mark as Read</Button>
              {/if}
              <Button variant="outline" size="sm">View Details</Button>
            </div>
          </div>
        </CardContent>
      </Card>
    {/each}

    {#if announcements.length === 0}
      <div class="text-center py-12">
        <Bell class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">No announcements</h3>
        <p class="mt-1 text-sm text-gray-500">Check back later for updates and news.</p>
      </div>
    {/if}
  </div>

  <!-- Load More Button -->
  {#if announcements.length > 0}
    <div class="mt-8 text-center">
      <Button variant="outline" class="w-full sm:w-auto">
        Load More Announcements
      </Button>
    </div>
  {/if}
</div>
</EmployeeLayout>
