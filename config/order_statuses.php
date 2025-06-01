<?php

return [
    [
        'slug' => 'pending',
        'name' => 'Függőben',
        'color' => 'warning',
        'active' => true,
        'description' => 'Fizetésre vár',
        'match' => fn($order) => $order->payment_status === 'pending',
    ],
    [
        'slug' => 'paid',
        'name' => 'Fizetve',
        'color' => 'success',
        'active' => true,
        'description' => 'Fizetés megtörtént',
        'match' => fn($order) => $order->payment_status === 'paid',
    ],
    [
        'slug' => 'shipped',
        'name' => 'Kiszállítva',
        'color' => 'info',
        'active' => true,
        'description' => 'Rendelés elküldve',
        'match' => fn($order) => $order->shipping_status === 'shipped',
    ],
    [
        'slug' => 'cancelled',
        'name' => 'Törölve',
        'color' => 'danger',
        'active' => true,
        'description' => 'A rendelést törölték',
        'match' => fn($order) => $order->status === 'cancelled',
    ],
];
