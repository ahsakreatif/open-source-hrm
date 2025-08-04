<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have shifts
        $this->createShifts();

        // Get all active employees
        $employees = Employee::where('is_active', true)->get();

        if ($employees->isEmpty()) {
            $this->command->warn('No active employees found. Please run EmployeeSeeder first.');
            return;
        }

        $this->command->info('Generating attendance data for current month...');

        foreach ($employees as $employee) {
            $this->generateEmployeeAttendance($employee);
        }

        $this->command->info('Attendance data generated successfully!');
    }

    /**
     * Create default shifts if they don't exist
     */
    private function createShifts(): void
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
    private function generateEmployeeAttendance(Employee $employee): void
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $currentDay = Carbon::now();
        $shifts = Shift::all();

        // Generate attendance for each working day of the current month
        $date = $currentMonth->copy();

        while ($date->lte($currentDay) && $date->month === $currentMonth->month) {
            // Skip weekends (optional - you can remove this for 24/7 operations)
            if ($date->isWeekend()) {
                $date->addDay();
                continue;
            }

            // Randomly decide if employee was present (90% attendance rate)
            if (rand(1, 100) <= 90) {
                $shift = $shifts->random();

                // Generate attendance record
                $attendance = $this->createAttendanceRecord($employee, $date, $shift);

                // Add some variety to the data
                $this->addAttendanceVariety($attendance, $shift);
            }

            $date->addDay();
        }

        $this->command->info("Generated attendance for {$employee->full_name}");
    }

    /**
     * Create a basic attendance record
     */
    private function createAttendanceRecord(Employee $employee, Carbon $date, Shift $shift): Attendance
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

        return Attendance::create([
            'employee_id' => $employee->id,
            'date' => $date->format('Y-m-d'),
            'clock_in' => $clockIn->format('H:i:s'),
            'clock_out' => $hasClockOut ? $clockOut->format('H:i:s') : null,
            'shift_id' => $shift->id,
            'remarks' => rand(1, 100) <= 20 ? $this->generateRemarks() : null, // 20% chance of remarks
        ]);
    }

    /**
     * Add variety to attendance records
     */
    private function addAttendanceVariety(Attendance $attendance, Shift $shift): void
    {
        $variationType = rand(1, 100);

        if ($variationType <= 10) {
            // 10% chance of late arrival
            $this->makeLateArrival($attendance, $shift);
        } elseif ($variationType <= 20) {
            // 10% chance of early departure
            $this->makeEarlyDeparture($attendance, $shift);
        } elseif ($variationType <= 30) {
            // 10% chance of overtime
            $this->makeOvertime($attendance, $shift);
        } elseif ($variationType <= 35) {
            // 5% chance of incomplete attendance
            $this->makeIncomplete($attendance);
        }
    }

    /**
     * Make attendance record with late arrival
     */
    private function makeLateArrival(Attendance $attendance, Shift $shift): void
    {
        $shiftStart = Carbon::parse($shift->start_time);
        $lateMinutes = rand(15, 60);
        $clockIn = $shiftStart->copy()->addMinutes($lateMinutes);

        $attendance->update([
            'clock_in' => $clockIn->format('H:i:s'),
            'remarks' => 'Late arrival',
        ]);
    }

    /**
     * Make attendance record with early departure
     */
    private function makeEarlyDeparture(Attendance $attendance, Shift $shift): void
    {
        $shiftEnd = Carbon::parse($shift->end_time);
        $earlyMinutes = rand(15, 60);
        $clockOut = $shiftEnd->copy()->subMinutes($earlyMinutes);

        $attendance->update([
            'clock_out' => $clockOut->format('H:i:s'),
            'remarks' => 'Early departure',
        ]);
    }

    /**
     * Make attendance record with overtime
     */
    private function makeOvertime(Attendance $attendance, Shift $shift): void
    {
        $shiftEnd = Carbon::parse($shift->end_time);
        $overtimeHours = rand(1, 3);
        $clockOut = $shiftEnd->copy()->addHours($overtimeHours);

        $attendance->update([
            'clock_out' => $clockOut->format('H:i:s'),
            'remarks' => 'Overtime worked',
        ]);
    }

    /**
     * Make attendance record incomplete
     */
    private function makeIncomplete(Attendance $attendance): void
    {
        $attendance->update([
            'clock_out' => null,
            'remarks' => 'Incomplete attendance',
        ]);
    }

    /**
     * Generate random remarks
     */
    private function generateRemarks(): string
    {
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

    /**
     * Generate attendance for a specific month
     */
    public function generateForMonth(int $year, int $month): void
    {
        $employees = Employee::where('is_active', true)->get();
        $shifts = Shift::all();

        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        foreach ($employees as $employee) {
            $date = $startDate->copy();

            while ($date->lte($endDate)) {
                // Skip weekends
                if ($date->isWeekend()) {
                    $date->addDay();
                    continue;
                }

                // 90% attendance rate
                if (rand(1, 100) <= 90) {
                    $shift = $shifts->random();
                    $this->createAttendanceRecord($employee, $date, $shift);
                }

                $date->addDay();
            }
        }
    }

    /**
     * Generate attendance for specific employee
     */
    public function generateForEmployee(Employee $employee, int $year, int $month): void
    {
        $shifts = Shift::all();
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $date = $startDate->copy();

        while ($date->lte($endDate)) {
            if ($date->isWeekend()) {
                $date->addDay();
                continue;
            }

            if (rand(1, 100) <= 90) {
                $shift = $shifts->random();
                $this->createAttendanceRecord($employee, $date, $shift);
            }

            $date->addDay();
        }
    }
}
