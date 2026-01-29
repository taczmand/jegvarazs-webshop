<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use LogsActivity;

    protected $fillable = ['customer_id'];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function user()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
