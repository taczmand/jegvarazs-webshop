<?php

namespace App\Models;

use App\Notifications\CustomerResetPasswordNotification;
use App\Traits\LogsActivity;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable implements CanResetPasswordContract
{
    use LogsActivity, CanResetPassword, Notifiable;

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

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomerResetPasswordNotification($token));
    }

}
