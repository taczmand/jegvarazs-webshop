<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    use LogsActivity;

    protected $guarded = [];

    public function cart()
    {
        return $this->hasOne(Cart::class, 'customer_id');
    }

    public function billingAddresses(): HasMany
    {
        return $this->hasMany(CustomerBillingAddress::class);
    }
    public function shippingAddresses(): HasMany
    {
        return $this->hasMany(CustomerShippingAddress::class);
    }
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
