<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Worksheet extends Model
{
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(WorksheetItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
