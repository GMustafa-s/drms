<?php

namespace App\Filament\Resources\SiteResource\Pages;

use App\Filament\Resources\SiteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;use Howdu\FilamentRecordSwitcher\Filament\Concerns\HasRecordSwitcher;


class EditSite extends EditRecord
{
    use HasRecordSwitcher;
    protected static string $resource = SiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
