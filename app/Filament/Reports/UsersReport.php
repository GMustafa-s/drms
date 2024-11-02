<?php

namespace App\Filament\Reports;

use EightyNine\Reports\Components\Text;
use EightyNine\Reports\Enums\FontSize;
use EightyNine\Reports\Report;
use App\Models\Well;
use App\Models\WellUsage;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Body\TextColumn;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use EightyNine\Reports\Components\VerticalSpace;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Carbon\Carbon;

class UsersReport extends Report
{
    public ?string $heading = "Well Usage Report";
    public ?string $subHeading = "Detailed report of well and its usage data";

    public function header(Header $header): Header
    {
        return $header
            ->schema([
                Header\Layout\HeaderRow::make()
                    ->schema([
                        Header\Layout\HeaderColumn::make()
                            ->schema([
                                Text::make("Well Usage Report")
                                    ->title()
                                    ->primary(),
                                Text::make("This report provides detailed information about a selected well and its usage records.")
                                    ->subtitle(),
                            ]),
                        Header\Layout\HeaderColumn::make()
                            ->alignRight(),
                    ]),
            ]);
    }

    public function body(Body $body): Body
    {
        return $body
            ->schema([
                Body\Layout\BodyColumn::make()
                    ->schema([
                        // Well Details Section
                        Text::make('Well Details')->title()->fontSize(FontSize::Lg),
                        Body\Table::make()
                            ->columns([
                                TextColumn::make('id')->label('Well ID'),
                                TextColumn::make('lease')->label('Well Name'),
                                TextColumn::make('chemical')->label('chemical'),
                                TextColumn::make('chemical_type')->label('Chemical Type'),
                                TextColumn::make('rate')->label('PPM (rate)'),
                                TextColumn::make('based_on')->label('Based On'),
                                TextColumn::make('based_on')->label('Based On'),
                            ])
                            ->data(
                                fn(?array $filters) => $this->getWellDetails($filters['well_id'] ?? null)
                            ),
                        VerticalSpace::make(),
                        // Well Usage Data Section
                        Text::make('Well Usage Data')->title()->primary(),
                        Body\Table::make()
                            ->columns([
                                TextColumn::make('id')->label('Usage ID'),
                                TextColumn::make('product_name')->label('Product Name'),
                                TextColumn::make('product_type')->label('Product Type'),
                                TextColumn::make('ppm')->label('PPM'),
                                TextColumn::make('quarts_per_day')->label('Quarts/Day'),
                                TextColumn::make('gallons_per_day')->label('Gallons/Day'),
                                TextColumn::make('gallons_per_month')->label('Gallons/Month'),
                                TextColumn::make('location')->label('Location'),
                                TextColumn::make('program')->label('Program'),
                                TextColumn::make('monthly_cost')->label('Monthly Cost')->numeric(),
                                TextColumn::make('created_at')->label('Date Created')->date(),
                            ])
                            ->data(
                                fn(?array $filters) => $this->getWellUsages($filters['well_id'] ?? null)
                            ),
                    ])
            ]);
    }

    public function footer(Footer $footer): Footer
    {
        return $footer
            ->schema([
                Footer\Layout\FooterRow::make()
                    ->schema([
                        Footer\Layout\FooterColumn::make()
                            ->schema([
                                Text::make("Well Usage Report Footer")
                                    ->title()
                                    ->primary(),
                                Text::make("Generated on: " . now()->format('Y-m-d H:i:s')),
                            ])
                            ->alignRight(),
                    ]),
            ]);
    }

    public function filterForm(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('well_id')->label('Select Well')
                    ->options(Well::query()->get()->pluck('lease', 'id')) // Fetch well names and IDs
                    ->searchable() // Make it searchable for convenience
                    ->required(), // Make well selection mandatory
            ]);
    }

    // Function to get Well details based on selected Well ID
    private function getWellDetails(?int $wellId): \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
    {
        if ($wellId) {
            return Well::query()
                ->where('id', $wellId)
                ->get();
        }
        return collect(); // Return an empty collection if no well is selected
    }

    // Function to get Well Usage data based on selected Well ID
    private function getWellUsages(?int $wellId): \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
    {
        if ($wellId) {
            return WellUsage::query()
                ->with('well')
                ->where('well_id', $wellId)
                ->get();
        }
        return collect(); // Return an empty collection if no well is selected
    }
}
