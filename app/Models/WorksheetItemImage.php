<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorksheetItemImage extends Model
{
    protected $guarded = [];

    public function item(): BelongsTo
    {
        return $this->belongsTo(WorksheetItem::class, 'worksheet_item_id');
    }

}
