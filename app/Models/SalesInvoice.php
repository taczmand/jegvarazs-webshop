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
        'issued_at' => 'date',
        'fulfilled_at' => 'date',
        'due_at' => 'date',
        'settled_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }
}
