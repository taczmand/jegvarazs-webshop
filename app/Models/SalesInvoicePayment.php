<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class SalesInvoicePayment extends Model
{
    use LogsActivity;

    protected $fillable = [
        'sales_invoice_id',
        'paid_at',
        'amount',
        'currency',
        'payment_method',
        'transaction_id',
        'reference',
        'note',
    ];

    protected $casts = [
        'paid_at' => 'date',
        'amount' => 'integer',
    ];

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }
}
