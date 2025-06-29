<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorksheetImage extends Model
{
    use LogsActivity;
    protected $guarded = [];

    public function item(): BelongsTo
    {
        return $this->belongsTo(WorksheetItem::class, 'worksheet_item_id');
    }

}
