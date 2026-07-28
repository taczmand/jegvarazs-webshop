<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Stocktake extends Model
{
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'started_at' => 'date',
        'started_at_time' => 'datetime',
        'closed_at' => 'date',
        'closed_at_time' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(StocktakeItem::class);
    }
}
