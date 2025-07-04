<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    use LogsActivity;

    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
