<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    public function cart()
    {
        return $this->hasOne(Cart::class, 'customer_id');
    }
}
