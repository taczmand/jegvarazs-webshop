<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }
}
