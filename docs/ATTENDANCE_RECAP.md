# Attendance Recap System

The Attendance Recap system provides monthly summaries of employee attendance and leave data for payroll processing.

## Overview

The `AttendanceRecap` model aggregates data from:
- `Attendance` records (clock in/out times, hours worked)
- `Leave` records (approved leave days)
- Calculates working days, overtime, late arrivals, and attendance rates

## Models

### AttendanceRecap Model

**Location:** `app/Models/AttendanceRecap.php`

**Key Features:**
- Monthly aggregation of attendance data
- Automatic calculation of working hours, overtime, and attendance rates
- Integration with Payroll system
- Support for late arrivals and early departures

**Key Fields:**
- `employee_id` - Employee reference
- `year` / `month` - Period for recap
- `total_days_present` - Days employee was present
- `total_hours_worked` - Total hours worked
- `overtime_hours` - Hours worked beyond standard
- `total_days_leave` - Approved leave days
- `attendance_rate` - Percentage attendance rate
- `late_minutes` / `early_departure_minutes` - Tardiness tracking

## Usage Examples

### 1. Generate Attendance Recap

```php
// Generate recap for specific employee
$recap = AttendanceRecap::generateRecap($employeeId, 2024, 8);

// Generate recaps for all employees
$results = AttendanceRecapService::generateMonthlyRecap(2024, 8);
```

### 2. Use in Payroll Calculation

```php
// Calculate payroll using attendance recap
$payroll = Payroll::calculateFromAttendanceRecap($employeeId, 2024, 8, $hourlyRate);

// Get payroll summary with attendance details
$summary = $payroll->getPayrollSummary();
```

### 3. Command Line Usage

```bash
# Generate recap for all employees (current month)
php artisan attendance:generate-recap

# Generate recap for specific employee
php artisan attendance:generate-recap --employee=1

# Generate recap for specific month
php artisan attendance:generate-recap --year=2024 --month=8

# Recalculate existing recaps
php artisan attendance:generate-recap --recalculate
```

## Service Methods

### AttendanceRecapService

**Location:** `app/Services/AttendanceRecapService.php`

**Key Methods:**

1. **generateMonthlyRecap($year, $month)**
   - Generates recaps for all active employees
   - Returns array with success/error status

2. **getPayrollSummary($employeeId, $year, $month)**
   - Returns attendance summary for payroll calculation
   - Includes hours worked, overtime, leave days

3. **calculateWorkingHours($employeeId, $year, $month)**
   - Calculates standard hours, overtime, and leave hours
   - Returns total payable hours

4. **getAttendanceStatistics($year, $month)**
   - Returns company-wide attendance statistics
   - Includes averages and counts

## Database Schema

### attendance_recaps Table

```sql
CREATE TABLE attendance_recaps (
    id BIGINT PRIMARY KEY,
    employee_id BIGINT,
    year INT,
    month INT,
    total_days_present INT DEFAULT 0,
    total_hours_worked DECIMAL(8,2) DEFAULT 0,
    total_days_absent INT DEFAULT 0,
    total_days_leave INT DEFAULT 0,
    total_leave_hours DECIMAL(8,2) DEFAULT 0,
    overtime_hours DECIMAL(8,2) DEFAULT 0,
    late_minutes INT DEFAULT 0,
    early_departure_minutes INT DEFAULT 0,
    working_days_in_month INT DEFAULT 0,
    attendance_rate DECIMAL(5,2) DEFAULT 0,
    status ENUM('pending', 'completed', 'error') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY unique_employee_month_recap (employee_id, year, month)
);
```

### Payroll Integration

The `payrolls` table includes an `attendance_recap_id` foreign key to link payroll records with attendance recaps.

## Calculations

### Working Days
- Excludes weekends
- Counts business days in the month

### Attendance Rate
```
attendance_rate = (days_present / (working_days - leave_days)) * 100
```

### Overtime Hours
```
overtime_hours = max(0, total_hours_worked - (days_present * 8))
```

### Late/Early Departure
- Compares clock in/out times with shift start/end times
- Accumulates minutes for payroll deductions

## Integration with Payroll

1. **Automatic Generation**: Attendance recaps are generated before payroll calculation
2. **Hour Calculation**: Payroll uses recap data to calculate payable hours
3. **Rate Application**: Different rates for standard hours, overtime, and leave
4. **Deductions**: Late arrivals and early departures can affect pay

## Best Practices

1. **Monthly Generation**: Generate recaps at the end of each month
2. **Validation**: Review recaps before payroll processing
3. **Recalculation**: Use recalculation when attendance data is updated
4. **Backup**: Keep historical recap data for audit purposes

## Troubleshooting

### Common Issues

1. **Missing Shift Data**: Ensure employee has assigned shifts for accurate time calculations
2. **Leave Overlaps**: Handle overlapping leave periods correctly
3. **Weekend Work**: Consider weekend work policies in calculations
4. **Holidays**: Add holiday calendar integration for accurate working days

### Debug Commands

```bash
# Check specific employee recap
php artisan attendance:generate-recap --employee=1 --year=2024 --month=8

# Recalculate problematic recaps
php artisan attendance:generate-recap --recalculate --year=2024 --month=8
``` 
