<?php

namespace App\Filament\Resources\ApiProviderResource\Pages;

use App\Filament\Resources\ApiProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditApiProvider extends EditRecord
{
    protected static string $resource = ApiProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}