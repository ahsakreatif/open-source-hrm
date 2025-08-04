<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Shift;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'Morning Shift',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
            ],
            [
                'name' => 'Afternoon Shift',
                'start_time' => '16:00:00',
                'end_time' => '00:00:00',
            ],
            [
                'name' => 'Night Shift',
                'start_time' => '00:00:00',
                'end_time' => '08:00:00',
            ],
            [
                'name' => 'Day Shift',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
            ],
            [
                'name' => 'Evening Shift',
                'start_time' => '17:00:00',
                'end_time' => '01:00:00',
            ],
            [
                'name' => 'Early Morning',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
            ],
            [
                'name' => 'Late Night',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
            ],
            [
                'name' => 'Split Shift',
                'start_time' => '08:00:00',
                'end_time' => '12:00:00',
            ],
            [
                'name' => 'Part-Time Morning',
                'start_time' => '10:00:00',
                'end_time' => '14:00:00',
            ],
            [
                'name' => 'Part-Time Evening',
                'start_time' => '18:00:00',
                'end_time' => '22:00:00',
            ],
            [
                'name' => 'Weekend Shift',
                'start_time' => '10:00:00',
                'end_time' => '18:00:00',
            ],
            [
                'name' => 'Flexible Hours',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::create($shift);
        }
    }
}
