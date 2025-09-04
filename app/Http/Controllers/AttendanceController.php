<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Get today's attendance for the authenticated employee
     */
    public function getTodayAttendance(): JsonResponse
    {
        try {
            $employee = Auth::guard('employee')->user();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not authenticated'
                ], 401);
            }

            $attendance = Attendance::today()
                ->forEmployee($employee->id)
                ->first();

            if (!$attendance) {
                // Create a new attendance record for today if none exists
                $attendance = Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => today(),
                    'status' => 'not_checked_in'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'attendance' => $attendance,
                    'status' => $attendance->attendance_status,
                    'can_check_in' => $this->canPerformAction($employee, 'checkIn'),
                    'can_check_out' => $this->canPerformAction($employee, 'checkOut'),
                    'time_windows' => [
                        'check_in' => $attendance->getTimeWindowInfo('checkIn'),
                        'check_out' => $attendance->getTimeWindowInfo('checkOut')
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get attendance data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check in the authenticated employee
     */
    public function checkIn(Request $request): JsonResponse
    {
        try {
            $employee = Auth::guard('employee')->user();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not authenticated'
                ], 401);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'accuracy' => 'nullable|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if employee can check in
            $canCheckIn = $this->canPerformAction($employee, 'checkIn');
            if (!$canCheckIn['can']) {
                return response()->json([
                    'success' => false,
                    'message' => $canCheckIn['reason']
                ], 400);
            }

            // Get or create today's attendance
            $attendance = Attendance::today()
                ->forEmployee($employee->id)
                ->first();

            if (!$attendance) {
                $attendance = Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => today(),
                    'status' => 'not_checked_in'
                ]);
            }

            // Check if already checked in
            if ($attendance->is_checked_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Already checked in for today'
                ], 400);
            }

            // Perform check in
            $attendance->checkIn(
                $request->latitude,
                $request->longitude,
                $request->accuracy
            );

            return response()->json([
                'success' => true,
                'message' => 'Check-in successful',
                'data' => [
                    'attendance' => $attendance->fresh(),
                    'status' => $attendance->attendance_status,
                    'check_in_time' => $attendance->clock_in,
                    'location' => [
                        'latitude' => $attendance->latitude,
                        'longitude' => $attendance->longitude,
                        'accuracy' => $attendance->accuracy
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Check-in failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check out the authenticated employee
     */
    public function checkOut(Request $request): JsonResponse
    {
        try {
            $employee = Auth::guard('employee')->user();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not authenticated'
                ], 401);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'accuracy' => 'nullable|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if employee can check out
            $canCheckOut = $this->canPerformAction($employee, 'checkOut');
            if (!$canCheckOut['can']) {
                return response()->json([
                    'success' => false,
                    'message' => $canCheckOut['reason']
                ], 400);
            }

            // Get today's attendance
            $attendance = Attendance::today()
                ->forEmployee($employee->id)
                ->first();

            if (!$attendance || !$attendance->is_checked_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Must check in before checking out'
                ], 400);
            }

            // Check if already checked out
            if ($attendance->is_checked_out) {
                return response()->json([
                    'success' => false,
                    'message' => 'Already checked out for today'
                ], 400);
            }

            // Perform check out
            $attendance->checkOut(
                $request->latitude,
                $request->longitude,
                $request->accuracy
            );

            return response()->json([
                'success' => true,
                'message' => 'Check-out successful',
                'data' => [
                    'attendance' => $attendance->fresh(),
                    'status' => $attendance->attendance_status,
                    'check_out_time' => $attendance->clock_out,
                    'hours_worked' => $attendance->hours,
                    'location' => [
                        'latitude' => $attendance->latitude,
                        'longitude' => $attendance->longitude,
                        'accuracy' => $attendance->accuracy
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Check-out failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance history for the authenticated employee
     */
    public function getAttendanceHistory(Request $request): JsonResponse
    {
        try {
            $employee = Auth::guard('employee')->user();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not authenticated'
                ], 401);
            }

            $query = Attendance::forEmployee($employee->id)
                ->orderBy('date', 'desc');

            // Apply filters
            if ($request->has('month')) {
                $query->whereMonth('date', $request->month);
            }

            if ($request->has('year')) {
                $query->whereYear('date', $request->year);
            }

            if ($request->has('status')) {
                $query->byStatus($request->status);
            }

            $attendances = $query->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $attendances
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get attendance history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if employee can perform the specified action
     */
    private function canPerformAction(Employee $employee, string $action): array
    {
        $now = now();
        $currentTime = $now->format('H:i');

        // Get time windows from config
        $checkInStart = config('attendance.check_in_start', '07:00');
        $checkInEnd = config('attendance.check_in_end', '10:00');
        $checkOutStart = config('attendance.check_out_start', '17:00');
        $checkOutEnd = config('attendance.check_out_end', '19:00');

        // Check time window
        if ($action === 'checkIn') {
            if ($currentTime < $checkInStart || $currentTime > $checkInEnd) {
                return [
                    'can' => false,
                    'reason' => "Check-in is only available between " . date('g:i A', strtotime($checkInStart)) . " and " . date('g:i A', strtotime($checkInEnd))
                ];
            }
        } elseif ($action === 'checkOut') {
            if ($currentTime < $checkOutStart || $currentTime > $checkOutEnd) {
                return [
                    'can' => false,
                    'reason' => "Check-out is only available between " . date('g:i A', strtotime($checkOutStart)) . " and " . date('g:i A', strtotime($checkOutEnd))
                ];
            }
        }

        return ['can' => true, 'reason' => null];
    }

    /**
     * Validate GPS location against office radius
     */
    public function validateLocation(Request $request): JsonResponse
    {
        try {
            $employee = Auth::guard('employee')->user();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not authenticated'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid coordinates',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get office location from employee's assigned location
            $officeLocation = $this->getOfficeLocation($employee);

            if (!$officeLocation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Office location not configured for this employee'
                ], 400);
            }

            $maxRadius = config('attendance.max_radius_meters', 100); // Get from config, default 100 meters

            $distance = $this->calculateDistance(
                $request->latitude,
                $request->longitude,
                $officeLocation['latitude'],
                $officeLocation['longitude']
            );

            $isWithinRadius = $distance <= $maxRadius;

            // Debug logging
            Log::info('Location validation debug', [
                'user_lat' => $request->latitude,
                'user_lng' => $request->longitude,
                'office_lat' => $officeLocation['latitude'],
                'office_lng' => $officeLocation['longitude'],
                'calculated_distance' => $distance,
                'max_radius' => $maxRadius,
                'is_within_radius' => $isWithinRadius,
                'office_name' => $officeLocation['name']
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'is_within_radius' => $isWithinRadius,
                    'distance' => round($distance, 2),
                    'max_radius' => $maxRadius,
                    'office_location' => [
                        'latitude' => $officeLocation['latitude'],
                        'longitude' => $officeLocation['longitude'],
                        'name' => $officeLocation['name'],
                        'address' => $officeLocation['address']
                    ],
                    'user_location' => [
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Location validation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get office location from employee's assigned location
     */
    private function getOfficeLocation(Employee $employee): ?array
    {
        // First try to get from employee's direct location
        if ($employee->location) {
            return [
                'latitude' => (float) $employee->location->latitude,
                'longitude' => (float) $employee->location->longitude,
                'name' => $employee->location->name,
                'address' => $employee->location->address
            ];
        }

        // Fallback to department location
        if ($employee->department && $employee->department->location) {
            return [
                'latitude' => (float) $employee->department->location->latitude,
                'longitude' => (float) $employee->department->location->longitude,
                'name' => $employee->department->location->name,
                'address' => $employee->department->location->address
            ];
        }

        // Final fallback to configured default location
        return [
            'latitude' => config('attendance.default_latitude', -6.389893),
            'longitude' => config('attendance.default_longitude', 106.720359),
            'name' => config('attendance.default_office_name', 'South Jakarta Office'),
            'address' => config('attendance.default_office_address', 'South Jakarta, Indonesia')
        ];
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371e3; // Earth's radius in meters
        $φ1 = $lat1 * M_PI / 180;
        $φ2 = $lat2 * M_PI / 180;
        $Δφ = ($lat2 - $lat1) * M_PI / 180;
        $Δλ = ($lon2 - $lon1) * M_PI / 180;

        $a = sin($Δφ / 2) * sin($Δφ / 2) +
             cos($φ1) * cos($φ2) *
             sin($Δλ / 2) * sin($Δλ / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $R * $c;
    }
}
