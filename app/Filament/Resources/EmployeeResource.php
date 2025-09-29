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

        // Expected headers mapping
        $expectedHeaders = [
            'employee_number' => 'Employee Number',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'national_id' => 'National ID',
            'kra_pin' => 'KRA PIN',
            'date_of_birth' => 'Date of Birth (YYYY-MM-DD)',
            'gender' => 'Gender (Male/Female)',
            'marital_status' => 'Marital Status (Single/Married/Divorced/Widowed)',
            'employment_type' => 'Employment Type (Permanent/Contract/Casual)',
            'hire_date' => 'Hire Date (YYYY-MM-DD)',
            'department_name' => 'Department Name',
            'position_title' => 'Position Title',
            'location_name' => 'Location Name',
            'emergency_contact_name' => 'Emergency Contact Name',
            'emergency_contact_phone' => 'Emergency Contact Phone',
            'next_of_kin_name' => 'Next of Kin Name',
            'next_of_kin_relationship' => 'Next of Kin Relationship',
            'next_of_kin_phone' => 'Next of Kin Phone',
            'next_of_kin_email' => 'Next of Kin Email',
            'is_active' => 'Is Active (true/false)'
        ];

        // Validate headers
        $headerMap = [];
        foreach ($expectedHeaders as $key => $label) {
            $found = false;
            foreach ($headers as $index => $header) {
                if (strtolower(trim($header)) === strtolower(trim($label))) {
                    $headerMap[$key] = $index;
                    $found = true;
                    break;
                }
            }
            if (!$found && in_array($key, ['employee_number', 'first_name', 'last_name', 'email'])) {
                throw new \Exception("Required column '{$label}' not found in CSV");
            }
        }

        DB::beginTransaction();

        try {
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

                    // Handle relationships
                    if (isset($employeeData['department_name']) && $employeeData['department_name']) {
                        $department = Department::where('name', $employeeData['department_name'])->first();
                        if (!$department) {
                            throw new \Exception("Department '{$employeeData['department_name']}' not found");
                        }
                        $employeeData['department_id'] = $department->id;
                        unset($employeeData['department_name']);
                    }

                    if (isset($employeeData['position_title']) && $employeeData['position_title']) {
                        $position = Position::where('title', $employeeData['position_title'])->first();
                        if (!$position) {
                            throw new \Exception("Position '{$employeeData['position_title']}' not found");
                        }
                        $employeeData['position_id'] = $position->id;
                        unset($employeeData['position_title']);
                    }

                    if (isset($employeeData['location_name']) && $employeeData['location_name']) {
                        $location = \App\Models\Location::where('name', $employeeData['location_name'])->first();
                        if (!$location) {
                            throw new \Exception("Location '{$employeeData['location_name']}' not found");
                        }
                        $employeeData['location_id'] = $location->id;
                        unset($employeeData['location_name']);
                    }

                    // Check for required fields
                    if (empty($employeeData['employee_number']) || empty($employeeData['first_name']) ||
                        empty($employeeData['last_name']) || empty($employeeData['email'])) {
                        throw new \Exception("Missing required fields");
                    }

                    // Check for duplicate employee number or email
                    if (Employee::where('employee_number', $employeeData['employee_number'])->exists()) {
                        throw new \Exception("Employee number '{$employeeData['employee_number']}' already exists");
                    }

                    if (Employee::where('email', $employeeData['email'])->exists()) {
                        throw new \Exception("Email '{$employeeData['email']}' already exists");
                    }

                    // Set default values
                    $employeeData['is_active'] = $employeeData['is_active'] ?? true;
                    $employeeData['password'] = bcrypt($employeeData['email']); // Default password is email

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

            return [
                'success' => $success,
                'errors' => $errors,
                'error_messages' => $errorMessages
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            throw $e;
        }
    }

    /**
     * Download CSV template for employee import
     */
    public static function downloadCsvTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Employee Number',
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'National ID',
            'KRA PIN',
            'Date of Birth (YYYY-MM-DD)',
            'Gender (Male/Female)',
            'Marital Status (Single/Married/Divorced/Widowed)',
            'Employment Type (Permanent/Contract/Casual)',
            'Hire Date (YYYY-MM-DD)',
            'Department Name',
            'Position Title',
            'Location Name',
            'Emergency Contact Name',
            'Emergency Contact Phone',
            'Next of Kin Name',
            'Next of Kin Relationship',
            'Next of Kin Phone',
            'Next of Kin Email',
            'Is Active (true/false)'
        ];

        $callback = function() use ($headers) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // Write headers
            fputcsv($file, $headers);

            // Write sample data
            $sampleData = [
                'EMP001',
                'John',
                'Doe',
                'john.doe@example.com',
                '+254712345678',
                '12345678',
                'A123456789B',
                '1990-01-15',
                'Male',
                'Single',
                'Permanent',
                '2023-01-01',
                'IT Department',
                'Software Developer',
                'Nairobi Office',
                'Jane Doe',
                '+254712345679',
                'Mary Doe',
                'Mother',
                '+254712345680',
                'mary.doe@example.com',
                'true'
            ];
            fputcsv($file, $sampleData);

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="employee_import_template.csv"',
        ]);
    }
}
