<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Main Office',
                'code' => 'HQ',
                'address' => 'Jl. Sudirman No. 123',
                'city' => 'Jakarta',
                'state' => 'Jakarta',
                'country' => 'Indonesia',
                'postal_code' => '12910',
                'phone' => '+62 21 12345678',
                'email' => 'info@company.com',
                'description' => 'Main headquarters office',
                'is_active' => true,
                'latitude' => '-6.175110',
                'longitude' => '106.865036',
            ],
            [
                'name' => 'Surabaya Branch',
                'code' => 'SBY',
                'address' => 'Jl. Basuki Rahmat No. 456',
                'city' => 'Surabaya',
                'state' => 'Surabaya',
                'country' => 'Indonesia',
                'postal_code' => '60271',
                'phone' => '+62 31 87654321',
                'email' => 'surabaya@company.com',
                'description' => 'Eastern Java region branch office',
                'is_active' => true,
                'latitude' => '-7.250000',
                'longitude' => '112.750000',
            ],
            [
                'name' => 'Bandung Branch',
                'code' => 'BDG',
                'address' => 'Jl. Asia Afrika No. 789',
                'city' => 'Bandung',
                'state' => 'Bandung',
                'country' => 'Indonesia',
                'postal_code' => '40111',
                'phone' => '+62 22 98765432',
                'email' => 'bandung@company.com',
                'description' => 'Western Java region branch office',
                'is_active' => true,
                'latitude' => '-6.900000',
                'longitude' => '107.600000',
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
