<?php

namespace App\Filament\Resources\AreaResource\Pages;

use App\Filament\Resources\AreaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;use Howdu\FilamentRecordSwitcher\Filament\Concerns\HasRecordSwitcher;


class EditArea extends EditRecord
{
    use HasRecordSwitcher;
    protected static string $resource = AreaResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
