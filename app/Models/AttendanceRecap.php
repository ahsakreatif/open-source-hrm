<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AttendanceRecap extends Model
{
    protected $table = 'attendance_recaps';

    protected $fillable = [
        'employee_id',
        'year',
        'month',
        'total_days_present',
        'total_hours_worked',
        'total_days_absent',
        'total_days_leave',
        'total_leave_hours',
        'overtime_hours',
        'late_minutes',
        'early_departure_minutes',
        'working_days_in_month',
        'attendance_rate',
        'status',
        'notes'
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'total_days_present' => 'integer',
        'total_hours_worked' => 'decimal:2',
        'total_days_absent' => 'integer',
        'total_days_leave' => 'integer',
        'total_leave_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'late_minutes' => 'integer',
        'early_departure_minutes' => 'integer',
        'working_days_in_month' => 'integer',
        'attendance_rate' => 'decimal:2',
    ];

    protected $appends = [
        'period_name',
        'period_date'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function payroll()
    {
        return $this->hasOne(Payroll::class, 'attendance_recap_id');
    }

    public function getPeriodNameAttribute()
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->format('F Y');
    }

    public function getPeriodDateAttribute()
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->format('Y-m');
    }

    /**
     * Calculate attendance recap for a specific employee and month
     */
    public static function calculateRecap($employeeId, $year, $month)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Get attendance records for the month
        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // Get leave records for the month
        $leaves = Leave::where('employee_id', $employeeId)
            ->where('status', 'Approved')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->get();

        // Calculate working days in month (excluding weekends)
        $workingDaysInMonth = $startDate->copy()->diffInDaysFiltered(function($date) {
            return !$date->isWeekend();
        }, $endDate->copy()->addDay());

        // Calculate totals
        $totalDaysPresent = $attendances->whereNotNull('clock_in')->count();
        $totalHoursWorked = $attendances->sum('hours') ?? 0;
        $totalDaysLeave = $leaves->sum('duration');
        $totalLeaveHours = $totalDaysLeave * 8; // Assuming 8 hours per day

        // Calculate overtime (assuming 8 hours per day is standard)
        $standardHours = $totalDaysPresent * 8;
        $overtimeHours = max(0, $totalHoursWorked - $standardHours);

        // Calculate late minutes and early departures
        $lateMinutes = 0;
        $earlyDepartureMinutes = 0;

        foreach ($attendances as $attendance) {
            if ($attendance->shift) {
                $shiftStart = Carbon::parse($attendance->shift->start_time);
                $clockIn = Carbon::parse($attendance->clock_in);
                $shiftEnd = Carbon::parse($attendance->shift->end_time);
                $clockOut = Carbon::parse($attendance->clock_out);

                if ($clockIn->gt($shiftStart)) {
                    $lateMinutes += $clockIn->diffInMinutes($shiftStart);
                }

                if ($clockOut && $clockOut->lt($shiftEnd)) {
                    $earlyDepartureMinutes += $shiftEnd->diffInMinutes($clockOut);
                }
            }
        }

        // Calculate attendance rate
        $totalExpectedDays = $workingDaysInMonth - $totalDaysLeave;
        $attendanceRate = $totalExpectedDays > 0 ? ($totalDaysPresent / $totalExpectedDays) * 100 : 0;

        // Calculate absent days
        $totalDaysAbsent = $workingDaysInMonth - $totalDaysPresent - $totalDaysLeave;

        return [
            'employee_id' => $employeeId,
            'year' => $year,
            'month' => $month,
            'total_days_present' => $totalDaysPresent,
            'total_hours_worked' => $totalHoursWorked,
            'total_days_absent' => max(0, $totalDaysAbsent),
            'total_days_leave' => $totalDaysLeave,
            'total_leave_hours' => $totalLeaveHours,
            'overtime_hours' => $overtimeHours,
            'late_minutes' => $lateMinutes,
            'early_departure_minutes' => $earlyDepartureMinutes,
            'working_days_in_month' => $workingDaysInMonth,
            'attendance_rate' => round($attendanceRate, 2),
            'status' => 'completed'
        ];
    }

    /**
     * Generate or update attendance recap for an employee
     */
    public static function generateRecap($employeeId, $year, $month)
    {
        $recapData = self::calculateRecap($employeeId, $year, $month);

        return self::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'year' => $year,
                'month' => $month
            ],
            $recapData
        );
    }

    /**
     * Get attendance recap for payroll calculation
     */
    public static function getForPayroll($employeeId, $year, $month)
    {
        return self::where('employee_id', $employeeId)
            ->where('year', $year)
            ->where('month', $month)
            ->first();
    }
}
