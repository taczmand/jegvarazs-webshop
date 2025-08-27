<?php
namespace App\Services\Order\PaymentHandlers;

class PaymentHandlerFactory
{
    public static function make(string $method): ?PaymentHandlerInterface
    {
        return match ($method) {
            'bank_transfer' => new BankTransferHandler(),
            'cod' => new CodHandler(),
            'simplepay' => new SimplePayHandler(),
            'cash' => new CashHandler(),
            default => null,
        };
    }
}
