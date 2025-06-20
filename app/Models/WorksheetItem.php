<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorksheetItem extends Model
{
    protected $guarded = [];

    public function worksheet()
    {
        return $this->belongsTo(Worksheet::class, 'worksheet_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(WorksheetItemImage::class);
    }
}
