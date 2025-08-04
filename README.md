# 🇮🇩 Indonesian HRM System (Open Source)

A modern, Laravel + FilamentPHP-based **Human Resource Management System** tailored for Indonesian businesses. This solution simplifies employee management, payroll (PPh 21, BPJS Ketenagakerjaan, BPJS Kesehatan), attendance, and more — all in compliance with Indonesian labor laws.

<img src="img.png" alt="Open source HRM" style="max-width: 100%; border-radius: 8px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);" >

---

## 🔧 Tech Stack

-   **Laravel 12+**
-   **FilamentPHP 3.x** (admin panel)
-   **MySQL/MariaDB** (database)
-   **PHP 8.2+**
-   **Tailwind CSS** (via Filament)
-   **Alpine.js** (via Filament)
-   **Flowforge** (Kanban board for tasks)

---

## 🚀 Features

### ✅ Core Modules

-   **Employee Records** (with NPWP, BPJS Ketenagakerjaan, BPJS Kesehatan, etc.)
-   **Departments & Positions**
-   **Attendance Management**
-   **Leave Management**
-   **Payroll System** (with Indonesian tax compliance)
-   **Attendance Recap System** (monthly summaries)
-   **Task Board** (Kanban-style project management)
-   **Employee Portal** (self-service access)
-   **Admin Management**

### 🔥 Advanced Features

#### 📊 Attendance Recap System
- **Monthly attendance summaries** for payroll processing
- **Automatic calculation** of working hours, overtime, and attendance rates
- **Late arrival and early departure tracking**
- **Integration with payroll system** for accurate pay calculations
- **Command-line tools** for bulk recap generation

#### 💰 Payroll System
- **Indonesian tax compliance** (PPh 21, BPJS Ketenagakerjaan, BPJS Kesehatan)
- **Attendance-based calculations** using recap data
- **Multiple pay periods** support
- **Status tracking** (pending, completed, calculated, cancelled)
- **Gross and net pay calculations**

#### 📋 Task Management
- **Kanban board interface** for project management
- **Task assignment** to employees
- **Due date tracking**
- **Status management** (To Do, In Progress, Completed)
- **Visual task organization**

#### 👥 Employee Management
- **Comprehensive employee profiles** with Indonesian-specific fields
- **Emergency contact information**
- **Next of kin details**
- **NPWP and national ID tracking**
- **Department and position assignments**
- **Active/inactive status management**

#### 🏢 Department & Position Management
- **Hierarchical department structure**
- **Manager assignments**
- **Position definitions**
- **Employee role management**

#### 📅 Leave Management
- **Leave request tracking**
- **Approval workflows**
- **Leave balance management**
- **Integration with attendance system**

#### ⏰ Attendance Tracking
- **Clock in/out functionality**
- **Shift management**
- **Working hours calculation**
- **Overtime tracking**

---

## 📁 Project Structure

```
app/
├── Filament/
│   ├── Resources/
│   │   ├── EmployeeResource.php
│   │   ├── DepartmentResource.php
│   │   ├── AttendanceResource.php
│   │   ├── AttendanceRecapResource.php
│   │   ├── LeaveResource.php
│   │   ├── PayrollResource.php
│   │   └── AdminResource.php
│   └── Pages/
│       └── TasksBoardPage.php
├── Models/
│   ├── Employee.php
│   ├── Department.php
│   ├── Position.php
│   ├── Attendance.php
│   ├── AttendanceRecap.php
│   ├── Leave.php
│   ├── Payroll.php
│   ├── Task.php
│   └── User.php
└── Services/
    └── AttendanceRecapService.php
```

---

## ⚙️ Installation

```bash
git clone https://github.com/michaelnjuguna/open-source-hrm.git
cd open-source-hrm

composer install
cp .env.example .env
php artisan key:generate

# Setup DB credentials in .env
php artisan migrate --seed

composer run dev
```

## 🛠️ Usage

### Attendance Recap Generation

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

### Payroll Processing

1. Generate attendance recaps for the month
2. Create payroll records with attendance data
3. Calculate gross and net pay
4. Process payments

### Task Management

- Access the Tasks Board from the admin panel
- Create tasks and assign to employees
- Track progress using the Kanban interface
- Set due dates and priorities

---

## 📚 Documentation

- [Attendance Recap System](docs/ATTENDANCE_RECAP.md) - Detailed guide for the attendance recap feature

---

## 🤝 Contributing

All contributions are welcome. Please fork the repo, create a feature branch and submit a pull request.

### Development Setup

```bash
# Install dependencies
composer install
npm install

# Run development server
composer run dev
```

---

## 📜 License

[MIT license](LICENSE)

Made with ❤️ for Indonesian businesses

---

## 📋 TODO - Indonesian Compliance Updates

### 🔧 Database & Model Updates Needed

- [ ] **Replace KRA PIN field with NPWP (Nomor Pokok Wajib Pajak)**
  - Update `employees` table migration
  - Update Employee model fillable fields
  - Update EmployeeResource form and table columns
  - Add NPWP validation rules

- [ ] **Add Indonesian-specific tax fields**
  - BPJS Ketenagakerjaan number
  - BPJS Kesehatan number
  - PPh 21 tax calculations
  - JHT (Jaminan Hari Tua) contributions

- [ ] **Update Payroll System**
  - Implement PPh 21 calculation logic
  - Add BPJS Ketenagakerjaan deductions
  - Add BPJS Kesehatan deductions
  - Add JHT contribution calculations
  - Update payroll reports for Indonesian compliance

- [ ] **Add Indonesian Labor Law Compliance**
  - Minimum wage compliance
  - Overtime calculations (Indonesian standards)
  - Leave entitlements (Indonesian labor law)
  - Severance pay calculations
  - Working hours compliance

### 🎯 Priority Implementation Order

1. **High Priority**: NPWP field replacement and validation
2. **High Priority**: BPJS number fields addition
3. **Medium Priority**: PPh 21 calculation implementation
4. **Medium Priority**: BPJS deduction calculations
5. **Low Priority**: Additional Indonesian labor law features

### 📝 Notes

- Current system uses Kenyan tax systems (KRA PIN, NSSF, NHIF) which are not applicable to Indonesia
- Need to implement Indonesian tax identification (NPWP) and social security systems (BPJS)
- Payroll calculations need to follow Indonesian tax regulations
- All tax and social security references should be updated to Indonesian equivalents
