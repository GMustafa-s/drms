<?php

namespace App\Filament\Resources\FeMnCountResource\Pages;

use App\Filament\Resources\FeMnCountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFeMnCount extends EditRecord
{
    protected static string $resource = FeMnCountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ViewAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}