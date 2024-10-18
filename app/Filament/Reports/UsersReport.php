<?php

namespace App\Filament\Reports;

use EightyNine\Reports\Components\Text;
use EightyNine\Reports\Enums\FontSize;
use EightyNine\Reports\Report;
use App\Models\WellUsage;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Body\TextColumn;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use EightyNine\Reports\Components\VerticalSpace;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Carbon\Carbon;

class UsersReport extends Report
{
    public ?string $heading = "Well Usage Report";
    public ?string $subHeading = "Detailed report of well usage data";

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
                                Text::make("This report provides detailed information on well usage, including product details, usage metrics, and associated costs.")
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
                        Text::make('Product Details Table')->title()->fontSize(FontSize::Lg),
                        Body\Table::make()
                            ->columns([
                                TextColumn::make('id')->label('Sr. No'),
                                TextColumn::make('well.name')->label('Well Name'),
                                TextColumn::make('product_name')->label('Product Name'),
                                TextColumn::make('product_type')->label('Product Type'),
                                TextColumn::make('injection_location')->label('Injection Location'),
                            ])
                            ->data(
                                fn(?array $filters) => WellUsage::query()
                                    ->when($filters['from'] ?? null, fn($query, $from) => $query->whereDate('created_at', '>=', Carbon::parse($from)))
                                    ->when($filters['until'] ?? null, fn($query, $until) => $query->whereDate('created_at', '<=', Carbon::parse($until)))
                                    ->when($filters['product_type'] ?? null, fn($query, $productType) => $query->where('product_type', $productType))
                                    ->get()
                            ),
                        VerticalSpace::make(),
                        Text::make('Usage Metrics Table')->title()->primary(),
                        Body\Table::make()
                            ->columns([
                                TextColumn::make('id')->label('Sr. No'),
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
                                fn(?array $filters) => WellUsage::query()
                                    ->when($filters['from'] ?? null, fn($query, $from) => $query->whereDate('created_at', '>=', Carbon::parse($from)))
                                    ->when($filters['until'] ?? null, fn($query, $until) => $query->whereDate('created_at', '<=', Carbon::parse($until)))
                                    ->when($filters['product_type'] ?? null, fn($query, $productType) => $query->where('product_type', $productType))
                                    ->get()
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
                DatePicker::make('from')->label('From Date'),
                DatePicker::make('until')->label('Until Date'),
                Select::make('product_type')->label('Product Type')
                    ->options([
                        'Chemical A' => 'Chemical A',
                        'Chemical B' => 'Chemical B',
                        'Chemical C' => 'Chemical C',
                    ]),
            ]);
    }
}
