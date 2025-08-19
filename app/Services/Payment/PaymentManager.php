<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\Drivers\MidtransDriver;
use App\Services\Payment\Drivers\XenditDriver;
use App\Services\Payment\Drivers\TripayDriver;
use InvalidArgumentException;

class PaymentManager
{
    protected array $drivers = [];
    protected string $defaultDriver;

    public function __construct()
    {
        $this->defaultDriver = config('payment.default', 'midtrans');
    }

    public function driver(string $name = null): PaymentService
    {
        $name = $name ?: $this->defaultDriver;

        if (!isset($this->drivers[$name])) {
            $this->drivers[$name] = $this->createDriver($name);
        }

        return $this->drivers[$name];
    }

    protected function createDriver(string $name): PaymentService
    {
        return match ($name) {
            'midtrans' => new MidtransDriver(),
            'xendit' => new XenditDriver(),
            'tripay' => new TripayDriver(),
            default => throw new InvalidArgumentException("Payment driver [{$name}] not supported."),
        };
    }

    public function createPayment(Order $order, string $method, array $options = []): Payment
    {
        $driver = $this->driver($options['driver'] ?? null);
        return $driver->createCharge($order, array_merge($options, ['method' => $method]));
    }
}