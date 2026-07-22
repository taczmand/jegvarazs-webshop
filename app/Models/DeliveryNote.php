<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DeliveryNote extends Model
{
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'issued_at' => 'date',
        'delivered_at' => 'date',
        'handed_over_at' => 'datetime',
        'stock_deducted_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }

    public function partnerable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'partnerable_type', 'partnerable_id');
    }
}
