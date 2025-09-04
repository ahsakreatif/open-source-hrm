<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Shift;

class Attendance extends Model
{
    protected $table = 'attendances';

    protected $fillable = [
        'employee_id',
        'date',
        'clock_in',
        'clock_out',
        'shift_id',
        'remarks',
        // New GPS and tracking fields
        'latitude',
        'longitude',
        'accuracy',
        'action_type',
        'status',
        'location_notes',
        'check_in_at',
        'check_out_at',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime:H:i',
        'clock_out' => 'datetime:H:i',
        'shift_id' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'integer',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
    ];

    protected $appends = [
        'shift_name',
        'hours',
        'is_checked_in',
        'is_checked_out',
        'attendance_status'
    ];

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    // Accessors
    public function getShiftNameAttribute(): ?string
    {
        return $this->shift ? $this->shift->name : null;
    }

    public function getHoursAttribute(): ?float
    {
        if ($this->clock_in && $this->clock_out) {
            $start = Carbon::parse($this->clock_in);
            $end = Carbon::parse($this->clock_out);
            return $start->diffInHours($end, false) + ($start->diffInMinutes($end, false) % 60) / 60;
        }
        return null;
    }

    public function getIsCheckedInAttribute(): bool
    {
        return !is_null($this->check_in_at);
    }

    public function getIsCheckedOutAttribute(): bool
    {
        return !is_null($this->check_out_at);
    }

    public function getAttendanceStatusAttribute(): string
    {
        if ($this->is_checked_out) {
            return 'checked_out';
        } elseif ($this->is_checked_in) {
            return 'checked_in';
        }
        return 'not_checked_in';
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->where('date', today());
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Methods
    public function checkIn($latitude = null, $longitude = null, $accuracy = null): bool
    {
        $now = now();

        $this->update([
            'check_in_at' => $now,
            'clock_in' => $now->format('H:i:s'),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy' => $accuracy,
            'action_type' => $latitude ? 'gps' : 'manual',
            'status' => $this->determineStatus($now),
            'date' => $now->toDateString(),
        ]);

        return true;
    }

    public function checkOut($latitude = null, $longitude = null, $accuracy = null): bool
    {
        $now = now();

        $this->update([
            'check_out_at' => $now,
            'clock_out' => $now->format('H:i:s'),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy' => $accuracy,
            'action_type' => $latitude ? 'gps' : 'manual',
        ]);

        return true;
    }

    private function determineStatus($checkInTime): string
    {
        $checkInHour = $checkInTime->hour;
        $checkInMinute = $checkInTime->minute;

        // Check if late (after 9:00 AM)
        if ($checkInHour > 9 || ($checkInHour === 9 && $checkInMinute > 0)) {
            return 'late';
        }

        // Check if early (before 7:00 AM)
        if ($checkInHour < 7) {
            return 'early';
        }

        return 'present';
    }

    public function isWithinTimeWindow(string $action): bool
    {
        $now = now();
        $currentTime = $now->format('H:i');

        if ($action === 'checkIn') {
            $start = config('attendance.check_in_start', '07:00');
            $end = config('attendance.check_in_end', '10:00');
            return $currentTime >= $start && $currentTime <= $end;
        } elseif ($action === 'checkOut') {
            $start = config('attendance.check_out_start', '17:00');
            $end = config('attendance.check_out_end', '19:00');
            return $currentTime >= $start && $currentTime <= $end;
        }

        return false;
    }

    public function getTimeWindowInfo(string $action): array
    {
        if ($action === 'checkIn') {
            return [
                'start' => config('attendance.check_in_start', '07:00'),
                'end' => config('attendance.check_in_end', '10:00'),
                'label' => 'Check-in Window'
            ];
        } elseif ($action === 'checkOut') {
            return [
                'start' => config('attendance.check_out_start', '17:00'),
                'end' => config('attendance.check_out_end', '19:00'),
                'label' => 'Check-out Window'
            ];
        }

        return [];
    }
}
