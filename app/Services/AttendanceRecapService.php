<?php

namespace App\Services;

use App\Models\AttendanceRecap;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Employee;
use Carbon\Carbon;

class AttendanceRecapService
{
    /**
     * Generate attendance recap for all employees for a specific month
     */
    public static function generateMonthlyRecap($year, $month)
    {
        $employees = Employee::where('is_active', true)->get();
        $results = [];

        foreach ($employees as $employee) {
            try {
                $recap = AttendanceRecap::generateRecap($employee->id, $year, $month);
                $results[] = [
                    'employee' => $employee->full_name,
                    'status' => 'success',
                    'recap' => $recap
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'employee' => $employee->full_name,
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Generate attendance recap for a specific employee
     */
    public static function generateEmployeeRecap($employeeId, $year, $month)
    {
        return AttendanceRecap::generateRecap($employeeId, $year, $month);
    }

    /**
     * Get attendance summary for payroll calculation
     */
    public static function getPayrollSummary($employeeId, $year, $month)
    {
        $recap = AttendanceRecap::getForPayroll($employeeId, $year, $month);

        if (!$recap) {
            // Generate recap if it doesn't exist
            $recap = self::generateEmployeeRecap($employeeId, $year, $month);
        }

        return [
            'total_days_present' => $recap->total_days_present,
            'total_hours_worked' => $recap->total_hours_worked,
            'overtime_hours' => $recap->overtime_hours,
            'total_days_leave' => $recap->total_days_leave,
            'total_leave_hours' => $recap->total_leave_hours,
            'attendance_rate' => $recap->attendance_rate,
            'late_minutes' => $recap->late_minutes,
            'early_departure_minutes' => $recap->early_departure_minutes,
            'working_days_in_month' => $recap->working_days_in_month
        ];
    }

    /**
     * Calculate working hours for payroll
     */
    public static function calculateWorkingHours($employeeId, $year, $month)
    {
        $recap = AttendanceRecap::getForPayroll($employeeId, $year, $month);

        if (!$recap) {
            $recap = self::generateEmployeeRecap($employeeId, $year, $month);
        }

        // Standard working hours (8 hours per day)
        $standardHours = $recap->total_days_present * 8;

        // Overtime hours
        $overtimeHours = $recap->overtime_hours;

        // Leave hours (paid leave)
        $leaveHours = $recap->total_leave_hours;

        return [
            'standard_hours' => $standardHours,
            'overtime_hours' => $overtimeHours,
            'leave_hours' => $leaveHours,
            'total_payable_hours' => $standardHours + $overtimeHours + $leaveHours,
            'attendance_rate' => $recap->attendance_rate
        ];
    }

    /**
     * Get attendance statistics for reporting
     */
    public static function getAttendanceStatistics($year, $month)
    {
        $recaps = AttendanceRecap::where('year', $year)
            ->where('month', $month)
            ->with('employee')
            ->get();

        $stats = [
            'total_employees' => $recaps->count(),
            'average_attendance_rate' => $recaps->avg('attendance_rate'),
            'total_hours_worked' => $recaps->sum('total_hours_worked'),
            'total_overtime_hours' => $recaps->sum('overtime_hours'),
            'total_leave_days' => $recaps->sum('total_days_leave'),
            'employees_with_perfect_attendance' => $recaps->where('attendance_rate', 100)->count(),
            'employees_with_late_arrivals' => $recaps->where('late_minutes', '>', 0)->count(),
            'employees_with_early_departures' => $recaps->where('early_departure_minutes', '>', 0)->count()
        ];

        return $stats;
    }

    /**
     * Recalculate all attendance recaps for a month
     */
    public static function recalculateMonthlyRecaps($year, $month)
    {
        $recaps = AttendanceRecap::where('year', $year)
            ->where('month', $month)
            ->get();

        foreach ($recaps as $recap) {
            $newData = AttendanceRecap::calculateRecap($recap->employee_id, $year, $month);
            $recap->update($newData);
        }

        return $recaps->count();
    }
}
