<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class GoodsReceiptItem extends Model
{
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }
}
