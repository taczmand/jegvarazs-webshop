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
            'canceled' => 'Törölve',
            'paid' => 'Fizetve',
            'payment_failed' => 'Sikertelen fizetés, próbálja újra',
            'timeout' => 'Időtúllépés',
            default => ucfirst($this->status),
        };
    }

    public function getPaymentLabelAttribute()
    {
        return match ($this->payment_method) {
            'simplepay' => 'Bankkártyás fizetés az SimplePay rendszerén keresztül',
            'bank_transfer' => 'Banki átutalás',
            'cod' => 'Utánvét',
            'cash' => 'Készpénzes fizetés',
            default => ucfirst($this->payment_method),
        };
    }
}
