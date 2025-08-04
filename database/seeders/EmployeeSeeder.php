<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get department and position IDs for relationships
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

        $hrManager = Position::where('code', 'HR-MGR')->first();
        $hrSpecialist = Position::where('code', 'HR-SPEC')->first();
        $itDirector = Position::where('code', 'IT-DIR')->first();
        $seniorDeveloper = Position::where('code', 'IT-SSD')->first();
        $financeManager = Position::where('code', 'FIN-MGR')->first();
        $marketingDirector = Position::where('code', 'MKT-DIR')->first();
        $salesManager = Position::where('code', 'SALES-MGR')->first();
        $opsManager = Position::where('code', 'OPS-MGR')->first();
        $csManager = Position::where('code', 'CS-MGR')->first();
        $rdManager = Position::where('code', 'RD-MGR')->first();

        $employees = [
            [
                'employee_number' => 'EMP001',
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'national_id' => '12345678901',
                'kra_pin' => 'A123456789B',
                'email' => 'sarah.johnson@company.com',
                'phone' => '+254700123456',
                'emergency_contact_name' => 'Michael Johnson',
                'emergency_contact_phone' => '+254700123457',
                'date_of_birth' => '1985-03-15',
                'gender' => 'Female',
                'marital_status' => 'Married',
                'employment_type' => 'Permanent',
                'hire_date' => '2020-01-15',
                'is_active' => true,
                'department_id' => $hrDept?->id,
                'position_id' => $hrManager?->id,
                'next_of_kin_name' => 'Michael Johnson',
                'next_of_kin_relationship' => 'Spouse',
                'next_of_kin_phone' => '+254700123457',
                'next_of_kin_email' => 'michael.johnson@email.com',
            ],
            [
                'employee_number' => 'EMP002',
                'first_name' => 'David',
                'last_name' => 'Chen',
                'national_id' => '12345678902',
                'kra_pin' => 'B123456789C',
                'email' => 'david.chen@company.com',
                'phone' => '+254700123458',
                'emergency_contact_name' => 'Lisa Chen',
                'emergency_contact_phone' => '+254700123459',
                'date_of_birth' => '1982-07-22',
                'gender' => 'Male',
                'marital_status' => 'Married',
                'employment_type' => 'Permanent',
                'hire_date' => '2019-06-10',
                'is_active' => true,
                'department_id' => $itDept?->id,
                'position_id' => $itDirector?->id,
                'next_of_kin_name' => 'Lisa Chen',
                'next_of_kin_relationship' => 'Spouse',
                'next_of_kin_phone' => '+254700123459',
                'next_of_kin_email' => 'lisa.chen@email.com',
            ],
            [
                'employee_number' => 'EMP003',
                'first_name' => 'Maria',
                'last_name' => 'Garcia',
                'national_id' => '12345678903',
                'kra_pin' => 'C123456789D',
                'email' => 'maria.garcia@company.com',
                'phone' => '+254700123460',
                'emergency_contact_name' => 'Carlos Garcia',
                'emergency_contact_phone' => '+254700123461',
                'date_of_birth' => '1990-11-08',
                'gender' => 'Female',
                'marital_status' => 'Single',
                'employment_type' => 'Permanent',
                'hire_date' => '2021-03-20',
                'is_active' => true,
                'department_id' => $financeDept?->id,
                'position_id' => $financeManager?->id,
                'next_of_kin_name' => 'Carlos Garcia',
                'next_of_kin_relationship' => 'Brother',
                'next_of_kin_phone' => '+254700123461',
                'next_of_kin_email' => 'carlos.garcia@email.com',
            ],
            [
                'employee_number' => 'EMP004',
                'first_name' => 'James',
                'last_name' => 'Wilson',
                'national_id' => '12345678904',
                'kra_pin' => 'D123456789E',
                'email' => 'james.wilson@company.com',
                'phone' => '+254700123462',
                'emergency_contact_name' => 'Emma Wilson',
                'emergency_contact_phone' => '+254700123463',
                'date_of_birth' => '1988-05-12',
                'gender' => 'Male',
                'marital_status' => 'Married',
                'employment_type' => 'Permanent',
                'hire_date' => '2020-08-05',
                'is_active' => true,
                'department_id' => $marketingDept?->id,
                'position_id' => $marketingDirector?->id,
                'next_of_kin_name' => 'Emma Wilson',
                'next_of_kin_relationship' => 'Spouse',
                'next_of_kin_phone' => '+254700123463',
                'next_of_kin_email' => 'emma.wilson@email.com',
            ],
            [
                'employee_number' => 'EMP005',
                'first_name' => 'Aisha',
                'last_name' => 'Patel',
                'national_id' => '12345678905',
                'kra_pin' => 'E123456789F',
                'email' => 'aisha.patel@company.com',
                'phone' => '+254700123464',
                'emergency_contact_name' => 'Raj Patel',
                'emergency_contact_phone' => '+254700123465',
                'date_of_birth' => '1992-09-30',
                'gender' => 'Female',
                'marital_status' => 'Single',
                'employment_type' => 'Contract',
                'hire_date' => '2022-01-10',
                'is_active' => true,
                'department_id' => $salesDept?->id,
                'position_id' => $salesManager?->id,
                'next_of_kin_name' => 'Raj Patel',
                'next_of_kin_relationship' => 'Father',
                'next_of_kin_phone' => '+254700123465',
                'next_of_kin_email' => 'raj.patel@email.com',
            ],
            [
                'employee_number' => 'EMP006',
                'first_name' => 'Robert',
                'last_name' => 'Taylor',
                'national_id' => '12345678906',
                'kra_pin' => 'F123456789G',
                'email' => 'robert.taylor@company.com',
                'phone' => '+254700123466',
                'emergency_contact_name' => 'Jennifer Taylor',
                'emergency_contact_phone' => '+254700123467',
                'date_of_birth' => '1985-12-03',
                'gender' => 'Male',
                'marital_status' => 'Married',
                'employment_type' => 'Permanent',
                'hire_date' => '2018-11-15',
                'is_active' => true,
                'department_id' => $opsDept?->id,
                'position_id' => $opsManager?->id,
                'next_of_kin_name' => 'Jennifer Taylor',
                'next_of_kin_relationship' => 'Spouse',
                'next_of_kin_phone' => '+254700123467',
                'next_of_kin_email' => 'jennifer.taylor@email.com',
            ],
            [
                'employee_number' => 'EMP007',
                'first_name' => 'Fatima',
                'last_name' => 'Hassan',
                'national_id' => '12345678907',
                'kra_pin' => 'G123456789H',
                'email' => 'fatima.hassan@company.com',
                'phone' => '+254700123468',
                'emergency_contact_name' => 'Ahmed Hassan',
                'emergency_contact_phone' => '+254700123469',
                'date_of_birth' => '1991-04-18',
                'gender' => 'Female',
                'marital_status' => 'Single',
                'employment_type' => 'Permanent',
                'hire_date' => '2021-07-22',
                'is_active' => true,
                'department_id' => $csDept?->id,
                'position_id' => $csManager?->id,
                'next_of_kin_name' => 'Ahmed Hassan',
                'next_of_kin_relationship' => 'Brother',
                'next_of_kin_phone' => '+254700123469',
                'next_of_kin_email' => 'ahmed.hassan@email.com',
            ],
            [
                'employee_number' => 'EMP008',
                'first_name' => 'Michael',
                'last_name' => 'Brown',
                'national_id' => '12345678908',
                'kra_pin' => 'H123456789I',
                'email' => 'michael.brown@company.com',
                'phone' => '+254700123470',
                'emergency_contact_name' => 'Linda Brown',
                'emergency_contact_phone' => '+254700123471',
                'date_of_birth' => '1987-08-25',
                'gender' => 'Male',
                'marital_status' => 'Married',
                'employment_type' => 'Permanent',
                'hire_date' => '2019-04-12',
                'is_active' => true,
                'department_id' => $rdDept?->id,
                'position_id' => $rdManager?->id,
                'next_of_kin_name' => 'Linda Brown',
                'next_of_kin_relationship' => 'Spouse',
                'next_of_kin_phone' => '+254700123471',
                'next_of_kin_email' => 'linda.brown@email.com',
            ],
            [
                'employee_number' => 'EMP009',
                'first_name' => 'Emily',
                'last_name' => 'Davis',
                'national_id' => '12345678909',
                'kra_pin' => 'I123456789J',
                'email' => 'emily.davis@company.com',
                'phone' => '+254700123472',
                'emergency_contact_name' => 'Thomas Davis',
                'emergency_contact_phone' => '+254700123473',
                'date_of_birth' => '1993-01-14',
                'gender' => 'Female',
                'marital_status' => 'Single',
                'employment_type' => 'Contract',
                'hire_date' => '2022-06-08',
                'is_active' => true,
                'department_id' => $itDept?->id,
                'position_id' => $seniorDeveloper?->id,
                'next_of_kin_name' => 'Thomas Davis',
                'next_of_kin_relationship' => 'Father',
                'next_of_kin_phone' => '+254700123473',
                'next_of_kin_email' => 'thomas.davis@email.com',
            ],
            [
                'employee_number' => 'EMP010',
                'first_name' => 'John',
                'last_name' => 'Anderson',
                'national_id' => '12345678910',
                'kra_pin' => 'J123456789K',
                'email' => 'john.anderson@company.com',
                'phone' => '+254700123474',
                'emergency_contact_name' => 'Mary Anderson',
                'emergency_contact_phone' => '+254700123475',
                'date_of_birth' => '1989-10-07',
                'gender' => 'Male',
                'marital_status' => 'Married',
                'employment_type' => 'Permanent',
                'hire_date' => '2020-12-01',
                'is_active' => true,
                'department_id' => $hrDept?->id,
                'position_id' => $hrSpecialist?->id,
                'next_of_kin_name' => 'Mary Anderson',
                'next_of_kin_relationship' => 'Spouse',
                'next_of_kin_phone' => '+254700123475',
                'next_of_kin_email' => 'mary.anderson@email.com',
            ],
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }
    }
}
