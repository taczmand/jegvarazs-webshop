<?php

return [
    [
        'name' => 'Banki átutalás',
        'slug' => 'bank_transfer',
        'active' => true,
        'fee' => 0,
        'description' => 'Kérjük, utalj a megadott bankszámlaszámra.',
        'public' => true,
    ],
    [
        'name' => 'Utánvét',
        'slug' => 'cod',
        'active' => true,
        'fee' => 990,
        'description' => 'Fizethetsz a futárnál készpénzzel, kártyával, vagy személyes átvétel esetén a helyszínen.',
        'public' => true,
    ],
    [
        'name' => 'SimplePay (OTP)',
        'slug' => 'simplepay',
        'active' => true,
        'fee' => 0,
        'description' => 'Bankkártyás fizetés a SimplePay rendszerén keresztül.',
        'public' => true,
        'settings' => [
            'merchant_id' => env('SIMPLEPAY_MERCHANT_ID'),
            'secret' => env('SIMPLEPAY_SECRET'),
        ]
    ],
    [
        'name' => 'Készpénz',
        'slug' => 'cash',
        'active' => true,
        'fee' => 0,
        'description' => 'Készpénzes fizetés üzletünkben.',
        'public' => false,
    ],
];
