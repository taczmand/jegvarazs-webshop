<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class DeliveryNoteItem extends Model
{
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class);
    }
}
