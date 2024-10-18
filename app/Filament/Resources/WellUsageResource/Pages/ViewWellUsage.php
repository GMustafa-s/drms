<?php

namespace App\Filament\Resources\WellUsageResource\Pages;

use App\Filament\Resources\WellUsageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWellUsage extends ViewRecord

{
    protected static string $resource = WellUsageResource::class;
    protected static ?string $title = 'Well Usage';

}
