<?php

namespace App\Filament\Reports;

use EightyNine\Reports\Components\Text;
use EightyNine\Reports\Enums\FontSize;
use EightyNine\Reports\Report;
use App\Models\Site;
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
use Filament\Facades\Filament;

class SiteReport extends Report
{
    public ?string $heading = "Site Usage Report";
    public ?string $subHeading = "Detailed report of site, wells, and their usage data";

    protected ?string $currentCompanyName = null;
    protected ?string $currentSiteName = null;

    public function __construct()
    {
        // Retrieve the current tenant (company) name
        $tenant = Filament::getTenant();
        $this->currentCompanyName = $tenant->name ?? 'Unknown Company';
    }

    public function header(Header $header): Header
    {
        return $header
            ->schema([
                Header\Layout\HeaderRow::make()
                    ->schema([
                        Header\Layout\HeaderColumn::make()
                            ->schema([
                                Text::make("Site Usage Report for {$this->currentCompanyName}")
                                    ->title()
                                    ->primary(),
                                Text::make("This report provides detailed information about a selected site, its wells, and well usage records."),
                                Text::make("Current Site: {$this->currentSiteName}")
                                    ->subtitle()
//                                    ->italic(),
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
                        // Site Details Section
                        Text::make('Site Details')->title()->fontSize(FontSize::Lg),
                        Body\Table::make()
                            ->columns([
                                TextColumn::make('id')->label('Site ID'),
                                TextColumn::make('location')->label('Location'),
                                TextColumn::make('comments')->label('Comments'),
                            ])
                            ->data(
                                fn(?array $filters) => $this->getSiteDetails($filters['site_id'] ?? null)
                            ),
                        VerticalSpace::make(),

                        // Wells Section
                        Text::make('Wells at the Selected Site')->title()->primary(),
                        Body\Table::make()
                            ->columns([
                                TextColumn::make('id')->label('Well ID'),
                                TextColumn::make('lease')->label('Lease'),
                                TextColumn::make('chemical')->label('Chemical'),
                                TextColumn::make('chemical_type')->label('Chemical Type'),
                                TextColumn::make('rate')->label('Rate'),
                                TextColumn::make('injection_point')->label('Injection Point'),
                                TextColumn::make('comments')->label('Comments'),
                            ])
                            ->data(
                                fn(?array $filters) => $this->getSiteWells($filters['site_id'] ?? null)
                            ),
                        VerticalSpace::make(),

                        // Well Usages Section for Each Well
                        Text::make('Well Usages Data')->title()->primary(),
                        Body\Table::make()
                            ->columns([
                                TextColumn::make('well_id')->label('Well ID'),
                                TextColumn::make('product_name')->label('Product Name'),
                                TextColumn::make('product_type')->label('Product Type'),
                                TextColumn::make('ppm')->label('PPM'),
                                TextColumn::make('quarts_per_day')->label('Quarts/Day'),
                                TextColumn::make('gallons_per_day')->label('Gallons/Day'),
                                TextColumn::make('gallons_per_month')->label('Gallons/Month'),
                                TextColumn::make('program')->label('Program'),
                                TextColumn::make('monthly_cost')->label('Monthly Cost')->numeric(),
                                TextColumn::make('created_at')->label('Date Created')->date(),
                            ])
                            ->data(
                                fn(?array $filters) => $this->getSiteWellUsages($filters['site_id'] ?? null)
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
                                Text::make("Report generated for {$this->currentCompanyName}")
                                    ->title()
                                    ->primary(),
                                Text::make("Current Site: {$this->currentSiteName}")
//                                    ->italic()
                                ,
                                Text::make("Generated on: " . now()->format('Y-m-d H:i:s')),
                            ])
                            ->alignRight(),
                    ]),
            ]);
    }

    public function filterForm(Form $form): Form
    {
        $tenant = Filament::getTenant();

        return $form
            ->schema([
                Select::make('site_id')->label('Select Site')
                    ->options(Site::query()
                        ->where('company_id', $tenant->id) // Scope to current tenant's sites
                        ->get()
                        ->pluck('location', 'id')
                    )
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state) => $this->setCurrentSiteName($state)),
            ]);
    }

    // Function to set the current site name based on the selected site ID
    private function setCurrentSiteName(?int $siteId)
    {
        if ($siteId) {
            $site = Site::find($siteId);
            $this->currentSiteName = $site->location ?? 'Unknown Site';
        } else {
            $this->currentSiteName = 'No Site Selected';
        }
    }

    // Function to get Site details based on selected Site ID and tenant scope
    private function getSiteDetails(?int $siteId)
    {
        $tenant = Filament::getTenant();

        if ($siteId) {
            return Site::query()
                ->where('id', $siteId)
                ->where('company_id', $tenant->id) // Scope to current tenant
                ->get();
        }
        return collect(); // Return an empty collection if no site is selected
    }

    // Function to get Wells based on selected Site ID and tenant scope
    private function getSiteWells(?int $siteId)
    {
        $tenant = Filament::getTenant();

        if ($siteId) {
            return Well::query()
                ->where('site_id', $siteId)
                ->where('company_id', $tenant->id) // Scope to current tenant
                ->get();
        }
        return collect(); // Return an empty collection if no site is selected
    }

    // Function to get Well Usages for all wells at a selected Site and tenant scope
    private function getSiteWellUsages(?int $siteId)
    {
        $tenant = Filament::getTenant();

        if ($siteId) {
            return WellUsage::query()
                ->whereHas('well', fn($query) => $query
                    ->where('site_id', $siteId)
                    ->where('company_id', $tenant->id) // Scope to current tenant
                )
                ->get();
        }
        return collect(); // Return an empty collection if no site is selected
    }
}
