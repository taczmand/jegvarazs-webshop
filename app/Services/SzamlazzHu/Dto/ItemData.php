<?php

namespace App\Services\SzamlazzHu\Dto;

readonly class ItemData
{
    public function __construct(
        public string $name,
        public float $quantity,
        public float $unitPrice,
        public int $vatPercent,
        public string $unit = 'db',
    ) {}
}
