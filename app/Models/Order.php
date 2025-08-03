<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use LogsActivity;

    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function history()
    {
        return $this->hasMany(OrderHistory::class);
    }
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'pending' => 'Függőben',
            'processing' => 'Feldolgozás alatt',
            'completed' => 'Teljesítve',
            'cancelled' => 'Törölve',
            default => ucfirst($this->status),
        };
    }
}
