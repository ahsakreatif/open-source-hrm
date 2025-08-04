<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Position;
use App\Models\Department;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get department IDs for relationships
        $hrDept = Department::where('code', 'HR')->first();
        $itDept = Department::where('code', 'IT')->first();
        $financeDept = Department::where('code', 'FIN')->first();
        $marketingDept = Department::where('code', 'MKT')->first();
        $salesDept = Department::where('code', 'SALES')->first();
        $opsDept = Department::where('code', 'OPS')->first();
        $csDept = Department::where('code', 'CS')->first();
        $rdDept = Department::where('code', 'R&D')->first();
        $legalDept = Department::where('code', 'LEGAL')->first();
        $adminDept = Department::where('code', 'ADMIN')->first();

        $positions = [
            // HR Positions
            [
                'title' => 'HR Manager',
                'department_id' => $hrDept?->id,
                'code' => 'HR-MGR',
                'description' => 'Oversees all HR functions including recruitment, employee relations, and policy development.',
                'salary' => 75000.00,
            ],
            [
                'title' => 'HR Specialist',
                'department_id' => $hrDept?->id,
                'code' => 'HR-SPEC',
                'description' => 'Handles recruitment, onboarding, and employee relations.',
                'salary' => 55000.00,
            ],
            [
                'title' => 'Recruiter',
                'department_id' => $hrDept?->id,
                'code' => 'HR-REC',
                'description' => 'Responsible for talent acquisition and recruitment processes.',
                'salary' => 50000.00,
            ],

            // IT Positions
            [
                'title' => 'IT Director',
                'department_id' => $itDept?->id,
                'code' => 'IT-DIR',
                'description' => 'Leads IT strategy, infrastructure, and technology initiatives.',
                'salary' => 120000.00,
            ],
            [
                'title' => 'Senior Software Developer',
                'department_id' => $itDept?->id,
                'code' => 'IT-SSD',
                'description' => 'Develops complex software solutions and mentors junior developers.',
                'salary' => 95000.00,
            ],
            [
                'title' => 'Software Developer',
                'department_id' => $itDept?->id,
                'code' => 'IT-DEV',
                'description' => 'Develops and maintains software applications.',
                'salary' => 75000.00,
            ],
            [
                'title' => 'System Administrator',
                'department_id' => $itDept?->id,
                'code' => 'IT-SYS',
                'description' => 'Manages IT infrastructure and system maintenance.',
                'salary' => 70000.00,
            ],
            [
                'title' => 'IT Support Specialist',
                'department_id' => $itDept?->id,
                'code' => 'IT-SUP',
                'description' => 'Provides technical support and troubleshooting.',
                'salary' => 55000.00,
            ],

            // Finance Positions
            [
                'title' => 'Finance Manager',
                'department_id' => $financeDept?->id,
                'code' => 'FIN-MGR',
                'description' => 'Oversees financial planning, budgeting, and reporting.',
                'salary' => 85000.00,
            ],
            [
                'title' => 'Senior Accountant',
                'department_id' => $financeDept?->id,
                'code' => 'FIN-SACC',
                'description' => 'Handles complex accounting tasks and financial analysis.',
                'salary' => 65000.00,
            ],
            [
                'title' => 'Accountant',
                'department_id' => $financeDept?->id,
                'code' => 'FIN-ACC',
                'description' => 'Manages day-to-day accounting operations.',
                'salary' => 55000.00,
            ],

            // Marketing Positions
            [
                'title' => 'Marketing Director',
                'department_id' => $marketingDept?->id,
                'code' => 'MKT-DIR',
                'description' => 'Leads marketing strategy and brand development.',
                'salary' => 100000.00,
            ],
            [
                'title' => 'Marketing Manager',
                'department_id' => $marketingDept?->id,
                'code' => 'MKT-MGR',
                'description' => 'Manages marketing campaigns and brand initiatives.',
                'salary' => 75000.00,
            ],
            [
                'title' => 'Digital Marketing Specialist',
                'department_id' => $marketingDept?->id,
                'code' => 'MKT-DIG',
                'description' => 'Handles digital marketing campaigns and social media.',
                'salary' => 60000.00,
            ],

            // Sales Positions
            [
                'title' => 'Sales Director',
                'department_id' => $salesDept?->id,
                'code' => 'SALES-DIR',
                'description' => 'Leads sales strategy and revenue generation.',
                'salary' => 110000.00,
            ],
            [
                'title' => 'Sales Manager',
                'department_id' => $salesDept?->id,
                'code' => 'SALES-MGR',
                'description' => 'Manages sales team and customer relationships.',
                'salary' => 80000.00,
            ],
            [
                'title' => 'Sales Representative',
                'department_id' => $salesDept?->id,
                'code' => 'SALES-REP',
                'description' => 'Generates sales and maintains customer relationships.',
                'salary' => 50000.00,
            ],

            // Operations Positions
            [
                'title' => 'Operations Manager',
                'department_id' => $opsDept?->id,
                'code' => 'OPS-MGR',
                'description' => 'Oversees business operations and process optimization.',
                'salary' => 85000.00,
            ],
            [
                'title' => 'Operations Specialist',
                'department_id' => $opsDept?->id,
                'code' => 'OPS-SPEC',
                'description' => 'Supports operational processes and efficiency improvements.',
                'salary' => 60000.00,
            ],

            // Customer Service Positions
            [
                'title' => 'Customer Service Manager',
                'department_id' => $csDept?->id,
                'code' => 'CS-MGR',
                'description' => 'Manages customer service team and satisfaction initiatives.',
                'salary' => 65000.00,
            ],
            [
                'title' => 'Customer Service Representative',
                'department_id' => $csDept?->id,
                'code' => 'CS-REP',
                'description' => 'Provides customer support and resolves issues.',
                'salary' => 40000.00,
            ],

            // R&D Positions
            [
                'title' => 'R&D Manager',
                'department_id' => $rdDept?->id,
                'code' => 'RD-MGR',
                'description' => 'Leads research and development initiatives.',
                'salary' => 95000.00,
            ],
            [
                'title' => 'Research Scientist',
                'department_id' => $rdDept?->id,
                'code' => 'RD-SCI',
                'description' => 'Conducts research and develops new products.',
                'salary' => 75000.00,
            ],

            // Legal Positions
            [
                'title' => 'Legal Counsel',
                'department_id' => $legalDept?->id,
                'code' => 'LEGAL-COUN',
                'description' => 'Provides legal advice and handles compliance matters.',
                'salary' => 90000.00,
            ],
            [
                'title' => 'Legal Assistant',
                'department_id' => $legalDept?->id,
                'code' => 'LEGAL-ASST',
                'description' => 'Supports legal operations and document management.',
                'salary' => 50000.00,
            ],

            // Administration Positions
            [
                'title' => 'Administrative Manager',
                'department_id' => $adminDept?->id,
                'code' => 'ADMIN-MGR',
                'description' => 'Manages administrative operations and office management.',
                'salary' => 60000.00,
            ],
            [
                'title' => 'Administrative Assistant',
                'department_id' => $adminDept?->id,
                'code' => 'ADMIN-ASST',
                'description' => 'Provides administrative support and office coordination.',
                'salary' => 40000.00,
            ],
        ];

        foreach ($positions as $position) {
            Position::create($position);
        }
    }
}
