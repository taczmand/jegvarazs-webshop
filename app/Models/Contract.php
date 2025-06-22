<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $guarded = [];
    protected $casts = [
        'data' => 'array',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'contract_products')
            ->withPivot('gross_price', 'product_qty')
            ->withTimestamps();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
