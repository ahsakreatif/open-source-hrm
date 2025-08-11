<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LocationResource\Pages;
use App\Filament\Resources\LocationResource\RelationManagers;
use App\Models\Location;
use App\Forms\Components\MapPicker;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'Organization';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Information')
                    ->collapsible()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Location Name')
                            ->placeholder('Enter location name'),
                        TextInput::make('code')
                            ->maxLength(50)
                            ->label('Location Code')
                            ->placeholder('Enter location code')
                            ->unique(ignoreRecord: true),
                        Textarea::make('description')
                            ->maxLength(500)
                            ->label('Description')
                            ->placeholder('Enter location description'),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make('Address Information')
                    ->collapsible()
                    ->schema([
                        Textarea::make('address')
                            ->maxLength(500)
                            ->label('Street Address')
                            ->placeholder('Enter street address'),
                        TextInput::make('city')
                            ->maxLength(100)
                            ->label('City')
                            ->placeholder('Enter city'),
                        TextInput::make('state')
                            ->maxLength(100)
                            ->label('State/Province')
                            ->placeholder('Enter state or province'),
                        TextInput::make('country')
                            ->maxLength(100)
                            ->label('Country')
                            ->placeholder('Enter country')
                            ->default('Indonesia'),
                        TextInput::make('postal_code')
                            ->maxLength(20)
                            ->label('Postal Code')
                            ->placeholder('Enter postal code'),
                    ])
                    ->columns(2),

                Section::make('Contact Information')
                    ->collapsible()
                    ->schema([
                        TextInput::make('phone')
                            ->tel()
                            ->label('Phone Number')
                            ->placeholder('Enter phone number'),
                        TextInput::make('email')
                            ->email()
                            ->label('Email Address')
                            ->placeholder('Enter email address'),
                    ])
                    ->columns(2),

                Section::make('Location Coordinates')
                    ->collapsible()
                    ->schema([
                        MapPicker::make('map_picker')
                            ->label('Select Location on Map')
                            ->defaultLatitude(-6.175110)
                            ->defaultLongitude(106.865036)
                            ->zoom(10)
                            ->height('400px')
                            ->columnSpanFull(),
                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->placeholder('Enter latitude (e.g., -6.175110)')
                            ->reactive(),
                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->placeholder('Enter longitude (e.g., 106.865036)')
                            ->reactive(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                static::getEloquentQuery()->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Location Name'),
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->label('Code'),
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable()
                    ->label('City'),
                Tables\Columns\TextColumn::make('country')
                    ->searchable()
                    ->sortable()
                    ->label('Country'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),
                Tables\Columns\TextColumn::make('employees_count')
                    ->counts('employees')
                    ->label('Employees')
                    ->sortable(),
                Tables\Columns\TextColumn::make('departments_count')
                    ->counts('departments')
                    ->label('Departments')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
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
            'index' => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'edit' => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
