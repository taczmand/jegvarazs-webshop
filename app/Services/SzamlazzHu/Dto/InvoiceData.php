<?php

namespace App\Services\SzamlazzHu\Dto;

readonly class InvoiceData
{
    public function __construct(
        public CustomerData $customer,
        public array $items,
        public string $paymentMethod,
        public string $currency = 'HUF',
    ) {}
}
