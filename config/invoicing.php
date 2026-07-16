<?php

return [
    'provider' => env('INVOICE_PROVIDER', 'szamlazzhu'),

    'providers' => [
        'szamlazzhu' => App\Services\SzamlazzHu\SzamlazzHuInvoiceService::class,
        // 'billingo' => App\Services\Billingo\BillingoInvoiceService::class,
    ],
];
