<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get a random employee
        $employee = Employee::inRandomOrder()->first();

        // Get a random shift or create a default one
        $shift = Shift::inRandomOrder()->first();
        if (!$shift) {
            $shift = Shift::create([
                'name' => 'Day Shift',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
            ]);
        }

        // Generate a date for current month
        $date = $this->faker->dateTimeBetween(
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );

        // Parse shift times
        $shiftStart = Carbon::parse($shift->start_time);
        $shiftEnd = Carbon::parse($shift->end_time);

        // Generate realistic clock in time (slightly before or after shift start)
        $clockInVariation = $this->faker->randomElement([
            -30, -15, -10, -5, 0, 5, 10, 15, 30, 45, 60 // minutes variation
        ]);
        $clockIn = $shiftStart->copy()->addMinutes($clockInVariation);

        // Generate realistic clock out time (slightly before or after shift end)
        $clockOutVariation = $this->faker->randomElement([
            -30, -15, -10, -5, 0, 5, 10, 15, 30, 45, 60 // minutes variation
        ]);
        $clockOut = $shiftEnd->copy()->addMinutes($clockOutVariation);

        // Sometimes generate incomplete attendance (no clock out)
        $hasClockOut = $this->faker->boolean(85); // 85% chance of having clock out

        return [
            'employee_id' => $employee ? $employee->id : Employee::factory(),
            'date' => $date->format('Y-m-d'),
            'clock_in' => $clockIn->format('H:i:s'),
            'clock_out' => $hasClockOut ? $clockOut->format('H:i:s') : null,
            'shift_id' => $shift->id,
            'remarks' => $this->faker->optional(0.3)->sentence(), // 30% chance of having remarks
        ];
    }

    /**
     * Generate attendance for a specific employee
     */
    public function forEmployee(Employee $employee): static
    {
        return $this->state(fn (array $attributes) => [
            'employee_id' => $employee->id,
        ]);
    }

    /**
     * Generate attendance for a specific date range
     */
    public function forDateRange($startDate, $endDate): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $this->faker->dateTimeBetween($startDate, $endDate)->format('Y-m-d'),
        ]);
    }

    /**
     * Generate attendance for current month
     */
    public function currentMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $this->faker->dateTimeBetween(
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            )->format('Y-m-d'),
        ]);
    }

    /**
     * Generate attendance with late arrival
     */
    public function lateArrival(): static
    {
        return $this->state(function (array $attributes) {
            $shift = Shift::find($attributes['shift_id']);
            $shiftStart = Carbon::parse($shift->start_time);

            // Generate late arrival (15-60 minutes late)
            $lateMinutes = $this->faker->numberBetween(15, 60);
            $clockIn = $shiftStart->copy()->addMinutes($lateMinutes);

            return [
                'clock_in' => $clockIn->format('H:i:s'),
            ];
        });
    }

    /**
     * Generate attendance with early departure
     */
    public function earlyDeparture(): static
    {
        return $this->state(function (array $attributes) {
            $shift = Shift::find($attributes['shift_id']);
            $shiftEnd = Carbon::parse($shift->end_time);

            // Generate early departure (15-60 minutes early)
            $earlyMinutes = $this->faker->numberBetween(15, 60);
            $clockOut = $shiftEnd->copy()->subMinutes($earlyMinutes);

            return [
                'clock_out' => $clockOut->format('H:i:s'),
            ];
        });
    }

    /**
     * Generate attendance with overtime
     */
    public function overtime(): static
    {
        return $this->state(function (array $attributes) {
            $shift = Shift::find($attributes['shift_id']);
            $shiftEnd = Carbon::parse($shift->end_time);

            // Generate overtime (1-4 hours extra)
            $overtimeHours = $this->faker->numberBetween(1, 4);
            $clockOut = $shiftEnd->copy()->addHours($overtimeHours);

            return [
                'clock_out' => $clockOut->format('H:i:s'),
            ];
        });
    }

    /**
     * Generate attendance for weekdays only
     */
    public function weekdaysOnly(): static
    {
        return $this->state(function (array $attributes) {
            $date = Carbon::parse($attributes['date']);

            // Keep generating until we get a weekday
            while ($date->isWeekend()) {
                $date = $this->faker->dateTimeBetween(
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth()
                );
            }

            return [
                'date' => $date->format('Y-m-d'),
            ];
        });
    }

    /**
     * Generate attendance with no clock out (incomplete)
     */
    public function incomplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'clock_out' => null,
        ]);
    }
}
