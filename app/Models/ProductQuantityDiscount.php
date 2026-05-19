<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductQuantityDiscount extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'min_quantity' => 'integer',
        'discount_value' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
