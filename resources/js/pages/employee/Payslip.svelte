<script lang="ts">
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/card';
  import { Button } from '../../components/ui/button';
  import { Select, SelectContent, SelectItem, SelectTrigger } from '../../components/ui/select';
  import { Breadcrumb, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator, Item, BreadcrumbLink } from '../../components/ui/breadcrumb';
  import { Link } from '@inertiajs/svelte';
  import { Download, FileText, Calendar, DollarSign, TrendingUp, TrendingDown } from 'lucide-svelte';
  import EmployeeLayout from '../../layouts/employee/EmployeeLayout.svelte';

  export let user: any;

  const breadcrumbItems = [
    { title: 'Payslips', href: undefined }
  ];

  // Mock data - replace with actual data from backend
  const payslips = [
    {
      id: 1,
      month: 'January 2024',
      period: 'Jan 1 - Jan 31, 2024',
      gross_pay: 5000.00,
      net_pay: 3750.00,
      deductions: 1250.00,
      status: 'paid',
      payment_date: '2024-02-01',
      pdf_url: '#'
    },
    {
      id: 2,
      month: 'December 2023',
      period: 'Dec 1 - Dec 31, 2023',
      gross_pay: 5000.00,
      net_pay: 3750.00,
      deductions: 1250.00,
      status: 'paid',
      payment_date: '2024-01-01',
      pdf_url: '#'
    },
    {
      id: 3,
      month: 'November 2023',
      period: 'Nov 1 - Nov 30, 2023',
      gross_pay: 5000.00,
      net_pay: 3750.00,
      deductions: 1250.00,
      status: 'paid',
      payment_date: '2023-12-01',
      pdf_url: '#'
    }
  ];

  const currentYear = new Date().getFullYear();
  const years = Array.from({ length: 5 }, (_, i) => (currentYear - i).toString());
  let selectedYear: string = currentYear.toString();

  function formatCurrency(amount: number) {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(amount);
  }

  function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  }

  function downloadPayslip(payslip: any) {
    // Implement actual download logic
    console.log('Downloading payslip:', payslip.month);
  }
</script>

<svelte:head>
  <title>Payslips - Employee Portal</title>
</svelte:head>

