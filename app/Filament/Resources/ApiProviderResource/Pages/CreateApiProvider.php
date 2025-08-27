<?php

namespace App\Filament\Resources\ApiProviderResource\Pages;

use App\Filament\Resources\ApiProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateApiProvider extends CreateRecord
{
    protected static string $resource = ApiProviderResource::class;
}