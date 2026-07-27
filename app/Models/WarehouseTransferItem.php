<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class WarehouseTransferItem extends Model
{
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    public function warehouseTransfer()
    {
        return $this->belongsTo(WarehouseTransfer::class);
    }
}
