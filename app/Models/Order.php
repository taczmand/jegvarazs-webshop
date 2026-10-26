<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use LogsActivity;

    protected $guarded = [];

    protected $appends = ['payment_method_label'];

    public function getPaymentMethodLabelAttribute()
    {
        // Betöltjük a fizetési módokat a configból
        $methods = collect(config('payment_methods')); // pl. config/payments.php

        $method = $methods->firstWhere('slug', $this->payment_method);

        return $method['name'] ?? $this->payment_method;
    }

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
            'canceled' => 'Törölve',
            default => ucfirst($this->status ?? ''),
        };
    }

    public function getPaymentStatusLabelAttribute()
    {
        return match ($this->payment_status) {
            'pending' => 'Függőben',
            'paid' => 'Fizetve',
            'failed' => 'Fizetés sikertelen',
            'refunded' => 'Visszatérítve',
            'partially_refunded' => 'Részlegesen visszatérítve',
            'partially_paid' => 'Részösszeg kifizetve',
            default => ucfirst($this->payment_status ?? ''),
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