<EmployeeLayout {user} currentPage="/employee/payslip">
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
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 md:text-3xl">Payslips</h1>
      <p class="text-gray-600 mt-2">View and download your monthly payslips</p>
    </div>

  <!-- Year Selector -->
  <div class="mb-6">
    <Card>
      <CardContent class="p-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Select Year</h3>
            <p class="text-sm text-gray-600">Choose a year to view payslips</p>
          </div>
          <div class="relative w-full sm:w-48">
            <select
              bind:value={selectedYear}
              class="w-full h-9 rounded-md border border-input bg-transparent px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
            >
              {#each years as year}
                <option value={year}>{year}</option>
              {/each}
            </select>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>

  <!-- Summary Cards -->
  <div class="grid gap-4 mb-6 md:grid-cols-3">
    <Card>
      <CardContent class="p-4">
        <div class="flex items-center space-x-2">
          <DollarSign class="h-5 w-5 text-green-600" />
          <div>
            <p class="text-sm font-medium text-gray-600">Total Gross Pay</p>
            <p class="text-2xl font-bold text-gray-900">{formatCurrency(payslips.reduce((sum, p) => sum + p.gross_pay, 0))}</p>
            <p class="text-xs text-gray-500">Year {selectedYear}</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardContent class="p-4">
        <div class="flex items-center space-x-2">
          <TrendingUp class="h-5 w-5 text-blue-600" />
          <div>
            <p class="text-sm font-medium text-gray-600">Total Net Pay</p>
            <p class="text-2xl font-bold text-gray-900">{formatCurrency(payslips.reduce((sum, p) => sum + p.net_pay, 0))}</p>
            <p class="text-xs text-gray-500">Year {selectedYear}</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardContent class="p-4">
        <div class="flex items-center space-x-2">
          <TrendingDown class="h-5 w-5 text-orange-600" />
          <div>
            <p class="text-sm font-medium text-gray-600">Total Deductions</p>
            <p class="text-2xl font-bold text-gray-900">{formatCurrency(payslips.reduce((sum, p) => sum + p.deductions, 0))}</p>
            <p class="text-xs text-gray-500">Year {selectedYear}</p>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>

  <!-- Payslips List -->
  <Card>
    <CardHeader>
      <CardTitle>Payslips for {selectedYear}</CardTitle>
      <CardDescription>Click on a payslip to view details or download</CardDescription>
    </CardHeader>
    <CardContent>
      <div class="space-y-4">
        {#each payslips as payslip}
          <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border rounded-lg hover:bg-gray-50 transition-colors">
            <div class="flex-1 space-y-2">
              <div class="flex items-center gap-2">
                <FileText class="h-5 w-5 text-blue-600" />
                <h3 class="font-semibold text-gray-900 text-lg">{payslip.month}</h3>
                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                  {payslip.status}
                </span>
              </div>

              <div class="flex flex-col sm:flex-row sm:items-center gap-4 text-sm text-gray-600">
                <div class="flex items-center gap-1">
                  <Calendar class="h-4 w-4" />
                  <span>{payslip.period}</span>
                </div>
                <span class="text-gray-400 hidden sm:inline">•</span>
                <span>Paid on {formatDate(payslip.payment_date)}</span>
              </div>

              <div class="grid grid-cols-3 gap-4 text-sm">
                <div>
                  <span class="text-gray-500">Gross Pay:</span>
                  <span class="ml-2 font-medium text-gray-900">{formatCurrency(payslip.gross_pay)}</span>
                </div>
                <div>
                  <span class="text-gray-500">Net Pay:</span>
                  <span class="ml-2 font-medium text-green-600">{formatCurrency(payslip.net_pay)}</span>
                </div>
                <div>
                  <span class="text-gray-500">Deductions:</span>
                  <span class="ml-2 font-medium text-orange-600">{formatCurrency(payslip.deductions)}</span>
                </div>
              </div>
            </div>

            <div class="flex gap-2 mt-4 sm:mt-0">
              <Button variant="outline" size="sm" onclick={() => downloadPayslip(payslip)}>
                <Download class="mr-2 h-4 w-4" />
                Download
              </Button>
              <Button variant="outline" size="sm">View Details</Button>
            </div>
          </div>
        {/each}

        {#if payslips.length === 0}
          <div class="text-center py-12">
            <FileText class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No payslips found</h3>
            <p class="mt-1 text-sm text-gray-500">No payslips available for the selected year.</p>
          </div>
        {/if}
      </div>
    </CardContent>
  </Card>

  <!-- Tax Information -->
  <div class="mt-8">
    <Card>
      <CardHeader>
        <CardTitle>Tax Information</CardTitle>
        <CardDescription>Important information about your tax deductions and benefits</CardDescription>
      </CardHeader>
      <CardContent class="space-y-4">
        <div class="grid gap-4 md:grid-cols-2">
          <div class="p-4 bg-blue-50 rounded-lg">
            <h4 class="font-medium text-blue-900 mb-2">Tax Deductions</h4>
            <p class="text-sm text-blue-700">
              Your payslip includes standard tax deductions for federal and state taxes,
              social security, and Medicare contributions.
            </p>
          </div>
          <div class="p-4 bg-green-50 rounded-lg">
            <h4 class="font-medium text-green-900 mb-2">Benefits & Allowances</h4>
            <p class="text-sm text-green-700">
              Additional benefits may include health insurance, retirement contributions,
              and other company-provided benefits.
            </p>
          </div>
        </div>

        <div class="p-4 bg-gray-50 rounded-lg">
          <h4 class="font-medium text-gray-900 mb-2">Need Help?</h4>
          <p class="text-sm text-gray-700 mb-3">
            If you have questions about your payslip or need assistance, please contact the HR department.
          </p>
          <Button variant="outline" size="sm">Contact HR</Button>
        </div>
      </CardContent>
    </Card>
  </div>
</div>
</EmployeeLayout>
