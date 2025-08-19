<?php

namespace App\Services\Payment\DTOs;

class CreateChargeRequest
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $invoiceNo,
        public readonly float $amount,
        public readonly string $customerName,
        public readonly string $customerEmail,
        public readonly string $customerPhone,
        public readonly string $method,
        public readonly ?string $channel = null,
        public readonly ?string $description = null,
        public readonly array $items = [],
        public readonly array $metadata = [],
        public readonly ?int $expiryMinutes = 180,
    ) {}

    public static function fromOrder(\App\Models\Order $order, string $method, ?string $channel = null): self
    {
        return new self(
            orderId: (string) $order->id,
            invoiceNo: $order->invoice_no,
            amount: $order->total,
            customerName: $order->username ?? 'Customer',
            customerEmail: $order->email,
            customerPhone: $order->whatsapp,
            method: $method,
            channel: $channel,
            description: "Payment for {$order->game->name}",
            items: [
                [
                    'id' => $order->denomination->id,
                    'name' => $order->denomination->name,
                    'price' => $order->total,
                    'quantity' => 1,
                ]
            ],
            metadata: [
                'game_id' => $order->game_id,
                'account_id' => $order->account_id,
                'server_id' => $order->server_id,
            ],
        );
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'invoice_no' => $this->invoiceNo,
            'amount' => $this->amount,
            'customer' => [
                'name' => $this->customerName,
                'email' => $this->customerEmail,
                'phone' => $this->customerPhone,
            ],
            'method' => $this->method,
            'channel' => $this->channel,
            'description' => $this->description,
            'items' => $this->items,
            'metadata' => $this->metadata,
            'expiry_minutes' => $this->expiryMinutes,
        ];
    }
}