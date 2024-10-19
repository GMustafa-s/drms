<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WellUsageResource\Pages;
use App\Filament\Resources\WellUsageResource\RelationManagers;
use App\Models\Well;
use App\Models\WellUsage;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use PHPUnit\Metadata\Group;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class WellUsageResource extends Resource
{
    protected static ?string $model = WellUsage::class;
    protected static ?string $navigationGroup = 'Well Management';
    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';

    public static function form(Form $form,): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()->schema([
                    Section::make('Enter Usage Details')->schema([

                            Forms\Components\Select::make('well_id')
                                ->preload()
                                ->label('Select Well')
                                ->relationship('well', 'lease')
                                ->reactive()
                                ->afterStateUpdated(fn (callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                            ->searchable(),
                        Section::make('Production (Daily Average)')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('production_location')
                                            ->required()
                                            ->label('Production Location'),
                                        Forms\Components\TextInput::make('bopd')
                                            ->numeric()->reactive()->afterStateUpdated(fn (callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                            ->required()
                                            ->label('BOPD'),
                                        Forms\Components\TextInput::make('mmcf')
                                            ->numeric()->reactive()->afterStateUpdated(fn (callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                            ->required()
                                            ->label('MMCF'),
                                        Forms\Components\TextInput::make('bwpd')
                                            ->numeric()->reactive()->afterStateUpdated(fn (callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                            ->required()
                                            ->label('BWPD'),
                                    ]),
                            ]),
                    ])
                ])->columnSpanFull(),
                Forms\Components\Group::make()
                    ->schema([
                        Section::make('Chemical Injection Points
')->schema([

                            Section::make('Details')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('product_type')
                                                ->required()
                                                ->label('Product Type'),
                                            Forms\Components\TextInput::make('product_name')
                                                ->required()
                                                ->label('Product Name'),
                                            Forms\Components\TextInput::make('injection_location')
                                                ->required()
                                                ->label('Injection Location'),
                                        ]),
                                ]),
                            Section::make('Data (Continuous Applications)')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('ppm')
                                                ->numeric()->reactive()->afterStateUpdated(fn (callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                                ->required()
                                                ->label('Parts Per Million (PPM)'),
                                            Forms\Components\TextInput::make('quarts_per_day')
                                                ->numeric()->reactive()->afterStateUpdated(fn (callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                                ->required()
                                                ->label('Quarts Per Day'),
                                            Forms\Components\TextInput::make('gallons_per_day')
                                                ->numeric()->reactive()->afterStateUpdated(fn (callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                                ->required()
                                                ->label('Gallons Per Day'),
                                            Forms\Components\TextInput::make('gallons_per_month')
                                                ->numeric()->reactive()->afterStateUpdated(fn (callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                                ->required()
                                                ->label('Gallons Per Month'),
                                        ]),
                                ]),
                        ]),

                    ]),
                Forms\Components\Group::make()->schema([
                    Section::make('Usage For Month')->schema([
                        Section::make('Details')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('usage_location')
                                            ->required()
                                            ->label('Usage Location'),
                                        Forms\Components\TextInput::make('program')
                                            ->required()
                                            ->label('Program'),
                                        Forms\Components\TextInput::make('deliveries_gallons')
                                            ->numeric()->reactive()->afterStateUpdated(fn (callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                            ->required()
                                            ->label('Deliveries (Gallons)'),
                                    ]),
                            ]),
                        Section::make('Data (Cost Based On Usage And Deliveries)')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('ppg')
                                            ->numeric()->reactive()->afterStateUpdated(fn (callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                            ->required()
                                            ->label('Price Per Gallon'),
                                        Forms\Components\TextInput::make('monthly_cost')
                                            ->numeric()->reactive()->afterStateUpdated(fn (callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                            ->required()
                                            ->label('Monthly Cost'),
                                        Forms\Components\TextInput::make('bwe')
                                            ->numeric()->reactive()->afterStateUpdated(fn (callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                            ->required()
                                            ->label('BWE'),
                                        Forms\Components\TextInput::make('bowg')
                                            ->numeric()->reactive()->afterStateUpdated(fn (callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                            ->required()
                                            ->label('BOWG'),
                                    ]),
                            ]),
                    ])
                ]),
            ]);

//        return $form
//            ->schema([
//
//
//                Forms\Components\Group::make()->schema([
//                    Section::make('Data')->schema([
//                        Grid::make(3)->schema([
//
//                            TextInput::make('ppm')
//                                ->label('PPM')
//                                ->numeric()
//                                ->reactive() // React to changes in PPM
//                                ->afterStateUpdated(fn (callable $get, callable $set) => static::calculateAllFields($get, $set)),
//                            Forms\Components\TextInput::make('quarts_per_day')
//                                ->label('Quarts per Day')
//                                ->reactive()
//                                ->afterStateUpdated(fn (callable $get, callable $set) => static::calculateAllFields($get, $set))
//                                ->required()
//                                ->numeric()
//                                ->required(),
//                            Forms\Components\TextInput::make('gallons_per_day')
//                                ->label('Gallons per Day')
//                                ->numeric()
//                                ->reactive()
//                                ->afterStateUpdated(fn (callable $get, callable $set) => static::calculateAllFields($get, $set))
//                                ->required()
//                                ->required(),
//                            Forms\Components\TextInput::make('gallons_per_month')
//                                ->label('Gallons per Month')
//                                ->reactive()
//                                ->afterStateUpdated(fn (callable $get, callable $set) => static::calculateAllFields($get, $set))
//                                ->required()
//                                ->required()
//                                ->numeric(),
//                            Forms\Components\TextInput::make('program')
//                                ->label('Program')
//                                ->required()
//                                ->maxLength(255),
//                            Forms\Components\TextInput::make('delivery_per_gallon')
//                                ->label('Delivery per Gallon')
//                                ->required()
//                                ->numeric()
//                                ->reactive()
//                                ->afterStateUpdated(fn (callable $get, callable $set) => static::calculateAllFields($get, $set))
//                                ->required(),
//                            Forms\Components\TextInput::make('ppg')
//                                ->label('PPG')
//                                ->numeric()
//                                ->reactive()
//                                ->afterStateUpdated(fn (callable $get, callable $set) => static::calculateAllFields($get, $set))
//                                ->required(),
//                            Forms\Components\TextInput::make('monthly_cost')
//                                ->label('Monthly cost')
//                                ->numeric()
//                                ->required()->reactive()
//                                ->afterStateUpdated(fn (callable $get, callable $set) => static::calculateAllFields($get, $set))
//                                ->required(),
//                            Forms\Components\TextInput::make('bwe')
//                                ->label('BWE')
//                                ->numeric()
//                                ->reactive()
//                                ->afterStateUpdated(fn (callable $get, callable $set) => static::calculateAllFields($get, $set))
//                                ->required(),
//                            Forms\Components\TextInput::make('bwpd')
//                                ->label('bwpd')
//                                ->numeric()
//                                ->reactive() // React to changes in bwpd
//                                ->afterStateUpdated(fn (callable $get, callable $set) => static::calculateAllFields($get, $set))
//                                ->required(),
//                            Forms\Components\TextInput::make('bopd')
//                                ->label('Bopd')
//                                ->numeric()
//                                ->required()->reactive()
//                                ->afterStateUpdated(fn (callable $get, callable $set) => static::calculateAllFields($get, $set))
//                                ->required(),
//                        ])
//
//                    ])
//                ]),
//                Forms\Components\Group::make()->schema([
//                    Section::make('Details')->schema([
//                        Grid::make(2)->schema([
//
//                            Forms\Components\Select::make('well_id')
//                                ->preload()
//                                ->relationship('well', 'lease')
//                                ->reactive()
//                                ->afterStateUpdated(fn (callable $get, callable $set) => static::populatePpmFromWell($get, $set))
//                            ->searchable(),
//                            Forms\Components\TextInput::make('product_name')
//                                ->datalist([
//                                    'BWM',
//                                    'Ford',
//                                    'Mercedes-Benz',
//                                    'Porsche',
//                                    'Toyota',
//                                    'Tesla',
//                                    'Volkswagen',
//                                ])
//                                ->required()
//                                ->maxLength(255),
//                            Forms\Components\TextInput::make('product_type')
//                                ->required()
//                                ->maxLength(255),
//                            Forms\Components\TextInput::make('injection_location')
//                                ->required()
//                                ->maxLength(255),
//
//                            Forms\Components\Toggle::make('is_published')
//                                ->required(),
//                        ])
//
//                    ])
//                ]),
//            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                ExportAction::make()   // ...
            ])
            ->columns([

                Tables\Columns\TextColumn::make('well.lease')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Month')
                    ->dateTime(' M, y ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('injection_location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ppm')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quarts_per_day')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gallons_per_day')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gallons_per_month')
                    ->searchable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('program')
                    ->searchable(),
                Tables\Columns\TextColumn::make('delivery_per_gallon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ppg')
                    ->searchable(),
                Tables\Columns\TextColumn::make('monthly_cost')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bwe')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bwpd')
                    ->searchable(),
                Tables\Columns\TextColumn::make('production_location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bopd')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Month')
                    ->dateTime(' M, y ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\EditAction::make(),

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
            'index' => Pages\ListWellUsages::route('/'),
//            'view' => Pages\ViewWellUsage::route('/{record}'),
            'create' => Pages\CreateWellUsage::route('/create'),
            'edit' => Pages\EditWellUsage::route('/{record}/edit'),
        ];
    }

    // Main function to calculate all fields
    // Main function to calculate all fields
    public static function calculateAllFields(callable $get, callable $set)
    {
        // Get original values as strings to use with BCMath for high precision calculations
        $ppm = $get('ppm');
        $bwpd = $get('bwpd');

        // Ensure values are valid numbers before proceeding with BCMath
        if (is_numeric($ppm) && is_numeric($bwpd)) {
            $ppm = (string) $ppm;
            $bwpd = (string) $bwpd;

            // Step 1: Calculate 'Quarts per Day' with BCMath for precision
            $quartsPerDay = bcdiv(bcmul($ppm, $bwpd, 10), '6000', 10);

            // Round 'Quarts per Day' to 1 decimal place for display
            $quartsPerDayRounded = self::roundToDecimal($quartsPerDay, 1);
            $set('quarts_per_day', $quartsPerDayRounded);

            // Step 2: Calculate 'Gallons per Day' with full precision using BCMath
            $gallonsPerDay = bcdiv($quartsPerDay, '4', 10);

            // Round 'Gallons per Day' to 1 decimal place for display
            $gallonsPerDayRounded = self::roundToDecimal($gallonsPerDay, 1);
            $set('gallons_per_day', $gallonsPerDayRounded);

            // Step 3: Calculate 'Gallons per Month' using BCMath with full precision
            $gallonsPerMonth = bcmul($gallonsPerDay, '30.3', 10);

            // Round 'Gallons per Month' to 1 decimal place for display
            $gallonsPerMonthRounded = self::roundToDecimal($gallonsPerMonth, 1);
            $set('gallons_per_month', $gallonsPerMonthRounded);
        } else {
            // Set default values if inputs are missing or invalid
            $set('quarts_per_day', 0);
            $set('gallons_per_day', 0);
            $set('gallons_per_month', 0);
        }
    }

    // Helper function to round a value to a specified number of decimal places
    private static function roundToDecimal($value, $precision = 1)
    {
        return round((float) $value, $precision);
    }

    protected static function populatePpmFromWell(callable $get, callable $set)
    {
        $well = Well::find($get('well_id'));

        if ($well) {
            // Set the 'ppm' value from the related Well model's 'rate' field
            $set('ppm', $well->ppm);
            $set('injection_location', $well->injection_point);
        } else {
            $set('ppm', 0);
        }

        // Recalculate other fields if necessary
        static::calculateAllFields($get, $set);
    }
}
