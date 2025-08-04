<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AttendanceRecapService;
use Carbon\Carbon;

class GenerateAttendanceRecap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:generate-recap
                            {--employee= : Generate recap for specific employee ID}
                            {--year= : Year for recap (default: current year)}
                            {--month= : Month for recap (default: current month)}
                            {--all : Generate recaps for all employees}
                            {--recalculate : Recalculate existing recaps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate attendance recap for payroll processing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->option('year') ?: Carbon::now()->year;
        $month = $this->option('month') ?: Carbon::now()->month;
        $employeeId = $this->option('employee');
        $recalculate = $this->option('recalculate');

        $this->info("Generating attendance recap for {$year}-{$month}");

        if ($recalculate) {
            $this->info('Recalculating existing recaps...');
            $count = AttendanceRecapService::recalculateMonthlyRecaps($year, $month);
            $this->info("Recalculated {$count} attendance recaps.");
            return;
        }

        if ($employeeId) {
            // Generate recap for specific employee
            $this->info("Generating recap for employee ID: {$employeeId}");
            try {
                $recap = AttendanceRecapService::generateEmployeeRecap($employeeId, $year, $month);
                $this->info("Successfully generated recap for employee {$recap->employee->full_name}");
                $this->table(
                    ['Metric', 'Value'],
                    [
                        ['Days Present', $recap->total_days_present],
                        ['Hours Worked', $recap->total_hours_worked],
                        ['Overtime Hours', $recap->overtime_hours],
                        ['Leave Days', $recap->total_days_leave],
                        ['Attendance Rate', $recap->attendance_rate . '%'],
                        ['Late Minutes', $recap->late_minutes],
                        ['Early Departure Minutes', $recap->early_departure_minutes],
                    ]
                );
            } catch (\Exception $e) {
                $this->error("Error generating recap: " . $e->getMessage());
            }
        } else {
            // Generate recaps for all employees
            $this->info('Generating recaps for all active employees...');
            $results = AttendanceRecapService::generateMonthlyRecap($year, $month);

            $successCount = collect($results)->where('status', 'success')->count();
            $errorCount = collect($results)->where('status', 'error')->count();

            $this->info("Generated {$successCount} successful recaps");
            if ($errorCount > 0) {
                $this->warn("{$errorCount} recaps failed to generate");

                foreach ($results as $result) {
                    if ($result['status'] === 'error') {
                        $this->error("  - {$result['employee']}: {$result['message']}");
                    }
                }
            }
        }

        $this->info('Attendance recap generation completed!');
    }
}
