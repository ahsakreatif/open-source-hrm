<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceRecapResource\Pages;
use App\Filament\Resources\AttendanceRecapResource\RelationManagers;
use App\Models\AttendanceRecap;
use App\Models\Employee;
use App\Services\AttendanceRecapService;
use App\Models\Payroll;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class AttendanceRecapResource extends Resource
{
    protected static ?string $model = AttendanceRecap::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'HR Management';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('employee_id')
                    ->label('Employee')
                    ->options(function () {
                        return Employee::where('is_active', true)->pluck('first_name', 'id');
                    })
                    ->searchable()
                    ->required(),
                TextInput::make('year')
                    ->label('Year')
                    ->numeric()
                    ->default(date('Y'))
                    ->required(),
                TextInput::make('month')
                    ->label('Month')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(12)
                    ->default(date('n'))
                    ->required(),
                TextInput::make('total_days_present')
                    ->label('Days Present')
                    ->numeric()
                    ->disabled(),
                TextInput::make('total_hours_worked')
                    ->label('Hours Worked')
                    ->numeric()
                    ->disabled(),
                TextInput::make('overtime_hours')
                    ->label('Overtime Hours')
                    ->numeric()
                    ->disabled(),
                TextInput::make('total_days_leave')
                    ->label('Leave Days')
                    ->numeric()
                    ->disabled(),
                TextInput::make('attendance_rate')
                    ->label('Attendance Rate (%)')
                    ->numeric()
                    ->disabled(),
                TextInput::make('late_minutes')
                    ->label('Late Minutes')
                    ->numeric()
                    ->disabled(),
                TextInput::make('early_departure_minutes')
                    ->label('Early Departure Minutes')
                    ->numeric()
                    ->disabled(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'error' => 'Error',
                    ])
                    ->default('pending'),
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
                    ->searchable(),
                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('period_name')
                    ->label('Period')
                    ->sortable(['year', 'month']),
                TextColumn::make('total_days_present')
                    ->label('Days Present')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('total_hours_worked')
                    ->label('Hours Worked')
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => number_format($state, 2)),
                TextColumn::make('overtime_hours')
                    ->label('Overtime')
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->color('warning'),
                TextColumn::make('total_days_leave')
                    ->label('Leave Days')
                    ->sortable()
                    ->alignCenter()
                    ->color('info'),
                TextColumn::make('attendance_rate')
                    ->label('Attendance Rate')
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => number_format($state, 1) . '%')
                    ->color(fn ($state) => $state >= 90 ? 'success' : ($state >= 80 ? 'warning' : 'danger')),
                TextColumn::make('late_minutes')
                    ->label('Late (min)')
                    ->sortable()
                    ->alignCenter()
                    ->color('danger'),
                TextColumn::make('early_departure_minutes')
                    ->label('Early (min)')
                    ->sortable()
                    ->alignCenter()
                    ->color('danger'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'error' => 'danger',
                        default => 'secondary',
                    })
                    ->label('Status'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'error' => 'Error',
                    ])
                    ->label('Status'),
                Tables\Filters\Filter::make('employee')
                    ->form([
                        Select::make('employee_id')
                            ->label('Employee')
                            ->options(function () {
                                return Employee::where('is_active', true)->pluck('first_name', 'id');
                            })
                            ->searchable()
                            ->placeholder('Select Employee'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['employee_id'],
                                fn (Builder $query, $employeeId): Builder => $query->where('employee_id', $employeeId),
                            );
                    }),
                Tables\Filters\Filter::make('month_filter')
                    ->form([
                        TextInput::make('year')
                            ->label('Year')
                            ->numeric()
                            ->default(date('Y')),
                        TextInput::make('month')
                            ->label('Month')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12)
                            ->default(date('n')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['year'],
                                fn (Builder $query, $year): Builder => $query->where('year', $year),
                            )
                            ->when(
                                $data['month'],
                                fn (Builder $query, $month): Builder => $query->where('month', $month),
                            );
                    })
                    ->label('Filter by Month'),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('calculate_payroll')
                        ->label('Calculate Payroll')
                        ->icon('heroicon-o-calculator')
                        ->color('success')
                        ->action(function (AttendanceRecap $record) {
                            try {
                                $payroll = Payroll::calculateFromAttendanceRecap(
                                    $record->employee_id,
                                    $record->year,
                                    $record->month
                                );

                                Notification::make()
                                    ->title('Payroll Calculated Successfully')
                                    ->body("Payroll generated for {$record->employee->full_name} - {$record->period_name}")
                                    ->success()
                                    ->send();

                                return redirect()->route('filament.admin.resources.payrolls.index');
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Payroll Calculation Failed')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Action::make('recalculate')
                        ->label('Recalculate')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->action(function (AttendanceRecap $record) {
                            try {
                                $newData = AttendanceRecap::calculateRecap($record->employee_id, $record->year, $record->month);
                                $record->update($newData);

                                Notification::make()
                                    ->title('Recap Recalculated')
                                    ->body("Attendance recap updated for {$record->employee->full_name}")
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Recalculation Failed')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('generate_monthly_recap')
                    ->label('Generate Monthly Recap')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->form([
                        TextInput::make('year')
                            ->label('Year')
                            ->numeric()
                            ->default(date('Y'))
                            ->required(),
                        TextInput::make('month')
                            ->label('Month')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12)
                            ->default(date('n'))
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        try {
                            $results = AttendanceRecapService::generateMonthlyRecap($data['year'], $data['month']);

                            $successCount = collect($results)->where('status', 'success')->count();
                            $errorCount = collect($results)->where('status', 'error')->count();

                            if ($errorCount > 0) {
                                Notification::make()
                                    ->title('Monthly Recap Generated with Errors')
                                    ->body("Generated {$successCount} successful recaps, {$errorCount} failed")
                                    ->warning()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Monthly Recap Generated Successfully')
                                    ->body("Generated {$successCount} attendance recaps")
                                    ->success()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Generation Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('recalculate_all')
                    ->label('Recalculate All')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        TextInput::make('year')
                            ->label('Year')
                            ->numeric()
                            ->default(date('Y'))
                            ->required(),
                        TextInput::make('month')
                            ->label('Month')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12)
                            ->default(date('n'))
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        try {
                            $count = AttendanceRecapService::recalculateMonthlyRecaps($data['year'], $data['month']);

                            Notification::make()
                                ->title('Recaps Recalculated')
                                ->body("Recalculated {$count} attendance recaps")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Recalculation Failed')
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
            'index' => Pages\ListAttendanceRecaps::route('/'),
            'create' => Pages\CreateAttendanceRecap::route('/create'),
            'edit' => Pages\EditAttendanceRecap::route('/{record}/edit'),
        ];
    }
}
