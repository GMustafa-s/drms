<?php

namespace App\Filament\Pages;
use App\Filament\Widgets\Chart1;
use App\Filament\Widgets\MonthlyCostBySiteChart;
use App\Filament\Widgets\MonthlyCostByWellChart;
use App\Filament\Widgets\User;
use App\Filament\Widgets\WellUsage;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Facades\Filament;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Illuminate\Support\Facades\Log; // Import the Log facade

 class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersForm;

    public string $dashboardState = 'site';  // Default state for dashboard

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
                        ->reactive() // Make the date input reactive
                        ->afterStateUpdated(function ($state) {
                            // Log the updated report month
                            Log::debug('Report Month updated to: ' . $state);
                        }),

                    ToggleButtons::make('Dashboard')
                        ->label('Switch Dashboard')
                        ->default('site')  // Ensure the state is correctly passed to the toggle
                        ->reactive()  // Make it reactive
                        ->inline()
                        ->options([
                            'site' => 'Site Overview',
                            'company' => 'Company Overview',
                        ])
                        ->icons([
                            'site' => 'heroicon-o-pencil',
                            'company' => 'heroicon-o-clock',
                        ])
                        ->afterStateUpdated(function ($state) {
                            $this->dashboardState = $state;  // Update the Livewire property
                            Log::debug('State updated to: ' . $this->dashboardState);  // Log state update for debugging
                            // You can also log the month if needed
//                            Log::debug('Report Month: ' . $this->getState('report_month'));
                        }),

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
                        ->visible($this->dashboardState === 'site'),  // Show based on Livewire state
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
        Log::debug('Current dashboard state: ' . $this->dashboardState); // Log state

        // Dynamically load widgets based on the current dashboard state
        return match ($this->dashboardState) {
            'company' => [
                User::class,
                Chart1::class,
                MonthlyCostBySiteChart::class,
                WellUsage::class,
                MonthlyCostBySiteChart::class

            ],
            'site' => [
//                MonthlyCostByWellChart::class,
//                McWell::class
            ]
        };
    }

    // This method will mount the component
    public function mount(): void
    {
        // Ensure initial state is set before rendering the page
        Log::debug('Dashboard Mounted with initial state: ' . $this->dashboardState);
    }

    // Livewire hook to hydrate the component
    public function hydrate(): void
    {
        // Ensure state is correctly reflected during the page hydration
        $this->dashboardState = $this->dashboardState ?? 'site';
        $this->report_month = $this->report_month ?? now()->startOfMonth();  // Default to current month
        Log::debug('Dashboard hydrated with state: ' . $this->dashboardState . ' and month: ' . $this->report_month);
    }

}
