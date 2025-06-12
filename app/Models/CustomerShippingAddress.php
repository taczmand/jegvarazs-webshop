<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerShippingAddress extends Model
{
    use LogsActivity;

    protected $fillable = [
        'customer_id',
        'name',
        'email',
        'phone',
        'country',
        'zip_code',
        'city',
        'address_line',
        'comment',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
