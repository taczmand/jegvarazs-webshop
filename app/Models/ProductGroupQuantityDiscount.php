<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductGroupQuantityDiscount extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'base_quantity' => 'integer',
        'percent_per_step' => 'decimal:2',
        'max_percent' => 'decimal:2',
    ];

    public function productGroup()
    {
        return $this->belongsTo(ProductGroup::class);
    }
}
