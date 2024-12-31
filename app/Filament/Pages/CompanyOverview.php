<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\Chart1;
use App\Filament\Widgets\MonthlyCostBySiteChart;
use App\Filament\Widgets\MonthlyCostByWellChart;
use App\Filament\Widgets\MonthlyCostbyProduct;
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
class CompanyOverview extends Page
{

    use HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.company-overview';








    protected function getHeaderWidgets(): array
    {    // Dynamically load widgets based on the current dashboard state
        return [
                User::class,
                Chart1::class,
                MonthlyCostBySiteChart::class,
                WellUsage::class,
        ];
    }
}
