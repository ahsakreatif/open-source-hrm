<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Human Resources',
                'code' => 'HR',
                'description' => 'Manages employee relations, recruitment, training, and organizational development.',
            ],
            [
                'name' => 'Information Technology',
                'code' => 'IT',
                'description' => 'Handles all technology infrastructure, software development, and technical support.',
            ],
            [
                'name' => 'Finance',
                'code' => 'FIN',
                'description' => 'Manages financial planning, accounting, budgeting, and financial reporting.',
            ],
            [
                'name' => 'Marketing',
                'code' => 'MKT',
                'description' => 'Responsible for brand management, advertising, and market research.',
            ],
            [
                'name' => 'Sales',
                'code' => 'SALES',
                'description' => 'Handles customer acquisition, account management, and revenue generation.',
            ],
            [
                'name' => 'Operations',
                'code' => 'OPS',
                'description' => 'Manages day-to-day business operations and process optimization.',
            ],
            [
                'name' => 'Customer Service',
                'code' => 'CS',
                'description' => 'Provides customer support and maintains customer satisfaction.',
            ],
            [
                'name' => 'Research & Development',
                'code' => 'R&D',
                'description' => 'Focuses on innovation, product development, and technological advancement.',
            ],
            [
                'name' => 'Legal',
                'code' => 'LEGAL',
                'description' => 'Handles legal compliance, contracts, and regulatory matters.',
            ],
            [
                'name' => 'Administration',
                'code' => 'ADMIN',
                'description' => 'Manages administrative tasks, facilities, and office operations.',
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
