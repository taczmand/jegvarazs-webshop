<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'received_at' => 'date',
        'stock_added_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }
}
