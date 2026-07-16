<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class SalesInvoiceItem extends Model
{
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:3',
        'discount_percent' => 'decimal:2',
        'unit_net_price' => 'integer',
        'unit_gross_price' => 'integer',
        'net_total' => 'integer',
        'vat_total' => 'integer',
        'gross_total' => 'integer',
    ];

    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }
}
