<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Filament\Resources\PayrollResource\RelationManagers;
use App\Models\Payroll;
// use Filament\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Employee;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\Action;

class PayrollResource extends Resource
{
    // TODO: Global search
    // TODO: Add icons
    protected static ?string $model = Payroll::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $activeNavigationIcon = 'heroicon-s-banknotes';

    protected static ?string $navigationGroup = 'HR Management';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Forms\Components\Select::make('employee_id')
                    ->options(function () {
                        return Employee::all()->pluck('full_name', 'id');
                    })
                    ->searchable(
                        [
                            'first_name',
                            'last_name',
                        ]
                    )
                    ->required()
                    ->label('Employee')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('attendance_recap_id', null);
                        }
                    }),
                Forms\Components\Select::make('attendance_recap_id')
                    ->label('Attendance Recap')
                    ->options(function (callable $get) {
                        $employeeId = $get('employee_id');
                        if (!$employeeId) {
                            return [];
                        }

                        return \App\Models\AttendanceRecap::where('employee_id', $employeeId)
                            ->orderBy('year', 'desc')
                            ->orderBy('month', 'desc')
                            ->get()
                            ->pluck('period_name', 'id');
                    })
                    ->searchable()
                    ->placeholder('Select Attendance Recap')
                    ->helperText('Select an attendance recap to auto-calculate payroll'),
                Forms\Components\DatePicker::make('pay_date')
                    ->label('Pay Date')
                    ->required(),
                Forms\Components\TextInput::make('period')
                    ->label('Period')
                    ->placeholder('e.g., 2025-01')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('gross_pay')
                    ->label('Gross Pay')
                    ->required()
                    ->numeric(),

                Forms\Components\TextInput::make('net_pay')
                    ->label('Net Pay')
                    ->required()
                    ->numeric(),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'calculated' => 'Calculated',
                    ])
                    ->default('pending'),
                KeyValue::make('deductions')
                    ->label('Deductions')
                    ->keyLabel('Type')

                    ->valueLabel('Amount'),

                KeyValue::make('allowances')
                    ->label('Allowances')
                    ->keyLabel('Type')

                    ->valueLabel('Amount'),
                KeyValue::make('bonuses')
                    ->label('Bonuses')
                    ->keyLabel('Type')

                    ->valueLabel('Amount'),
                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->nullable()
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.employee_number')
                    ->label('Employee No.')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->searchable(),


                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable([
                        'employees.first_name',
                        'employees.last_name',
                    ])
                    ->sortable(
                        [
                            'employees.first_name',
                            'employees.last_name',
                        ]
                    ),
                Tables\Columns\TextColumn::make('pay_date')
                    ->date()
                    ->label('Pay Date')
                    ->sortable(),
                Tables\Columns\TextColumn::make('period')
                    ->label('Period')
                    ->searchable()
                    ->limit(10)
                    ->sortable(),
                Tables\Columns\TextColumn::make('attendanceRecap.period_name')
                    ->label('Attendance Period')
                    ->searchable()
                    ->sortable()
                    ->placeholder('No recap linked'),
                Tables\Columns\TextColumn::make('gross_pay')
                    ->label('Gross Pay')
                    ->sortable()
                    ->money('IDR', true),
                Tables\Columns\TextColumn::make('net_pay')
                    ->label('Net Pay')
                    ->sortable()
                    ->money('IDR', true),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'calculated' => 'info',
                        default => 'secondary',
                    })
                    ->label('Status'),



            ])
            ->filters([
                //
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'calculated' => 'Calculated',
                    ])
                    ->label('Status'),
                Tables\Filters\Filter::make('employee')

                    ->form([
                        Forms\Components\Select::make('employee_id')
                            ->label('Employee')
                            ->options(function () {
                                return Employee::all()->pluck('full_name', 'id');
                            })
                            ->searchable()
                            ->required(),

                    ]),


            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Action::make('calculate_from_recap')
                        ->label('Calculate from Recap')
                        ->icon('heroicon-o-calculator')
                        ->color('success')
                        ->visible(fn ($record) => $record->attendance_recap_id)
                        ->action(function ($record) {
                            try {
                                $recap = $record->attendanceRecap;
                                $payroll = Payroll::calculateFromAttendanceRecap(
                                    $record->employee_id,
                                    $recap->year,
                                    $recap->month
                                );

                                \Filament\Notifications\Notification::make()
                                    ->title('Payroll Recalculated')
                                    ->body("Payroll updated using attendance recap data")
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Calculation Failed')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('generate_payroll_from_recaps')
                    ->label('Generate Payroll from Recaps')
                    ->icon('heroicon-o-calculator')
                    ->color('primary')
                    ->form([
                        Forms\Components\Select::make('employee_id')
                            ->label('Employee')
                            ->options(function () {
                                return Employee::where('is_active', true)->pluck('first_name', 'id');
                            })
                            ->searchable()
                            ->placeholder('Select Employee (optional)'),
                        Forms\Components\TextInput::make('year')
                            ->label('Year')
                            ->numeric()
                            ->default(date('Y'))
                            ->required(),
                        Forms\Components\TextInput::make('month')
                            ->label('Month')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12)
                            ->default(date('n'))
                            ->required(),
                        Forms\Components\TextInput::make('hourly_rate')
                            ->label('Hourly Rate (IDR)')
                            ->numeric()
                            ->default(1000)
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        try {
                            if (isset($data['employee_id'])) {
                                // Generate for specific employee
                                $payroll = Payroll::calculateFromAttendanceRecap(
                                    $data['employee_id'],
                                    $data['year'],
                                    $data['month'],
                                    $data['hourly_rate']
                                );

                                \Filament\Notifications\Notification::make()
                                    ->title('Payroll Generated')
                                    ->body("Payroll generated for selected employee")
                                    ->success()
                                    ->send();
                            } else {
                                // Generate for all employees
                                $employees = Employee::where('is_active', true)->get();
                                $count = 0;

                                foreach ($employees as $employee) {
                                    try {
                                        Payroll::calculateFromAttendanceRecap(
                                            $employee->id,
                                            $data['year'],
                                            $data['month'],
                                            $data['hourly_rate']
                                        );
                                        $count++;
                                    } catch (\Exception $e) {
                                        // Continue with other employees
                                    }
                                }

                                \Filament\Notifications\Notification::make()
                                    ->title('Payroll Generated')
                                    ->body("Generated {$count} payroll records")
                                    ->success()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Generation Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
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
            'index' => Pages\ListPayrolls::route('/'),
            // 'create' => Pages\CreatePayroll::route('/create'),
            // 'edit' => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }
}
