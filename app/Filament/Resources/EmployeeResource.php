<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\Employee;
use App\Models\Position;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter;
use App\Models\Department;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'HR Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Section::make('Basic Information')
                    ->collapsible()

                    ->schema([
                        TextInput::make('employee_number')
                            ->required()
                            ->maxLength(50)
                            ->label('Employee Number')
                            ->placeholder('Enter employee number'),
                        TextInput::make('first_name')
                            ->required(),
                        TextInput::make('last_name')
                            ->required(),
                        DatePicker::make('date_of_birth'),
                        Select::make('gender')
                            ->options(['Male' => 'Male', 'Female' => 'Female']),
                        Select::make('marital_status')
                            ->options([
                                'Single' => 'Single',
                                'Married' => 'Married',
                                'Divorced' => 'Divorced',
                                'Widowed' => 'Widowed'
                            ]),

                    ])
                    ->columns(2),
                Section::make('Contact Information')
                    ->collapsible()
                    ->schema([
                        TextInput::make('email')->email()->required()->label('Email Address (this will be the password for the employee)')
                            ->unique(ignoreRecord: true),
                        TextInput::make('phone')->tel()->label('Phone Number')->unique(ignoreRecord: true),
                        TextInput::make('national_id')->unique(ignoreRecord: true)
                            ->integer(),
                        TextInput::make('kra_pin'),
                    ])
                    ->columns(2),
                Section::make('Emergency Contact')
                    ->collapsible()
                    ->schema([
                        TextInput::make('emergency_contact_name'),
                        TextInput::make('emergency_contact_phone'),
                    ])
                    ->columns(2),
                Section::make('Next of Kin')
                    ->collapsible()
                    ->schema([
                        TextInput::make('next_of_kin_name')
                            ->label('Name'),
                        TextInput::make('next_of_kin_relationship')
                            ->label('Relationship'),
                        TextInput::make('next_of_kin_phone')
                            ->tel()
                            ->label('Phone'),
                        TextInput::make('next_of_kin_email')
                            ->label('Email')
                            ->email(),
                    ])
                    ->columns(2),
                Section::make('Employment Details')
                    ->collapsible()
                    ->schema([
                        Select::make('department_id')
                            ->relationship(
                                name: 'department',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn(Builder $query) => $query->select('id', 'name')->orderBy('name', 'asc')
                            )
                            ->label('Department')
                            ->searchable()
                            ->placeholder('Select a department')
                            ->preload()
                            // ->columnSpanFull()
                            ->nullable(),
                        Select::make('position_id')
                            ->options(
                                Position::all()->pluck('title', 'id')

                            )
                            ->label('Position')
                            ->searchable()
                            ->placeholder('Select a position')
                            ->preload()
                            ->nullable()
                            ->createOptionForm([
                                TextInput::make('title')
                                    ->required()
                                    ->label('Position Title'),
                                Select::make('department_id')
                                    ->options(
                                        Department::all()->pluck('name', 'id')
                                    ),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('code')
                                            ->label('Position Code')
                                            ->unique(ignoreRecord: true)
                                            ->nullable(),
                                        TextInput::make('salary')
                                            ->label('Salary')
                                            ->numeric()
                                            ->nullable(),
                                    ]),
                                Textarea::make('description')
                                    ->label('Description')
                                    ->nullable()
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(function (array $data) {
                                return Position::create([
                                    'title' => $data['title'],
                                    'department_id' => $data['department_id'],
                                    'code' => $data['code'] ?? null,
                                    'salary' => $data['salary'] ?? null,
                                    'description' => $data['description'] ?? null,
                                ])->id;
                            })
                            ->native(false),
                        Select::make('location_id')
                            ->relationship(
                                name: 'location',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn(Builder $query) => $query->select('id', 'name')->orderBy('name', 'asc')
                            )
                            ->label('Location')
                            ->searchable()
                            ->placeholder('Select a location')
                            ->preload()
                            ->nullable(),
                        Select::make('employment_type')
                            ->options([
                                'Permanent' => 'Permanent',
                                'Contract' => 'Contract',
                                'Casual' => 'Casual',
                            ]),
                        DatePicker::make('hire_date'),
                        DatePicker::make('termination_date'),
                        Toggle::make('is_active')->default(true),
                    ])

                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                static::getEloquentQuery()
                    ->with(['department'])
                    ->latest()
            )
            ->filters(
                [


                    Filter::make('is_active')
                        ->label('Active Employees')
                        // ->toggle()
                        ->query(fn(Builder $query): Builder => $query->where('is_active', true))
                        ->default(false),
                    Filter::make('is_inactive')
                        ->label('Inactive Employees')
                        // ->toggle()
                        ->query(fn(Builder $query): Builder => $query->where('is_active', false))
                        ->default(false),
                    SelectFilter::make('department_id')
                        ->label('Department')
                        ->options(
                            fn() => \App\Models\Department::all()->pluck('name', 'id')
                        )
                        ->searchable(),
                    SelectFilter::make('employment_type')
                        ->label('Employment Type')
                        ->options([
                            'Permanent' => 'Permanent',
                            'Contract' => 'Contract',
                            'Casual' => 'Casual',
                        ]),
                    SelectFilter::make('position_id')
                        ->label('Position')
                        ->options(
                            Position::all()->pluck('title', 'id')
                        )
                        ->searchable(),




                ],

            )
            ->columns([
                //
                Tables\Columns\TextColumn::make('employee_number')
                    ->label('Employee No.')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(
                        [
                            'first_name',
                            'last_name'
                        ]
                    )
                    ->sortable([
                        'first_name',
                        'last_name'
                    ]),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('position.title')
                    ->label('Position')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('national_id')
                    ->label('National ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('kra_pin')
                    ->label('KRA PIN')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('employment_type')
                    ->label('Employment Type')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Is Active')

                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->label('Date of Birth')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('termination_date')
                    ->label('Termination Date')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('hire_date')
                    ->label('Hire Date')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

            ])

            ->actions([
                Tables\Actions\ActionGroup::make([

                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])

            ])
            ->headerActions([
                Action::make('import')
                    ->label('Import Employees')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->form([
                        FileUpload::make('file')
                            ->label('CSV File')
                            ->acceptedFileTypes(['text/csv', 'application/csv', '.csv'])
                            ->required()
                            ->helperText('Upload a CSV file with employee data. Download the template for the correct format.')
                            ->disk('local')
                            ->directory('imports')
                            ->visibility('private'),
                    ])
                    ->action(function (array $data): void {
                        $filePath = Storage::disk('local')->path($data['file']);

                        try {
                            $imported = self::importEmployeesFromCsv($filePath);

                            Notification::make()
                                ->title('Import Successful')
                                ->body("Successfully imported {$imported['success']} employees. " .
                                      ($imported['errors'] > 0 ? "{$imported['errors']} rows had errors." : ''))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Import Failed')
                                ->body('Error: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->modalSubmitActionLabel('Import Employees')
                    ->modalCancelActionLabel('Cancel')
                    ->modalWidth('lg'),
                Action::make('download_template')
                    ->label('Download Template')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function (): \Symfony\Component\HttpFoundation\StreamedResponse {
                        return self::downloadCsvTemplate();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'view' => Pages\ViewEmployee::route('/{record}'),
            // 'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }

    /**
     * Import employees from CSV file
     */
    public static function importEmployeesFromCsv(string $filePath): array
    {
        $success = 0;
        $errors = 0;
        $errorMessages = [];

        if (!file_exists($filePath)) {
            throw new \Exception('File not found');
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception('Could not open file');
        }

        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            throw new \Exception('Invalid CSV format');
        }

        // Remove BOM from first header if present
        if (!empty($headers[0])) {
            $headers[0] = preg_replace('/^\x{FEFF}/u', '', $headers[0]);
        }

        // Expected headers - use database column names
        $expectedHeaders = [
            'employee_number',
            'first_name',
            'last_name',
            'email',
            'phone',
            'national_id',
            'kra_pin',
            'date_of_birth',
            'gender',
            'marital_status',
            'employment_type',
            'hire_date',
            'department_name',
            'position_title',
            'location_name',
            'emergency_contact_name',
            'emergency_contact_phone',
            'next_of_kin_name',
            'next_of_kin_relationship',
            'next_of_kin_phone',
            'next_of_kin_email',
            'is_active'
        ];

        // Required fields
        $requiredFields = ['employee_number', 'first_name'];

        Log::info($headers);

        // Validate headers and create mapping
        $headerMap = [];
        foreach ($expectedHeaders as $expectedField) {
            $found = false;
            foreach ($headers as $index => $header) {
                if (strtolower(trim($header)) === strtolower(trim($expectedField))) {
                    $headerMap[$expectedField] = $index;
                    $found = true;
                    break;
                }
            }
            if (!$found && in_array($expectedField, $requiredFields)) {
                throw new \Exception("Required column '{$expectedField}' not found in CSV");
            }
        }

        // First pass: Collect all unique relationship values and employee data
        $uniqueDepartments = [];
        $uniquePositions = [];
        $uniqueLocations = [];
        $employeeNumbers = [];
        $emails = [];

        // Reset file pointer to beginning (after headers)
        rewind($handle);
        fgetcsv($handle); // Skip header row

        while (($row = fgetcsv($handle)) !== false) {
            // Collect unique department names
            if (isset($headerMap['department_name']) && isset($row[$headerMap['department_name']])) {
                $deptName = trim($row[$headerMap['department_name']]);
                if ($deptName) {
                    $uniqueDepartments[$deptName] = true;
                }
            }

            // Collect unique position titles
            if (isset($headerMap['position_title']) && isset($row[$headerMap['position_title']])) {
                $posTitle = trim($row[$headerMap['position_title']]);
                if ($posTitle) {
                    $uniquePositions[$posTitle] = true;
                }
            }

            // Collect unique location names
            if (isset($headerMap['location_name']) && isset($row[$headerMap['location_name']])) {
                $locName = trim($row[$headerMap['location_name']]);
                if ($locName) {
                    $uniqueLocations[$locName] = true;
                }
            }

            // Collect employee numbers and emails for duplicate checking
            if (isset($headerMap['employee_number']) && isset($row[$headerMap['employee_number']])) {
                $empNum = trim($row[$headerMap['employee_number']]);
                if ($empNum) {
                    $employeeNumbers[$empNum] = true;
                }
            }

            if (isset($headerMap['email']) && isset($row[$headerMap['email']])) {
                $email = trim($row[$headerMap['email']]);
                if ($email) { // Only collect non-empty emails
                    $emails[$email] = true;
                }
            }
        }

        // Create missing relationships using firstOrCreate
        $departmentMap = [];
        foreach (array_keys($uniqueDepartments) as $deptName) {
            $department = Department::firstOrCreate(
                ['name' => $deptName],
                ['description' => 'Auto-created during import']
            );
            $departmentMap[$deptName] = $department->id;
        }

        $positionMap = [];
        foreach (array_keys($uniquePositions) as $posTitle) {
            $position = Position::firstOrCreate(
                ['title' => $posTitle],
                ['description' => 'Auto-created during import']
            );
            $positionMap[$posTitle] = $position->id;
        }

        $locationMap = [];
        foreach (array_keys($uniqueLocations) as $locName) {
            $location = \App\Models\Location::firstOrCreate(
                ['name' => $locName],
                ['address' => 'Auto-created during import']
            );
            $locationMap[$locName] = $location->id;
        }

        // Check for existing employee numbers and emails in bulk
        $existingEmployeeNumbers = Employee::whereIn('employee_number', array_keys($employeeNumbers))->pluck('employee_number')->toArray();
        $existingEmails = Employee::whereIn('email', array_keys($emails))->pluck('email')->toArray();

        DB::beginTransaction();

        try {
            // Reset file pointer for second pass
            rewind($handle);
            fgetcsv($handle); // Skip header row
            $rowNumber = 1; // Start from 1 since we already read the header

            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                try {
                    $employeeData = [];

                    // Map CSV data to employee fields
                    foreach ($headerMap as $field => $index) {
                        if (isset($row[$index])) {
                            $value = trim($row[$index]);

                            // Handle specific field transformations
                            switch ($field) {
                                case 'date_of_birth':
                                case 'hire_date':
                                    if ($value && $value !== '') {
                                        try {
                                            $employeeData[$field] = \Carbon\Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d');
                                        } catch (\Exception $e) {
                                            throw new \Exception("Invalid date format for {$field}: {$value}");
                                        }
                                    }
                                    break;
                                case 'is_active':
                                    $employeeData[$field] = in_array(strtolower($value), ['true', '1', 'yes', 'active']);
                                    break;
                                case 'gender':
                                    if ($value && !in_array($value, ['Male', 'Female'])) {
                                        throw new \Exception("Invalid gender: {$value}. Must be 'Male' or 'Female'");
                                    }
                                    $employeeData[$field] = $value;
                                    break;
                                case 'marital_status':
                                    if ($value && !in_array($value, ['Single', 'Married', 'Divorced', 'Widowed'])) {
                                        throw new \Exception("Invalid marital status: {$value}");
                                    }
                                    $employeeData[$field] = $value;
                                    break;
                                case 'employment_type':
                                    if ($value && !in_array($value, ['Permanent', 'Contract', 'Casual'])) {
                                        throw new \Exception("Invalid employment type: {$value}");
                                    }
                                    $employeeData[$field] = $value;
                                    break;
                                default:
                                    $employeeData[$field] = $value;
                                    break;
                            }
                        }
                    }

                    // Handle relationships using pre-mapped IDs
                    if (isset($employeeData['department_name']) && $employeeData['department_name']) {
                        $deptName = $employeeData['department_name'];
                        if (!isset($departmentMap[$deptName])) {
                            throw new \Exception("Department '{$deptName}' not found in pre-processing");
                        }
                        $employeeData['department_id'] = $departmentMap[$deptName];
                        unset($employeeData['department_name']);
                    }

                    if (isset($employeeData['position_title']) && $employeeData['position_title']) {
                        $posTitle = $employeeData['position_title'];
                        if (!isset($positionMap[$posTitle])) {
                            throw new \Exception("Position '{$posTitle}' not found in pre-processing");
                        }
                        $employeeData['position_id'] = $positionMap[$posTitle];
                        unset($employeeData['position_title']);
                    }

                    if (isset($employeeData['location_name']) && $employeeData['location_name']) {
                        $locName = $employeeData['location_name'];
                        if (!isset($locationMap[$locName])) {
                            throw new \Exception("Location '{$locName}' not found in pre-processing");
                        }
                        $employeeData['location_id'] = $locationMap[$locName];
                        unset($employeeData['location_name']);
                    }

                    // Check for required fields
                    if (empty($employeeData['employee_number']) || empty($employeeData['first_name'])) {
                        throw new \Exception("Missing required fields: employee_number and first_name are required");
                    }

                    // Check for duplicate employee number or email using pre-collected data
                    if (in_array($employeeData['employee_number'], $existingEmployeeNumbers)) {
                        throw new \Exception("Employee number '{$employeeData['employee_number']}' already exists");
                    }

                    // Only check email duplicates if email is not null/empty
                    if (!empty($employeeData['email']) && in_array($employeeData['email'], $existingEmails)) {
                        throw new \Exception("Email '{$employeeData['email']}' already exists");
                    }

                    // Clean nullable fields - set empty strings to null to avoid unique constraint violations
                    $nullableFields = [
                        'last_name', 'national_id', 'kra_pin', 'email', 'phone',
                        'emergency_contact_name', 'emergency_contact_phone', 'date_of_birth',
                        'gender', 'marital_status', 'employment_type', 'hire_date',
                        'next_of_kin_name', 'next_of_kin_relationship', 'next_of_kin_phone',
                        'next_of_kin_email'
                    ];

                    foreach ($nullableFields as $field) {
                        if (isset($employeeData[$field]) && $employeeData[$field] === '') {
                            $employeeData[$field] = null;
                        }
                    }

                    // Set default values - use faster hash for bulk import
                    $employeeData['is_active'] = $employeeData['is_active'] ?? true;
                    $employeeData['password'] = hash('sha256', $employeeData['email'] . 'default_salt'); // Faster hash for bulk import

                    // Create employee
                    Employee::create($employeeData);
                    $success++;

                } catch (\Exception $e) {
                    $errors++;
                    $errorMessages[] = "Row {$rowNumber}: " . $e->getMessage();
                }
            }

            fclose($handle);
            DB::commit();

            if ($errors > 0) {
                Log::error($errorMessages);
            }

            return [
                'success' => $success,
                'errors' => $errors,
                'error_messages' => $errorMessages
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getTrace());

            fclose($handle);
            throw $e;
        }
    }

    /**
     * Download CSV template for employee import
     */
    public static function downloadCsvTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        // Use database column names as headers (keys from the expectedHeaders array)
        $headers = [
            'employee_number',
            'first_name',
            'last_name',
            'email',
            'phone',
            'national_id',
            'kra_pin',
            'date_of_birth',
            'gender',
            'marital_status',
            'employment_type',
            'hire_date',
            'department_name',
            'position_title',
            'location_name',
            'emergency_contact_name',
            'emergency_contact_phone',
            'next_of_kin_name',
            'next_of_kin_relationship',
            'next_of_kin_phone',
            'next_of_kin_email',
            'is_active'
        ];

        $callback = function() use ($headers) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // Write headers
            fputcsv($file, $headers);

            // Write multiple sample rows to demonstrate different options
            $sampleData1 = [
                'EMP001',                           // employee_number
                'John',                             // first_name
                'Doe',                              // last_name
                'john.doe@example.com',             // email
                '+254712345678',                    // phone
                '12345678',                         // national_id
                'A123456789B',                      // kra_pin
                '1990-01-15',                       // date_of_birth (YYYY-MM-DD)
                'Male',                             // gender (Male/Female)
                'Single',                           // marital_status (Single/Married/Divorced/Widowed)
                'Permanent',                        // employment_type (Permanent/Contract/Casual)
                '2023-01-01',                       // hire_date (YYYY-MM-DD)
                'IT Department',                    // department_name
                'Software Developer',               // position_title
                'Nairobi Office',                   // location_name
                'Jane Doe',                         // emergency_contact_name
                '+254712345679',                    // emergency_contact_phone
                'Mary Doe',                         // next_of_kin_name
                'Mother',                           // next_of_kin_relationship
                '+254712345680',                    // next_of_kin_phone
                'mary.doe@example.com',             // next_of_kin_email
                'true'                              // is_active (true/false)
            ];

            $sampleData2 = [
                'EMP002',
                'Jane',
                'Smith',
                'jane.smith@example.com',
                '+254723456789',
                '87654321',
                'B987654321C',
                '1985-06-20',
                'Female',                            // Example: Female
                'Married',                           // Example: Married
                'Contract',                          // Example: Contract
                '2022-03-15',
                'HR Department',
                'HR Manager',
                'Nairobi Office',
                'John Smith',
                '+254734567890',
                'Sarah Smith',
                'Sister',
                '+254745678901',
                'sarah.smith@example.com',
                'true'
            ];

            $sampleData3 = [
                'EMP003',
                'Michael',
                'Johnson',
                'michael.j@example.com',
                '+254756789012',
                '11223344',
                '',                                  // kra_pin optional
                '1995-12-10',
                'Male',
                'Divorced',                          // Example: Divorced
                'Casual',                            // Example: Casual
                '2024-01-10',
                'Operations',
                'Attendant',
                'Mombasa Office',
                'Lisa Johnson',
                '+254767890123',
                'Robert Johnson',
                'Father',
                '+254778901234',
                '',                                  // next_of_kin_email optional
                'false'                              // Example: inactive employee
            ];

            fputcsv($file, $sampleData1);
            fputcsv($file, $sampleData2);
            fputcsv($file, $sampleData3);

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="employee_import_template.csv"',
        ]);
    }
}
