<?php

return [
    [
        'name' => 'Kifogyott',
        'slug' => 'out_of_stock',
        'color' => 'danger',
        'active' => true,
        'match' => fn(int $stock) => $stock < 0,
        'description' => 'készlet < 0',
    ],
    [
        'name' => 'Rendelhető, érdeklődjön',
        'slug' => 'backorder',
        'color' => 'warning',
        'active' => true,
        'match' => fn(int $stock) => $stock === 0,
        'description' => 'keszlet = 0',
    ],
    [
        'name' => 'Raktáron',
        'slug' => 'in_stock',
        'color' => 'success',
        'active' => true,
        'match' => fn(int $stock) => $stock > 0,
        'description' => 'készlet > 0',
    ],
];
