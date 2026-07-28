<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class StocktakeItem extends Model
{
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'expected_quantity' => 'float',
        'counted_quantity' => 'float',
        'difference_quantity' => 'float',
        'counted_at' => 'datetime',
    ];
}
