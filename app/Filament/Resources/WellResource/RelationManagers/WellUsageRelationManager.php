<?php

namespace App\Filament\Resources\WellResource\RelationManagers;

use App\Models\Well;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class WellUsageRelationManager extends RelationManager
{
    protected static string $relationship = 'WellUsage';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()->schema([
                    Section::make('Enter Usage Details')->schema([


                        Forms\Components\Select::make('well_id')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->relationship('well', 'lease', fn($query) => $query->where('company_id', Filament::getTenant()->id))
                            ->getSearchResultsUsing(fn($query) => Well::where('company_id', Filament::getTenant()->id)
                                ->where('name', 'like', "%{$query}%")
                                ->pluck('name', 'id')
                                ->toArray())
                            ->getOptionLabelUsing(fn($value) => Well::where('company_id', Filament::getTenant()->id)
                                ->find($value)?->name)
                            ->reactive()
                            ->afterStateUpdated(fn(callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                            ->searchable(),

                        Forms\Components\DatePicker::make('created_at')->native(false),
                        Section::make('Production (Daily Average)')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        // Forms\Components\TextInput::make('production_location')
                                        //     ->required()
                                        //     ->label('Production Location'),
                                        Forms\Components\TextInput::make('bopd')
                                            ->numeric()->reactive()->afterStateUpdated(fn(callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                            ->required()
                                            ->label('BOPD'),
                                        Forms\Components\TextInput::make('mmcf')
                                            ->numeric()->reactive()->afterStateUpdated(fn(callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                            ->required()
                                            ->label('MMCF'),
                                        Forms\Components\TextInput::make('bwpd')
                                            ->numeric()->reactive()->afterStateUpdated(fn(callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                            ->required()
                                            ->label('BWPD'),
                                    ]),
                            ]),
                    ])
                ])->columnSpanFull(),
                Forms\Components\Group::make()
                    ->schema([
                        Section::make('Chemical Injection Points')
                            ->schema([
                                Section::make('Details')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('product_type')
                                                    ->required()
                                                    ->label('Product Type'),
                                                Forms\Components\Select::make('product_name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->label('Product')
                                                    ->options(Product::all()->pluck('name', 'name'))
                                                    ->reactive()
                                                    ->afterStateUpdated(function (callable $set, $state) {
                                                        if ($state) {
                                                            $product = Product::find($state);
                                                            $set('product_type', $product->productType->type ?? null);
                                                        }
                                                    })
                                                    ->required(),
                                                Forms\Components\Select::make('injection_location')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->options(InjectionLocation::all()->pluck('name', 'name'))
                                                    ->label('Injection Location'),
                                            ]),
                                    ]),
                                Section::make('Data (Continuous Applications)')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('ppm')
                                                    ->numeric()->reactive()->afterStateUpdated(fn(callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                                    ->required()
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->label('Parts Per Million (PPM)'),
                                                Forms\Components\TextInput::make('quarts_per_day')
                                                    ->numeric()->reactive()->afterStateUpdated(fn(callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                                    ->required()
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->label('Quarts Per Day'),
                                                Forms\Components\TextInput::make('gallons_per_day')
                                                    ->numeric()->reactive()->afterStateUpdated(fn(callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                                    ->required()
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->label('Gallons Per Day'),
                                                Forms\Components\TextInput::make('gallons_per_month')
                                                    ->numeric()->reactive()->afterStateUpdated(fn(callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                                    ->required()
                                                    ->disabled()
                                                    ->dehydrated()
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
                                        // Forms\Components\TextInput::make('usage_location')
                                        //     ->required()
                                        //     ->label('Usage Location'),
                                        // Forms\Components\TextInput::make('program')
                                        //     ->required()
                                        //     ->label('Program'),
                                        Forms\Components\TextInput::make('deliveries_gallons')
                                            ->numeric()->reactive()->afterStateUpdated(fn(callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                            ->required()
                                            ->label('Deliveries (Gallons)'),
                                    ]),
                            ]),
                        Section::make('Data (Cost Based On Usage And Deliveries)')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('ppg')
                                            ->numeric()->reactive()->afterStateUpdated(fn(callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                            ->required()
                                            ->label('Price Per Gallon'),
                                        Forms\Components\TextInput::make('monthly_cost')
                                            ->numeric()->reactive()->afterStateUpdated(fn(callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                            ->required()
                                            ->disabled()
                                            ->dehydrated()
                                            ->label('Monthly Cost'),
                                        Forms\Components\TextInput::make('bwe')
                                            ->numeric()->reactive()->afterStateUpdated(fn(callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                            ->required()
                                            ->disabled()
                                            ->dehydrated()
                                            ->label('BWE'),
                                        Forms\Components\TextInput::make('bowg')
                                            ->numeric()->reactive()->afterStateUpdated(fn(callable $get, callable $set) => static::populatePpmFromWell($get, $set))
                                            ->required()
                                            ->disabled()
                                            ->dehydrated()
                                            ->label('BOWG'),
                                    ]),
                            ]),
                    ])
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ppm')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Month')
                    ->dateTime(' M, y ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('monthly_cost') ->searchable(),
                Tables\Columns\TextColumn::make('product_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('injection_location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ppm')
                    ->searchable(),
//                Tables\Columns\TextColumn::make('quarts_per_day')
//                    ->searchable(),
                Tables\Columns\TextColumn::make('gallons_per_day')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gallons_per_month')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('location')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('program')
                //     ->searchable(),
                Tables\Columns\TextColumn::make('deliveries_gallons')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ppg')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bwe')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bowg')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('production_location')
                //     ->searchable(),
                Tables\Columns\TextColumn::make('bopd')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                // ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                ExportAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // Main function to calculate all fields
    // Main function to calculate all fields
    public static function calculateAllFields(callable $get, callable $set): void
    {
        // Existing calculations
        $ppm = $get('ppm');
        $bwpd = $get('bwpd');

        if (is_numeric($ppm) && is_numeric($bwpd)) {
            $ppm = (string) $ppm;
            $bwpd = (string) $bwpd;

            // Calculate 'Quarts per Day'
            $quartsPerDay = bcdiv(bcmul($ppm, $bwpd, 10), '6000', 10);
            $quartsPerDayRounded = self::roundToDecimal($quartsPerDay);
            $set('quarts_per_day', $quartsPerDayRounded);

            // Calculate 'Gallons per Day'
            $gallonsPerDay = bcdiv($quartsPerDay, '4', 10);
            $gallonsPerDayRounded = self::roundToDecimal($gallonsPerDay);
            $set('gallons_per_day', $gallonsPerDayRounded);

            // Calculate 'Gallons per Month'
            $gallonsPerMonth = bcmul($gallonsPerDay, '30.3', 10);
            $gallonsPerMonthRounded = self::roundToDecimal($gallonsPerMonth);
            $set('gallons_per_month', $gallonsPerMonthRounded);
        } else {
            $set('quarts_per_day', 0);
            $set('gallons_per_day', 0);
            $set('gallons_per_month', 0);
        }

        // New Monthly Cost Calculation
        $deliveriesPerGallons = $get('deliveries_gallons');
        $ppg = $get('ppg');

        if (is_numeric($deliveriesPerGallons) && is_numeric($ppg)) {
            $deliveriesPerGallons = (string) $deliveriesPerGallons;
            $ppg = (string) $ppg;

            // Calculate 'Monthly Cost'
            $monthlyCost = bcmul($deliveriesPerGallons, $ppg, 10);
            $monthlyCostRounded = self::roundToDecimal($monthlyCost);
            $set('monthly_cost', $monthlyCostRounded);
        } else {
            $set('monthly_cost', 0);
        }

        // BWE Calculation: monthly_cost / (BWPD * 30.3)
        $monthlyCost = $get('monthly_cost');
        $bwpd = $get('bwpd');

        if (is_numeric($monthlyCost) && is_numeric($bwpd) && $bwpd != 0) {
            // Use string format for high-precision calculations
            $monthlyCost = (string) $monthlyCost;
            $bwpd = (string) $bwpd;

            // Calculate the denominator (BWPD * 30.3) with high precision
            $denominator = bcmul($bwpd, '30.3', 10); // Keep full precision with scale 10

            // Double-check the denominator is not zero
            if (bccomp($denominator, '0', 10) != 0) {
                // Calculate 'BWE' with high precision
                $bwe = bcdiv($monthlyCost, $denominator, 10); // Divide with scale 10
                $bweRounded = round((float)$bwe, 2); // Round to 4 decimal places for clarity
                $set('bwe', $bweRounded);
            } else {
                $set('bwe', 0);
            }
        } else {
            $set('bwe', 0);
        }


        // BOWG Calculation: Q / ((W * 30.3) + (Y * 30.3) + ((X / 6) * 30.3))
        $monthlyCost = $get('monthly_cost');
        $bopd = $get('bopd');
        $bwpd = $get('bwpd');
        $mmcf = $get('mmcf');

        if (is_numeric($monthlyCost) && is_numeric($bopd) && is_numeric($bwpd) && is_numeric($mmcf)) {
            // Convert values to string for BCMath calculations
            $monthlyCost = (string) $monthlyCost;
            $bopd = (string) $bopd;
            $bwpd = (string) $bwpd;
            $mmcf = (string) $mmcf;

            // Calculate each part of the denominator
            $bopdPart = bcmul($bopd, '30.3', 10);  // W * 30.3
            $bwpdPart = bcmul($bwpd, '30.3', 10);  // Y * 30.3

            // Calculate (X / 6) * 30.3
            $mmcfDivided = bcdiv($mmcf, '6', 10);     // X / 6
            $mmcfPart = bcmul($mmcfDivided, '30.3', 10);

            // Calculate the full denominator: (W * 30.3) + (Y * 30.3) + ((X / 6) * 30.3)
            $denominator = bcadd($bopdPart, $bwpdPart, 10);
            $denominator = bcadd($denominator, $mmcfPart, 10);

            // Check if the denominator is not zero before division
            if (bccomp($denominator, '0', 10) != 0) {
                // Calculate 'BOWG' with high precision
                $bowg = bcdiv($monthlyCost, $denominator, 10); // Divide Q by the full denominator
                $bowgRounded = round((float)$bowg, 2); // Round to 4 decimal places for clarity
                $set('bowg', $bowgRounded);
            } else {
                $set('bowg', 0);
            }
        } else {
            $set('bowg', 0);
        }
    }

    // Helper function to round a value to a specified number of decimal places
    private static function roundToDecimal($value): float
    {
        return round((float) $value, 1);
    }

    protected function populatePpmFromWell(callable $get, callable $set): void
    {
        $parentWell = $this->getOwnerRecord(); // Get the parent record in the relation manager context.

        if ($parentWell) {
            // Set the 'ppm' value from the parent Well model's 'rate' field
            $set('ppm', $parentWell->rate);
        } else {
            $set('ppm', 0);
        }

        // Recalculate other fields if necessary
        $this->calculateAllFields($get, $set);
    }
}
