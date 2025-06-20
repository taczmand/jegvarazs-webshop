<?php

namespace App\Models;

use App\Observers\CartItemObserver;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use LogsActivity;

    protected $fillable = ['cart_id', 'product_id', 'quantity'];

    protected static function booted()
    {
        static::observe(CartItemObserver::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
