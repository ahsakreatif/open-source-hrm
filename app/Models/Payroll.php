<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\AttendanceRecapService;

class Payroll extends Model
{
    protected $table = 'payrolls';
    protected $fillable = [
        'employee_id',
        'attendance_recap_id',
        'pay_date',
        'period',
        'gross_pay',
        'net_pay',
        'deductions',
        'allowances',
        'bonuses',
        'notes',
        'status'
    ];
    protected $casts = [
        'deductions' => 'array',
        'allowances' => 'array',
        'bonuses' => 'array',
    ];

    protected $with = [
        'employee',
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendanceRecap()
    {
        return $this->belongsTo(AttendanceRecap::class, 'attendance_recap_id');
    }

    /**
     * Calculate payroll using attendance recap
     */
    public static function calculateFromAttendanceRecap($employeeId, $year, $month, $hourlyRate = null)
    {
        // Get or generate attendance recap
        $recap = AttendanceRecap::getForPayroll($employeeId, $year, $month);
        if (!$recap) {
            $recap = AttendanceRecap::generateRecap($employeeId, $year, $month);
        }

        // Get working hours summary
        $workingHours = AttendanceRecapService::calculateWorkingHours($employeeId, $year, $month);

        // Calculate gross pay based on hours worked
        $standardPay = $workingHours['standard_hours'] * ($hourlyRate ?? 1000); // Default hourly rate
        $overtimePay = $workingHours['overtime_hours'] * ($hourlyRate ?? 1000) * 1.5; // 1.5x for overtime
        $leavePay = $workingHours['leave_hours'] * ($hourlyRate ?? 1000); // Paid leave

        $grossPay = $standardPay + $overtimePay + $leavePay;

        // Create or update payroll record
        $payroll = self::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'period' => "{$year}-{$month}",
            ],
            [
                'attendance_recap_id' => $recap->id,
                'pay_date' => now(),
                'gross_pay' => $grossPay,
                'net_pay' => $grossPay, // Simplified - no deductions for now
                'status' => 'calculated'
            ]
        );

        return $payroll;
    }

    /**
     * Get payroll summary with attendance details
     */
    public function getPayrollSummary()
    {
        if (!$this->attendanceRecap) {
            return null;
        }

        return [
            'employee' => $this->employee->full_name,
            'period' => $this->period,
            'gross_pay' => $this->gross_pay,
            'net_pay' => $this->net_pay,
            'attendance' => [
                'days_present' => $this->attendanceRecap->total_days_present,
                'hours_worked' => $this->attendanceRecap->total_hours_worked,
                'overtime_hours' => $this->attendanceRecap->overtime_hours,
                'leave_days' => $this->attendanceRecap->total_days_leave,
                'attendance_rate' => $this->attendanceRecap->attendance_rate,
                'late_minutes' => $this->attendanceRecap->late_minutes,
                'early_departure_minutes' => $this->attendanceRecap->early_departure_minutes,
            ]
        ];
    }
}
