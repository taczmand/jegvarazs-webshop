<?php

namespace App\Services\SzamlazzHu\Dto;

readonly class CustomerData
{
    public function __construct(
        public string $name,
        public string $zip,
        public string $city,
        public string $address,
        public string $country = 'HU',
        public ?string $taxNumber = null,
        public ?string $email = null,
    ) {}
}
