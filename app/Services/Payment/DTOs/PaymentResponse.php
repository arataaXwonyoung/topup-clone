<?php

namespace App\Services\Payment\DTOs;

class PaymentResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly string $reference,
        public readonly ?string $externalId = null,
        public readonly ?string $status = null,
        public readonly ?string $checkoutUrl = null,
        public readonly ?string $qrisString = null,
        public readonly ?string $vaNumber = null,
        public readonly ?float $fee = null,
        public readonly ?\DateTime $expiresAt = null,
        public readonly ?string $errorMessage = null,
        public readonly array $rawResponse = [],
    ) {}

    public static function success(array $data): self
    {
        return new self(
            success: true,
            reference: $data['reference'],
            externalId: $data['external_id'] ?? null,
            status: $data['status'] ?? 'PENDING',
            checkoutUrl: $data['checkout_url'] ?? null,
            qrisString: $data['qris_string'] ?? null,
            vaNumber: $data['va_number'] ?? null,
            fee: $data['fee'] ?? null,
            expiresAt: isset($data['expires_at']) ? new \DateTime($data['expires_at']) : null,
            errorMessage: null,
            rawResponse: $data,
        );
    }

    public static function error(string $message, array $data = []): self
    {
        return new self(
            success: false,
            reference: $data['reference'] ?? '',
            errorMessage: $message,
            rawResponse: $data,
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'reference' => $this->reference,
            'external_id' => $this->externalId,
            'status' => $this->status,
            'checkout_url' => $this->checkoutUrl,
            'qris_string' => $this->qrisString,
            'va_number' => $this->vaNumber,
            'fee' => $this->fee,
            'expires_at' => $this->expiresAt?->format('Y-m-d H:i:s'),
            'error_message' => $this->errorMessage,
        ];
    }
}