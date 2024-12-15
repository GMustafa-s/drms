<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WellUsageResource\Pages;
use App\Models\InjectionLocation;
use App\Models\Site;
use App\Models\Well;
use App\Models\Product;
use App\Models\WellUsage;
use Carbon\Carbon;

use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;

use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Sum;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class WellUsageResource extends Resource
{
    protected static ?string $model = WellUsage::class;
    protected static ?string $navigationGroup = 'Well Management';
    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';

    public static function form(Form $form): Form
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
                                                ->options(Product::all()->pluck('name', 'id'))
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
        //            ]);333
    }

    public static function table(Table $table): Table
    {
        return $table

            ->headerActions([
                ExportAction::make()   // ...
            ])
            ->columns([
                TextColumn::make('well.lease')
                    ->label('Lease (Well)')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Month')
                    ->dateTime(' M, y ')
                    ->sortable(),

                BadgeColumn::make('monthly_cost')
                    ->label('Monthly Cost')
                    ->summarize([
                         Tables\Columns\Summarizers\Sum::make()->label("Total (sum)") ,
                        Range::make()->label("Range"),
                        Average::make()->label("average"),
                    ])
                    ->color('primary')
                    ->sortable(),

                // TextColumn::make('production_location')
                //     ->label('Production Location')
                //     ->sortable()
                //     ->searchable(),

                BadgeColumn::make('bopd')
                    ->summarize([
                         Tables\Columns\Summarizers\Sum::make()->label("Total (sum)") ,
                        Range::make()->label("Range"),
                        Average::make()->label("average"),
                    ])
                    ->label('BOPD')
                    ->color('warning')
                    ->sortable(),

                BadgeColumn::make('mmcf')
                    ->summarize([
                         Tables\Columns\Summarizers\Sum::make()->label("Total (sum)") ,
                        Range::make()->label("Range"),
                        Average::make()->label("average"),
                    ])
                    ->label('MMCF')
                    ->color('warning')
                    ->sortable(),

                BadgeColumn::make('bwpd')
                    ->summarize([
                         Tables\Columns\Summarizers\Sum::make()->label("Total (sum)") ,
                        Range::make()->label("Range"),
                        Average::make()->label("average"),
                    ])
                    ->label('BWPD')
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('product_type')
                    ->label('Product Type')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('product_name')
                    ->label('Product Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('injection_location')
                    ->label('Injection Location')
                    ->sortable()
                    ->searchable(),

                BadgeColumn::make('ppm')
                    ->label('Parts Per Million (PPM)')
                    ->summarize([
                         Tables\Columns\Summarizers\Sum::make()->label("Total (sum)") ,
                        Range::make()->label("Range"),
                        Average::make()->label("average"),
                    ])
                    ->color('danger')
                    ->sortable(),

                BadgeColumn::make('quarts_per_day')
                    ->summarize([
                         Tables\Columns\Summarizers\Sum::make()->label("Total (sum)") ,
                        Range::make()->label("Range"),
                        Average::make()->label("average"),
                    ])
                    ->label('Quarts Per Day')
                    ->color('danger')
                    ->sortable(),

                BadgeColumn::make('gallons_per_day')
                    ->summarize([
                         Tables\Columns\Summarizers\Sum::make()->label("Total (sum)") ,
                        Range::make()->label("Range"),
                        Average::make()->label("average"),
                    ])
                    ->label('Gallons Per Day')
                    ->color('danger')
                    ->sortable(),

                BadgeColumn::make('gallons_per_month')
                    ->summarize([
                         Tables\Columns\Summarizers\Sum::make()->label("Total (sum)") ,
                        Range::make()->label("Range"),
                        Average::make()->label("average"),
                    ])
                    ->label('Gallons Per Month')
                    ->color('danger')
                    ->sortable(),

                // TextColumn::make('usage_location')
                //     ->label('Usage Location')
                //     ->sortable()
                //     ->searchable(),

                // TextColumn::make('program')
                //     ->label('Program')
                //     ->sortable()
                //     ->searchable(),

                BadgeColumn::make('deliveries_gallons')
                    ->label('Deliveries (Gallons)')
                    ->color('success')
                    ->summarize([
                         Tables\Columns\Summarizers\Sum::make()->label("Total (sum)") ,
                        Range::make()->label("Range"),
                        Average::make()->label("average"),
                    ])
                    ->sortable(),

                BadgeColumn::make('ppg')
                    ->label('Price Per Gallon')
                    ->color('primary')
                    ->summarize([
                         Tables\Columns\Summarizers\Sum::make()->label("Total (sum)") ,
                        Range::make()->label("Range"),
                        Average::make()->label("average"),
                    ])
                    ->sortable(),



                BadgeColumn::make('bwe')
                    ->label('BWE')
                    ->color('primary')


                    ->summarize([
                         Tables\Columns\Summarizers\Sum::make()->label("Total (sum)") ,
                        Range::make()->label("Range"),
                        Average::make()->label("average"),
                    ])
                    ->sortable(),

                BadgeColumn::make('bowg')
                    ->label('BOWG')
                    ->color('primary')

                    ->summarize([
                         Tables\Columns\Summarizers\Sum::make()->label("Total (sum)"),
                        Range::make()->label("Range"),
                        Average::make()->label("average"),
                    ])
                    ->sortable(),
            ])
            ->filters([

                Tables\Filters\SelectFilter::make('well_id')
                    ->label('Select Well')
                    ->searchable()
                    ->preload()
                    ->relationship('well', 'lease'),
                SelectFilter::make('site_id') // The filter will use site_id
                ->label('Select Site')
                    ->searchable()
                    ->preload()
                    ->multiple() // Enable multiple selection if needed
                    ->options(function () {
                        // Get the current tenant using Filament's getTenant method
                        $tenant = Filament::getTenant();

                        // Fetch the sites related to the current tenant
                        return Site::where('company_id', $tenant->id) // Assuming 'company_id' is the field to match the tenant
                        ->pluck('location', 'id') // Pluck the location (or whatever field is needed)
                        ->toArray();
                    })
                    ->query(function ($query, $filter) {
                        $siteIds = $filter->getState(); // Get the selected site IDs

                        // If no sites are selected, return all records
                        if (empty($siteIds)) {
                            return $query; // Don't apply any filter, show all records
                        }

                        // Flatten the array in case it contains nested arrays
                        $siteIds = Arr::flatten($siteIds);

                        // Apply the filter to WellUsage, filtering by site_id
                        return $query->whereHas('well', function ($query) use ($siteIds) {
                            // Make sure that the Well belongs to the selected Site(s)
                            $query->whereIn('site_id', $siteIds); // Filter Well by site_id
                        });
                    }),
                Filter::make('created_at')
                    ->default(now())
                    ->form([
                        Flatpickr::make('month')->monthSelect()->animate(),
//                        DatePicker::make('created_from')
//                            ->native(false),
//                        DatePicker::make('created_until')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        // Get the selected month or use the current month as default
                        $selectedMonth = $data['month'] ?? now()->format('Y-m');

                        // Determine the start and end dates of the selected month
                        $startDate = Carbon::parse($selectedMonth)->startOfMonth();
                        $endDate = Carbon::parse($selectedMonth)->endOfMonth();

                        // Apply the date filter
                        return $query->whereBetween('created_at', [$startDate, $endDate]);
                    })
                ], layout: FiltersLayout::AboveContent)
            ->actions([
                //                Action::make('duplicate')
                //                    ->label('Duplicate')
                //                    ->icon('heroicon-o-document-duplicate') // Use an appropriate icon here
                //                    ->action(function ($record) {
                //                        // Duplicate the record
                //                        $duplicate = $record->replicate(); // Clone the record
                //                        $duplicate->save(); // Save the new cloned record
                //
                //                        // Use Filament's resource URL helper for redirection
                //                        return redirect(WellUsageResource::getUrl('edit', ['record' => $duplicate->id]));
                //                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function Infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Tabs::make('Well Usage') // Added Tabs to organize sections more effectively
                    ->columnSpanFull() // Ensures the tabs take up the full width
                    ->tabs([
                        Tab::make('Usage Details')
                            ->schema([
                                \Filament\Infolists\Components\Section::make('Usage Details')
                                    ->columns(2) // Set columns for better width utilization
                                    //                                ->color('primary') // Correct method to apply color to the section header
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('well.lease')
                                            ->label('Well Lease')
                                            ->badge()->color('success'),
                                        \Filament\Infolists\Components\TextEntry::make('created_at')
                                            ->label('Month')
                                            ->date('M, Y')
                                            ->badge()->color('info'),
                                        \Filament\Infolists\Components\Section::make('Production (Daily Average)')
                                            //                                        ->color('secondary') // Corrected color attribute
                                            ->schema([
                                                \Filament\Infolists\Components\Grid::make(2) // Changed to 2 columns for a better layout
                                                    ->schema([
                                                        // \Filament\Infolists\Components\TextEntry::make('production_location')
                                                        //     ->label('Production Location')
                                                        //     ->badge()->color('warning'),
                                                        \Filament\Infolists\Components\TextEntry::make('bopd')
                                                            ->label('BOPD')
                                                            ->badge()->color('warning'),
                                                        \Filament\Infolists\Components\TextEntry::make('mmcf')
                                                            ->label('MMCF')
                                                            ->badge()->color('warning'),
                                                        \Filament\Infolists\Components\TextEntry::make('bwpd')
                                                            ->label('BWPD')
                                                            ->badge()->color('warning'),
                                                    ]),
                                            ]),
                                    ]),
                            ]),
                        Tab::make('Chemical Injection Points')
                            ->schema([
                                \Filament\Infolists\Components\Section::make('Chemical Injection Points')
                                    ->columns(2) // Set columns to use full width effectively
                                    //                                ->badge() ->color('primary')
                                    ->schema([
                                        \Filament\Infolists\Components\Section::make('Details')
                                            //                                        ->badge() ->color('secondary')
                                            ->schema([
                                                \Filament\Infolists\Components\Grid::make(2)
                                                    ->schema([
                                                        \Filament\Infolists\Components\TextEntry::make('product_type')
                                                            ->label('Product Type')
                                                            ->badge()->color('info'),
                                                        \Filament\Infolists\Components\TextEntry::make('product_name')
                                                            ->label('Product Name')
                                                            ->badge()->color('info'),
                                                        \Filament\Infolists\Components\TextEntry::make('injection_location')
                                                            ->label('Injection Location')
                                                            ->badge()->color('info'),
                                                    ]),
                                            ]),
                                        \Filament\Infolists\Components\Section::make('Data (Continuous Applications)')
                                            //                                        ->badge() ->color('secondary')
                                            ->schema([
                                                \Filament\Infolists\Components\Grid::make(2)
                                                    ->schema([
                                                        \Filament\Infolists\Components\TextEntry::make('ppm')
                                                            ->label('Parts Per Million (PPM)')
                                                            ->badge()->color('danger'),
                                                        \Filament\Infolists\Components\TextEntry::make('quarts_per_day')
                                                            ->label('Quarts Per Day')
                                                            ->badge()->color('danger'),
                                                        \Filament\Infolists\Components\TextEntry::make('gallons_per_day')
                                                            ->label('Gallons Per Day')
                                                            ->badge()->color('danger'),
                                                        \Filament\Infolists\Components\TextEntry::make('gallons_per_month')
                                                            ->label('Gallons Per Month')
                                                            ->badge()->color('danger'),
                                                    ]),
                                            ]),
                                    ]),
                            ]),
                        Tab::make('Usage For Month')
                            ->schema([
                                \Filament\Infolists\Components\Section::make('Usage For Month')
                                    ->columns(2) // Set columns for full width usage
                                    //                                ->badge() ->color('primary')
                                    ->schema([
                                        \Filament\Infolists\Components\Section::make('Details')
                                            //                                        ->color('secondary')
                                            ->schema([
                                                \Filament\Infolists\Components\Grid::make(2)
                                                    ->schema([
                                                        // \Filament\Infolists\Components\TextEntry::make('usage_location')
                                                        //     ->label('Usage Location')
                                                        //     ->badge()->color('success'),
                                                        // \Filament\Infolists\Components\TextEntry::make('program')
                                                        //     ->label('Program')
                                                        //     ->badge()->color('success'),
                                                        \Filament\Infolists\Components\TextEntry::make('deliveries_gallons')
                                                            ->label('Deliveries (Gallons)')
                                                            ->badge()->color('success'),
                                                    ]),
                                            ]),
                                        \Filament\Infolists\Components\Section::make('Data (Cost Based On Usage And Deliveries)')
                                            //                                        ->badge() ->color('secondary')
                                            ->schema([
                                                \Filament\Infolists\Components\Grid::make(2)
                                                    ->schema([
                                                        \Filament\Infolists\Components\TextEntry::make('ppg')
                                                            ->label('Price Per Gallon')
                                                            ->badge()->color('primary'),
                                                        \Filament\Infolists\Components\TextEntry::make('monthly_cost')
                                                            ->label('Monthly Cost')
                                                            ->badge()->color('primary'),
                                                        \Filament\Infolists\Components\TextEntry::make('bwe')
                                                            ->label('BWE')
                                                            ->badge()->color('primary'),
                                                        \Filament\Infolists\Components\TextEntry::make('bowg')
                                                            ->label('BOWG')
                                                            ->badge()->color('primary'),
                                                    ]),
                                            ]),
                                    ]),
                            ]),
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
            'create' => Pages\CreateWellUsage::route('/create'),
            'view' => Pages\ViewWellUsage::route('/{record}'),
            'edit' => Pages\EditWellUsage::route('/{record}/edit'),
        ];
    }

    // Main function to calculate all fields
    // Main function to calculate all fields
    public static function calculateAllFields(callable $get, callable $set): void
    {
        // Existing calculations
        $ppm = $get('ppm');
        $bwpd = $get('bwpd');

        if (is_numeric($ppm) && is_numeric($bwpd)) {
            $ppm = (string)$ppm;
            $bwpd = (string)$bwpd;

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
            $deliveriesPerGallons = (string)$deliveriesPerGallons;
            $ppg = (string)$ppg;

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
            $monthlyCost = (string)$monthlyCost;
            $bwpd = (string)$bwpd;

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
            $monthlyCost = (string)$monthlyCost;
            $bopd = (string)$bopd;
            $bwpd = (string)$bwpd;
            $mmcf = (string)$mmcf;

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
        return round((float)$value, 1);
    }

    protected static function populatePpmFromWell(callable $get, callable $set): void
    {
        $well = \App\Models\Well::find($get('well_id'));

        if ($well) {
            // Set the 'ppm' value from the related Well model's 'rate' field
            $set('ppm', $well->rate);
        } else {
            $set('ppm', 0);
        }

        // Recalculate other fields if necessary
        static::calculateAllFields($get, $set);
    }

    public static function can($action, $record = null): bool
    {
        $user = auth()->user();

        // Super Admin has full access
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Panel User has restricted access (view & viewAny only)
        if ($user->hasRole('Panel User')) {
            if (in_array($action, ['view', 'viewAny'])) {
                return true;
            } else {
                return false;
            }
        }

        // Other users do not have any access
        return false;
    }
}
