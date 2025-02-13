<?php

namespace App\Filament\Pages;
use App\Filament\Widgets\Chart1;
use App\Filament\Widgets\MonthlyCostBySiteChart;
use App\Filament\Widgets\MonthlyCostByWellChart;
use App\Filament\Widgets\MonthlyCostbyProduct;
use App\Filament\Widgets\BOWGBySiteOverTimeChart;
use App\Filament\Widgets\BWEBySiteOverTimeChart;
use App\Filament\Widgets\User;
use App\Filament\Widgets\WellUsage;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Facades\Filament;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

 class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersForm;

    public string $dashboardState = 'site';  // Default state for dashboard
    public $report_month; // Property to store the selected month

    // Method to render the form
    public function filtersForm(Form $form): Form
    {
        $tenant = Filament::getTenant();

        return $form->schema([
            Grid::make(3)
                ->schema([
                    Flatpickr::make('report_month')
                        ->label('Select Month')
                        ->monthSelect()
                        ->animate()
                        ->clickOpens(true)
                        ->allowInvalidPreload()
                        ->reactive() // Make the date input reactive
                        ->afterStateUpdated(function ($state) {
                            // Log the updated report month
                            $this->report_month = $state;
                            $this->dispatch('report_month_updated', $state);
                        }),

//                    ToggleButtons::make('Dashboard')
//                        ->label('Switch Dashboard')
//                        ->default('site')  // Ensure the state is correctly passed to the toggle
//                        ->reactive()  // Make it reactive
//                        ->inline()
//                        ->options([
//                            'site' => 'Site Overview',
//                            'company' => 'Company Overview',
//                        ])
//                        ->icons([
//                            'site' => 'heroicon-o-pencil',
//                            'company' => 'heroicon-o-clock',
//                        ])
//                        ->afterStateUpdated(function ($state) {
//                            $this->dashboardState = $state;
//                        }),

                    Select::make('site_id')
                        ->label('Filter by Site')
                        ->searchable()
                        ->multiple()
                        ->preload()
                        ->options(function () use ($tenant) {
                            return \App\Models\Site::where('company_id', $tenant->id)
                                ->pluck('location', 'id')
                                ->toArray();
                        })

                ]),
        ]);
    }


    // Method to get widgets based on the current state

     protected function getHeaderWidgets(): array
     {
         return [
         ];
     }

     public function getWidgets(): array
     {
         // Dynamically load widgets based on the current dashboard state
//        return match ($this->dashboardState) {
//            'company' => [
//                User::class,
//                Chart1::class,
//                MonthlyCostBySiteChart::class,
//                WellUsage::class,
//            ],
//            'site' => [
//                MonthlyCostByWellChart::class,
//                MonthlyCostbyProduct::class,
//                BOWGBySiteOverTimeChart::class,
//                BWEBySiteOverTimeChart::class,
//            ]
//        };

         return [
             MonthlyCostByWellChart::class,
             MonthlyCostbyProduct::class,
             BOWGBySiteOverTimeChart::class,
             BWEBySiteOverTimeChart::class,
         ];
     }

    // This method will mount the component
    public function mount(): void
    {
        $this->report_month = now()->startOfMonth()->format('Y-m');
    }

    // // Livewire hook to hydrate the component
    // public function hydrate(): void
    // {
    //     $this->dashboardState ??= 'site';
    // }

}
