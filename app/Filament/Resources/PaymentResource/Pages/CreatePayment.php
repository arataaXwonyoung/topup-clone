<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['payment_id'] = 'PAY-' . strtoupper(uniqid());
        $data['net_amount'] = $data['amount'] - ($data['fee'] ?? 0);
        
        return $data;
    }
}