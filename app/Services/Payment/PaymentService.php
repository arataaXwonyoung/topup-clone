<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;

interface PaymentService
{
    public function createCharge(Order $order, array $options = []): Payment;
    public function checkStatus(Payment $payment): array;
    public function handleWebhook(array $payload): bool;
    public function verifyWebhookSignature(string $signature, array $payload): bool;
    public function cancelPayment(Payment $payment): bool;
}