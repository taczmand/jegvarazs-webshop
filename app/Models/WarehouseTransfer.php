<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class WarehouseTransfer extends Model
{
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'transferred_at' => 'date',
        'stock_moved_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(WarehouseTransferItem::class);
    }
}
