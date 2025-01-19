<?php

namespace App\Filament\Resources\FeMnCountResource\Pages;

use App\Filament\Resources\FeMnCountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFeMnCount extends CreateRecord
{
    protected static string $resource = FeMnCountResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}