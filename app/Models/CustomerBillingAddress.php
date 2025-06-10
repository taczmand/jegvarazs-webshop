<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerBillingAddress extends Model
{
    protected $fillable = [
        'customer_id',
        'is_company',
        'name',
        'tax_number',
        'country',
        'zip_code',
        'city',
        'address_line',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
