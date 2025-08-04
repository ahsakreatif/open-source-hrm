<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;

class GenerateAttendanceData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:generate-data
                            {--employee= : Generate data for specific employee ID}
                            {--year= : Year for data generation (default: current year)}
                            {--month= : Month for data generation (default: current month)}
                            {--count= : Number of attendance records to generate (default: 50)}
                            {--clear : Clear existing attendance data first}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sample attendance data for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->option('year') ?: Carbon::now()->year;
        $month = $this->option('month') ?: Carbon::now()->month;
        $employeeId = $this->option('employee');
        $count = $this->option('count') ?: 50;
        $clear = $this->option('clear');

        $this->info("Generating attendance data for {$year}-{$month}");

        if ($clear) {
            $this->warn('Clearing existing attendance data...');
            Attendance::truncate();
            $this->info('Existing attendance data cleared.');
        }

        // Ensure we have shifts
        $this->ensureShiftsExist();

        if ($employeeId) {
            // Generate for specific employee
            $employee = Employee::find($employeeId);
            if (!$employee) {
                $this->error("Employee with ID {$employeeId} not found.");
                return;
            }
            $this->generateForEmployee($employee, $year, $month, $count);
        } else {
            // Generate for all employees
            $employees = Employee::where('is_active', true)->get();
            if ($employees->isEmpty()) {
                $this->error('No active employees found. Please run EmployeeSeeder first.');
                return;
            }

            foreach ($employees as $employee) {
                $this->generateForEmployee($employee, $year, $month, $count);
            }
        }

        $this->info('Attendance data generation completed!');
    }

    /**
     * Ensure shifts exist
     */
    private function ensureShiftsExist(): void
    {
        $shifts = [
            [
                'name' => 'Day Shift',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
            ],
            [
                'name' => 'Night Shift',
                'start_time' => '20:00:00',
                'end_time' => '05:00:00',
            ],
            [
                'name' => 'Morning Shift',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
            ],
            [
                'name' => 'Evening Shift',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
            ],
        ];

        foreach ($shifts as $shiftData) {
            Shift::firstOrCreate(
                ['name' => $shiftData['name']],
                $shiftData
            );
        }
    }

    /**
     * Generate attendance data for a specific employee
     */
    private function generateForEmployee(Employee $employee, int $year, int $month, int $count): void
    {
        $shifts = Shift::all();
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $this->info("Generating {$count} attendance records for {$employee->full_name}");

        $recordsCreated = 0;
        $attempts = 0;
        $maxAttempts = $count * 2; // Prevent infinite loop

                while ($recordsCreated < $count && $attempts < $maxAttempts) {
            $date = Carbon::parse(\Faker\Factory::create()->dateTimeBetween($startDate, $endDate));

            // Skip weekends (optional)
            if ($date->format('N') >= 6) { // Saturday = 6, Sunday = 7
                $attempts++;
                continue;
            }

            // Check if attendance already exists for this employee and date
            $existingAttendance = Attendance::where('employee_id', $employee->id)
                ->where('date', $date->format('Y-m-d'))
                ->first();

            if ($existingAttendance) {
                $attempts++;
                continue;
            }

            $shift = $shifts->random();
            $this->createAttendanceRecord($employee, $date, $shift);
            $recordsCreated++;
            $attempts++;
        }

        $this->info("Created {$recordsCreated} attendance records for {$employee->full_name}");
    }

    /**
     * Create a single attendance record
     */
    private function createAttendanceRecord(Employee $employee, Carbon $date, Shift $shift): void
    {
        $shiftStart = Carbon::parse($shift->start_time);
        $shiftEnd = Carbon::parse($shift->end_time);

        // Generate realistic clock in time
        $clockInVariation = rand(-30, 15); // 30 minutes early to 15 minutes late
        $clockIn = $shiftStart->copy()->addMinutes($clockInVariation);

        // Generate realistic clock out time
        $clockOutVariation = rand(-15, 60); // 15 minutes early to 1 hour late
        $clockOut = $shiftEnd->copy()->addMinutes($clockOutVariation);

        // Sometimes generate incomplete attendance (5% chance)
        $hasClockOut = rand(1, 100) > 5;

        // Add variety to the data
        $variationType = rand(1, 100);

        if ($variationType <= 10) {
            // Late arrival
            $lateMinutes = rand(15, 60);
            $clockIn = $shiftStart->copy()->addMinutes($lateMinutes);
        } elseif ($variationType <= 20) {
            // Early departure
            $earlyMinutes = rand(15, 60);
            $clockOut = $shiftEnd->copy()->subMinutes($earlyMinutes);
        } elseif ($variationType <= 30) {
            // Overtime
            $overtimeHours = rand(1, 3);
            $clockOut = $shiftEnd->copy()->addHours($overtimeHours);
        } elseif ($variationType <= 35) {
            // Incomplete
            $hasClockOut = false;
        }

        Attendance::create([
            'employee_id' => $employee->id,
            'date' => $date->format('Y-m-d'),
            'clock_in' => $clockIn->format('H:i:s'),
            'clock_out' => $hasClockOut ? $clockOut->format('H:i:s') : null,
            'shift_id' => $shift->id,
            'remarks' => $this->generateRemarks($variationType),
        ]);
    }

    /**
     * Generate remarks based on variation type
     */
    private function generateRemarks(int $variationType): ?string
    {
        if ($variationType <= 10) {
            return 'Late arrival';
        } elseif ($variationType <= 20) {
            return 'Early departure';
        } elseif ($variationType <= 30) {
            return 'Overtime worked';
        } elseif ($variationType <= 35) {
            return 'Incomplete attendance';
        } elseif (rand(1, 100) <= 20) {
            $remarks = [
                'Regular attendance',
                'On time',
                'Good attendance',
                'Standard shift',
                'Normal working hours',
                'Regular day',
                'Standard attendance',
                'Normal shift',
                'Regular work day',
                'Standard attendance record',
            ];
            return $remarks[array_rand($remarks)];
        }

        return null;
    }
}
