<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Office Location
    |--------------------------------------------------------------------------
    |
    | These coordinates are used as a fallback when an employee doesn't have
    | a specific location assigned. This should be the main office location.
    |
    */
    'default_latitude' => env('ATTENDANCE_DEFAULT_LATITUDE', -6.389893),
    'default_longitude' => env('ATTENDANCE_DEFAULT_LONGITUDE', 106.720359),
    'default_office_name' => env('ATTENDANCE_DEFAULT_OFFICE_NAME', 'South Jakarta Office'),
    'default_office_address' => env('ATTENDANCE_DEFAULT_OFFICE_ADDRESS', 'South Jakarta, Indonesia'),

    /*
    |--------------------------------------------------------------------------
    | Office Radius Settings
    |--------------------------------------------------------------------------
    |
    | The maximum distance (in meters) an employee can be from the office
    | to submit attendance.
    |
    */
    'max_radius_meters' => env('ATTENDANCE_MAX_RADIUS', 100),

    /*
    |--------------------------------------------------------------------------
    | Time Window Settings
    |--------------------------------------------------------------------------
    |
    | The time windows when employees can check in and check out.
    | Times should be in 24-hour format (HH:MM).
    |
    */
    'check_in_start' => env('ATTENDANCE_CHECK_IN_START', '07:00'),
    'check_in_end' => env('ATTENDANCE_CHECK_IN_END', '09:00'),
    'check_out_start' => env('ATTENDANCE_CHECK_OUT_START', '17:00'),
    'check_out_end' => env('ATTENDANCE_CHECK_OUT_END', '19:00'),

    /*
    |--------------------------------------------------------------------------
    | GPS Settings
    |--------------------------------------------------------------------------
    |
    | GPS accuracy and timeout settings for location capture.
    |
    */
    'gps_accuracy_meters' => env('ATTENDANCE_GPS_ACCURACY', 50),
    'gps_timeout_seconds' => env('ATTENDANCE_GPS_TIMEOUT', 10),
    'gps_max_age_seconds' => env('ATTENDANCE_GPS_MAX_AGE', 60),
];
