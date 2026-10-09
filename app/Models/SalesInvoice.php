<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class SalesInvoice extends Model
{
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'prices_include_vat' => 'boolean',
        'issued_at' => 'date:Y-m-d',
        'fulfilled_at' => 'date:Y-m-d',
        'due_at' => 'date:Y-m-d',
        'settled_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }
    public function payments()
    {
        return $this->hasMany(SalesInvoicePayment::class);
    }
}
