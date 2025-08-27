<?php

namespace App\Filament\Resources\ApiProviderResource\Pages;

use App\Filament\Resources\ApiProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewApiProvider extends ViewRecord
{
    protected static string $resource = ApiProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}