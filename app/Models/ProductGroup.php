<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductGroup extends Model
{
    protected $guarded = [];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_group_product');
    }

    public function quantityDiscount()
    {
        return $this->hasOne(ProductGroupQuantityDiscount::class);
    }
}
